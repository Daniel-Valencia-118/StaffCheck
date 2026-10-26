<?php

use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        (new RolesPermisosSeeder)->run();

        Role::findOrCreate('usuario', 'web')->givePermissionTo(['panel.acceder', 'mi_ficha.editar']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Role::findByName('usuario', 'web')->revokePermissionTo('panel.acceder');
        Permission::where('name', 'mi_ficha.editar')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
