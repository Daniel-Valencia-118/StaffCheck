<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions:id,name')->withCount('users')->orderBy('name')->get()
            ->map(fn (Role $rol) => [
                'id' => $rol->id,
                'nombre' => $rol->name,
                'usuarios' => $rol->users_count,
                'permisos' => $rol->permissions->pluck('name')->diff(RolesPermisosSeeder::PERMISOS_SOLO_ADMIN)->values(),
                'protegido' => in_array($rol->name, RolesPermisosSeeder::ROLES_PROTEGIDOS, true),
                'esAdmin' => $rol->name === 'admin',
            ]);

        $permisos = Permission::whereNotIn('name', RolesPermisosSeeder::PERMISOS_SOLO_ADMIN)->orderBy('name')->pluck('name')
            ->map(fn (string $nombre) => [
                'nombre' => $nombre,
                'modulo' => strtok($nombre, '.'),
                'descripcion' => RolesPermisosSeeder::PERMISOS[$nombre] ?? $nombre,
            ]);

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->where('guard_name', 'web')],
        ], [
            'nombre.regex' => 'Use solo minúsculas, números y guion bajo (ej.: jefe_oficina).',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        $rol = Role::create(['name' => $datos['nombre'], 'guard_name' => 'web']);
        $rol->givePermissionTo('panel.acceder');

        Auditoria::registrar('roles', 'creado', "Creó el rol {$rol->name}", $rol);

        return back()->with('success', "Rol {$rol->name} creado. Asígnele permisos.");
    }

    public function update(Request $request, $id)
    {
        $rol = Role::findOrFail($id);
        abort_if($rol->name === 'admin', 403, 'El rol admin tiene todos los permisos y no se edita.');

        $datos = $request->validate([
            'permisos' => ['present', 'array'],
            'permisos.*' => ['string', Rule::notIn(RolesPermisosSeeder::PERMISOS_SOLO_ADMIN), Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ], [
            'permisos.*.not_in' => 'Ese permiso es exclusivo del administrador.',
        ]);

        $actuales = $rol->permissions->pluck('name');
        $nuevos = collect($datos['permisos'])->unique()->values();

        abort_unless(auth()->user()->puedeOtorgar($nuevos->diff($actuales)), 403, 'No puede otorgar permisos que usted no tiene.');

        $rol->syncPermissions($nuevos->all());

        Auditoria::registrar('roles', 'permisos_actualizados', "Actualizó los permisos del rol {$rol->name}", $rol, [
            'agregados' => $nuevos->diff($actuales)->values()->all(),
            'quitados' => $actuales->diff($nuevos)->values()->all(),
        ]);

        return back()->with('success', "Permisos del rol {$rol->name} actualizados.");
    }

    public function destroy($id)
    {
        $rol = Role::withCount('users')->findOrFail($id);

        if (in_array($rol->name, RolesPermisosSeeder::ROLES_PROTEGIDOS, true)) {
            return back()->with('error', "El rol {$rol->name} es parte del sistema y no se puede eliminar.");
        }

        if ($rol->users_count > 0) {
            return back()->with('error', "El rol {$rol->name} tiene usuarios asignados. Cámbielos de rol antes de eliminarlo.");
        }

        $nombre = $rol->name;
        $rol->delete();

        Auditoria::registrar('roles', 'eliminado', "Eliminó el rol {$nombre}");

        return back()->with('success', "Rol {$nombre} eliminado.");
    }
}
