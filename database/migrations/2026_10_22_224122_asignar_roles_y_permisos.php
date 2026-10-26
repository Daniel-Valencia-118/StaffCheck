<?php

use App\Models\User;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        (new RolesPermisosSeeder)->run();

        $roles = Role::pluck('id', 'name');

        DB::table('users')->select(['id', 'rol'])->orderBy('id')->each(function ($user) use ($roles) {
            $rol = mb_strtolower(trim((string) $user->rol), 'UTF-8');

            if (isset($roles[$rol])) {
                DB::table('model_has_roles')->insertOrIgnore([
                    'role_id' => $roles[$rol],
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ]);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
