<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    // Mantenemos tu misma firma para ejecutarlo en la consola
    protected $signature = 'make:admin';

    protected $description = 'Crea el administrador global del sistema';

    public function handle()
    {
        // 1. Preguntar credenciales interactivamente por consola
        $nombre = $this->ask('Introduce tu nombre completo', 'Admin');
        $paterno = $this->ask('Introduce tu apellido paterno', 'Super');
        $materno = $this->ask('Introduce tu apellido materno', 'Super');
        $email = $this->ask('Introduce tu email');
        $password = $this->secret('Introduce tu contraseña');

        // Validación básica rápida para evitar colisiones de email
        if (User::where('email', $email)->exists()) {
            $this->error('Error: Ya existe un usuario registrado con ese correo electrónico.');

            return Command::FAILURE;
        }

        try {
            // 2. Crear el usuario base asignándole el rol estricto de tu sistema
            $user = User::create([
                'nombre' => $nombre,
                'paterno' => $paterno,
                'materno' => $materno,
                'email' => $email,
                'password' => Hash::make($password),
            ]);

            $user->asignarRol('admin');

            $this->info("¡Éxito! El usuario {$user->nombre_completo} ya es administrador global del sistema.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Algo salió mal al crear el administrador: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
