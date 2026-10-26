<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\GuardarUsuarioRequest;
use App\Models\Persona;
use App\Models\User;
use App\Support\Auditoria;
use App\Support\Busqueda;
use App\Support\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    private const COLUMNAS_ORDEN = ['nombre', 'paterno', 'materno', 'email', 'rol', 'estado', 'created_at'];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $pendientes = $request->boolean('pendientes');

        $query = User::query()
            ->select(['id', 'nombre', 'paterno', 'materno', 'email', 'rol', 'estado', 'pendiente', 'created_at'])
            ->with('roles:id,name')
            ->where('id', '!=', auth()->id())
            ->tap(fn ($query) => Busqueda::filtrar($query, ['nombre', 'paterno', 'materno', 'email'], $search))
            ->when($pendientes, fn ($query) => $query->where('pendiente', true));

        $orden = Orden::aplicar($query, $request->input('orden'), $request->input('dir'), self::COLUMNAS_ORDEN, 'created_at');

        $usuarios = $query
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $u) => [
                ...$u->only(['id', 'nombre', 'paterno', 'materno', 'email', 'estado', 'pendiente']),
                'registrado' => $u->created_at?->format('d/m/Y'),
                'rol' => $u->roles->pluck('name')->first() ?? $u->rol,
                'editable' => auth()->user()->esAdmin() || ! $u->roles->contains('name', 'admin'),
            ]);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => array_filter(['search' => $search, 'pendientes' => $pendientes ? 1 : null, ...$orden]),
            'totalPendientes' => User::where('pendiente', true)->count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Usuarios/Crear', [
            'roles' => $this->rolesAsignables(),
        ]);
    }

    public function store(GuardarUsuarioRequest $request)
    {
        $rol = $this->rolPermitido($request->rol);

        $user = DB::transaction(function () use ($request, $rol) {
            $user = User::create([
                ...$request->safe()->only(['nombre', 'paterno', 'materno', 'email']),
                'password' => Hash::make($request->password),
                'estado' => true,
            ]);
            $user->asignarRol($rol->name);

            return $user;
        });

        Auditoria::registrar('usuarios', 'creado', "Creó el usuario {$user->email} con rol {$rol->name}", $user);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = $this->usuarioEditable($id);

        return Inertia::render('Usuarios/Editar', [
            'usuario' => [
                ...$usuario->only(['id', 'nombre', 'paterno', 'materno', 'email', 'estado', 'pendiente']),
                'rol' => $usuario->getRoleNames()->first() ?? $usuario->rol,
            ],
            'roles' => $this->rolesAsignables(),
            'esPropio' => $usuario->is(auth()->user()),
            'ficha' => $this->resumenFicha($usuario->persona()->first()),
            'fichaSugerida' => $usuario->id_persona ? null : $this->resumenFicha(
                Persona::query()->where('email', $usuario->email)->whereDoesntHave('usuario')->first()
            ),
        ]);
    }

    public function update(GuardarUsuarioRequest $request, $id)
    {
        $usuario = $this->usuarioEditable($id);
        $rolAnterior = $usuario->getRoleNames()->first();
        $rol = $request->rol === $rolAnterior
            ? Role::findByName($rolAnterior, 'web')
            : $this->rolPermitido($request->rol);

        if ($rol->name !== $rolAnterior) {
            if ($usuario->is(auth()->user())) {
                return back()->with('error', 'No puede cambiar su propio rol.');
            }

            if ($rolAnterior === 'admin' && $this->esUltimoAdminActivo($usuario)) {
                return back()->with('error', 'No se puede quitar el rol al último administrador activo.');
            }
        }

        DB::transaction(function () use ($usuario, $request, $rol, $rolAnterior) {
            $usuario->fill($request->safe()->only(['nombre', 'paterno', 'materno', 'email']))->save();

            if ($rol->name !== $rolAnterior) {
                $usuario->asignarRol($rol->name);
            }
        });

        $detalle = $rol->name !== $rolAnterior ? ['rol' => ['antes' => $rolAnterior, 'despues' => $rol->name]] : [];
        Auditoria::registrar('usuarios', 'editado', "Editó el usuario {$usuario->email}", $usuario, $detalle);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function updateStatus(Request $request, $id)
    {
        $usuario = $this->usuarioEditable($id);
        $request->validate(['estado' => 'required|boolean']);
        $suspender = ! $request->boolean('estado');

        if ($suspender && $usuario->is(auth()->user())) {
            return back()->with('error', 'No puede suspender su propia cuenta.');
        }

        if ($suspender && $usuario->esAdmin() && $this->esUltimoAdminActivo($usuario)) {
            return back()->with('error', 'No se puede suspender al último administrador activo.');
        }

        if ($usuario->pendiente) {
            return back()->with('error', 'La cuenta es una solicitud pendiente: apruébela desde Editar.');
        }

        $usuario->estado = $request->boolean('estado');
        $usuario->save();

        Auditoria::registrar('usuarios', $suspender ? 'suspendido' : 'reactivado',
            ($suspender ? 'Suspendió' : 'Reactivó')." al usuario {$usuario->email}", $usuario);

        return back()->with('success', $suspender ? 'Usuario suspendido.' : 'Usuario reactivado.');
    }

    public function updatePassword(Request $request, $id)
    {
        $usuario = $this->usuarioEditable($id);

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $usuario->password = Hash::make($request->password);
        $usuario->setRememberToken(null);
        $usuario->save();

        Auditoria::registrar('usuarios', 'contrasena_restablecida', "Restableció la contraseña de {$usuario->email}", $usuario);

        return back()->with('success', 'Contraseña restablecida. Comuníquela al usuario por un medio seguro.');
    }

    public function aprobar(Request $request, $id)
    {
        $usuario = $this->usuarioEditable($id);
        abort_unless($usuario->pendiente, 404);

        $request->validate(['rol' => ['required', 'string', 'exists:roles,name']]);
        $rol = $this->rolPermitido($request->rol);

        DB::transaction(function () use ($usuario, $rol) {
            $usuario->forceFill(['pendiente' => false, 'estado' => true])->save();
            $usuario->asignarRol($rol->name);
        });

        Auditoria::registrar('usuarios', 'solicitud_aprobada', "Aprobó la solicitud de {$usuario->email} con rol {$rol->name}", $usuario);

        return redirect()->route('usuarios.edit', $usuario->id)->with('success', 'Solicitud aprobada. El usuario ya puede iniciar sesión.');
    }

    public function rechazar($id)
    {
        $usuario = $this->usuarioEditable($id);
        abort_unless($usuario->pendiente, 404);

        $email = $usuario->email;
        DB::transaction(function () use ($usuario) {
            $usuario->syncRoles([]);
            $usuario->delete();
        });

        Auditoria::registrar('usuarios', 'solicitud_rechazada', "Rechazó la solicitud de cuenta de {$email}");

        return redirect()->route('usuarios.index', ['pendientes' => 1])->with('success', 'Solicitud rechazada y eliminada.');
    }

    public function vincularFicha(Request $request, $id)
    {
        $usuario = $this->usuarioEditable($id);

        $datos = $request->validate([
            'ci' => ['nullable', 'string', 'max:15'],
        ]);

        $persona = null;
        if (filled($datos['ci'] ?? null)) {
            $persona = Persona::firstWhere('ci', $datos['ci']);

            if (! $persona) {
                return back()->withErrors(['ci' => 'No existe una ficha activa con ese CI.']);
            }

            if (User::where('id_persona', $persona->id)->whereKeyNot($usuario->id)->exists()) {
                return back()->withErrors(['ci' => 'Esa ficha ya está vinculada a otra cuenta.']);
            }
        }

        $usuario->forceFill(['id_persona' => $persona?->id])->save();

        Auditoria::registrar('usuarios', $persona ? 'ficha_vinculada' : 'ficha_desvinculada',
            $persona ? "Vinculó la cuenta {$usuario->email} a la ficha {$persona->nombre_completo}" : "Desvinculó la ficha de la cuenta {$usuario->email}",
            $usuario);

        return back()->with('success', $persona ? 'Ficha vinculada a la cuenta.' : 'Ficha desvinculada.');
    }

    private function resumenFicha(?Persona $persona): ?array
    {
        return $persona ? ['id' => $persona->id, 'ci' => $persona->ci, 'nombre' => $persona->nombre_completo] : null;
    }

    private function usuarioEditable($id): User
    {
        $usuario = User::findOrFail($id);

        abort_if($usuario->esAdmin() && ! auth()->user()->esAdmin(), 403, 'Solo un administrador puede modificar a otro administrador.');

        return $usuario;
    }

    private function rolPermitido(string $nombre): Role
    {
        $rol = Role::findByName($nombre, 'web');

        abort_unless(auth()->user()->puedeAsignarRol($rol), 403, 'No puede asignar un rol con permisos que usted no tiene.');

        return $rol;
    }

    private function rolesAsignables(): array
    {
        $actor = auth()->user();

        return Role::with('permissions:id,name')->orderBy('name')->get()
            ->filter(fn (Role $rol) => $actor->puedeAsignarRol($rol))
            ->map(fn (Role $rol) => ['value' => $rol->name, 'label' => $rol->name])
            ->values()
            ->all();
    }

    private function esUltimoAdminActivo(User $usuario): bool
    {
        return User::role('admin')->where('estado', true)->whereKeyNot($usuario->id)->doesntExist();
    }
}
