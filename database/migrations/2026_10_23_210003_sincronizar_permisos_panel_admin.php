<?php

use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NUEVOS = ['roles.gestionar', 'configuracion.gestionar', 'auditoria.ver', 'sistema.gestionar'];

    public function up(): void
    {
        (new RolesPermisosSeeder)->run();
    }

    public function down(): void
    {
        Permission::whereIn('name', self::NUEVOS)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
