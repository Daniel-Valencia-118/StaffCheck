<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Rules\TurnstileRule;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'registroCuentasAbierto' => Configuracion::valor('registro_cuentas_abierto'),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'cf_turnstile_response' => ['required', new TurnstileRule],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            $rechazo = match (true) {
                $user->pendiente => 'Tu cuenta está pendiente de aprobación por un administrador.',
                ! $user->isActivo() => 'Tu cuenta está suspendida.',
                ! $user->can('panel.acceder') => 'Tu cuenta no tiene acceso al sistema.',
                default => null,
            };

            if ($rechazo) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors(['email' => $rechazo]);
            }

            $request->session()->regenerate();

            Auditoria::registrar('auth', 'login', 'Inició sesión', $user);

            return Inertia::location(redirect()->intended($user->getDashboardRoute()));
        }

        Auditoria::registrar('auth', 'login_fallido', 'Intento de inicio de sesión fallido', null, [
            'email' => mb_strtolower((string) $request->input('email'), 'UTF-8'),
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auditoria::registrar('auth', 'logout', 'Cerró sesión', $request->user());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
