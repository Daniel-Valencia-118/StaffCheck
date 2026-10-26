<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class GuardarUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $usuarioId = $this->route('id');

        return [
            'nombre' => ['required', 'string', 'max:100'],
            'paterno' => ['required', 'string', 'max:100'],
            'materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuarioId)],
            'rol' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => $usuarioId ? ['prohibited'] : ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'rol.exists' => 'El rol seleccionado no existe.',
            'email.unique' => 'Ya existe un usuario con ese correo electrónico.',
        ];
    }
}
