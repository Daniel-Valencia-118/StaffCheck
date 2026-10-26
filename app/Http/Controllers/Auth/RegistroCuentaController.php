<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\User;
use App\Rules\TurnstileRule;
use App\Support\Auditoria;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class RegistroCuentaController extends Controller
{
    private const MENSAJE_ENVIADA = 'Solicitud enviada. Si los datos son correctos, un administrador revisará su cuenta y le asignará acceso.';

    public function create()
    {
        if (! Configuracion::valor('registro_cuentas_abierto')) {
            return redirect()->route('login')->with('error', 'El registro de cuentas no está habilitado.');
        }

        return Inertia::render('Auth/Registro');
    }

    public function store(Request $request)
    {
        if (! Configuracion::valor('registro_cuentas_abierto')) {
            return redirect()->route('login')->with('error', 'El registro de cuentas no está habilitado.');
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'paterno' => ['required', 'string', 'max:100'],
            'materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'cf_turnstile_response' => ['required', new TurnstileRule],
        ]);

        $enviada = redirect()->route('login')->with('success', self::MENSAJE_ENVIADA);

        if (User::where('email', mb_strtolower($datos['email'], 'UTF-8'))->exists()) {
            Auditoria::registrar('usuarios', 'solicitud_duplicada', 'Se intentó solicitar una cuenta con un correo ya registrado');

            return $enviada;
        }

        try {
            $usuario = DB::transaction(function () use ($datos) {
                $usuario = new User([
                    'nombre' => $datos['nombre'],
                    'paterno' => $datos['paterno'],
                    'materno' => $datos['materno'] ?? '',
                    'email' => $datos['email'],
                    'password' => Hash::make($datos['password']),
                ]);
                $usuario->forceFill(['estado' => false, 'pendiente' => true])->save();
                $usuario->asignarRol('usuario');

                return $usuario;
            });
        } catch (UniqueConstraintViolationException) {
            return $enviada;
        }

        Auditoria::registrar('usuarios', 'solicitud_registrada', "Nueva solicitud de cuenta de {$usuario->email}", $usuario);

        return $enviada;
    }
}
