<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesPermisosSeeder extends Seeder
{
    public const PERMISOS = [
        'panel.acceder' => 'Acceder al panel',
        'personal.ver' => 'Ver la lista de personal',
        'personal.ver_detalle' => 'Ver la ficha completa',
        'personal.cambiar_estado' => 'Cambiar el estado de una ficha',
        'personal.exportar' => 'Exportar el personal a Excel',
        'personal.pdf' => 'Generar la ficha en PDF',
        'personal.editar' => 'Editar los datos de una ficha',
        'personal.eliminar' => 'Enviar fichas a la papelera y restaurarlas',
        'mi_ficha.editar' => 'Ver y corregir su propia ficha (cuenta vinculada)',
        'usuarios.ver' => 'Ver los usuarios del sistema',
        'usuarios.gestionar' => 'Crear, editar, suspender usuarios y restablecer contraseñas',
        'roles.gestionar' => 'Crear roles y asignarles permisos',
        'configuracion.gestionar' => 'Cambiar la configuración del sistema',
        'auditoria.ver' => 'Ver el registro de auditoría',
        'sistema.gestionar' => 'Ver el estado del sistema y limpiar cachés',
    ];

    public const PERMISOS_SOLO_ADMIN = ['personal.eliminar'];

    public const ROLES_PROTEGIDOS = ['admin', 'encargado', 'usuario'];

    public const ROLES = [
        'admin' => [],
        'encargado' => ['panel.acceder', 'personal.ver', 'personal.ver_detalle', 'personal.exportar', 'personal.pdf'],
        'usuario' => ['panel.acceder', 'mi_ficha.editar'],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(self::PERMISOS) as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        foreach (self::ROLES as $nombre => $permisos) {
            $rol = Role::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);

            if ($rol->wasRecentlyCreated && $permisos) {
                $rol->givePermissionTo($permisos);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
