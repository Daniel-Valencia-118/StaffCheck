<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class CuentaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Cuenta', [
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $cambiaCredenciales = $request->filled('password')
            || mb_strtolower(trim((string) $request->input('email')), 'UTF-8') !== $user->email;

        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'paterno' => 'required|string|max:100',
            'materno' => 'nullable|string|max:100',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'current_password' => [Rule::requiredIf($cambiaCredenciales), 'nullable', 'current_password'],
        ], [
            'current_password.required' => 'Ingrese su contraseña actual para cambiar el email o la contraseña.',
            'current_password.current_password' => 'La contraseña actual no es correcta.',
        ]);

        $user->nombre = $validated['nombre'];
        $user->paterno = $validated['paterno'];
        $user->materno = $validated['materno'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $cambiosSensibles = array_keys(array_intersect_key($user->getDirty(), ['email' => 1, 'password' => 1]));
        $user->save();

        if ($cambiosSensibles) {
            Auditoria::registrar('usuarios', 'cuenta_actualizada', 'Cambió '.implode(' y ', $cambiosSensibles).' de su cuenta', $user);
        }

        return back()->with('success', 'Datos actualizados correctamente');
    }
}
