<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermisosTest extends TestCase
{
    use RefreshDatabase;

    private function persona(): Persona
    {
        return Persona::create([
            'paterno' => 'quispe', 'materno' => 'mamani', 'nombres' => 'juan', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => 'aprobado',
        ]);
    }

    public static function matriz(): array
    {
        return [
            'admin: personal' => ['admin', 'personal.index', 200],
            'admin: ficha' => ['admin', 'personal.show', 200],
            'admin: exportar' => ['admin', 'personal.export', 200],
            'admin: usuarios' => ['admin', 'usuarios.index', 200],
            'admin: crear usuario' => ['admin', 'usuarios.create', 200],
            'admin: cuenta' => ['admin', 'cuenta', 200],
            'encargado: personal' => ['encargado', 'personal.index', 200],
            'encargado: ficha' => ['encargado', 'personal.show', 200],
            'encargado: exportar' => ['encargado', 'personal.export', 200],
            'encargado: cuenta' => ['encargado', 'cuenta', 200],
            'encargado: usuarios' => ['encargado', 'usuarios.index', 403],
            'encargado: crear usuario' => ['encargado', 'usuarios.create', 403],
            'usuario: personal' => ['usuario', 'personal.index', 403],
            'usuario: cuenta' => ['usuario', 'cuenta', 200],
            'usuario: mi ficha' => ['usuario', 'mi_ficha.edit', 200],
            'usuario: exportar' => ['usuario', 'personal.export', 403],
            'encargado: mi ficha' => ['encargado', 'mi_ficha.edit', 403],
        ];
    }

    #[DataProvider('matriz')]
    public function test_matriz_de_acceso_por_rol(string $rol, string $ruta, int $estado): void
    {
        $user = User::factory()->create(['rol' => $rol]);
        $parametros = $ruta === 'personal.show' ? [$this->persona()->id] : [];

        $this->actingAs($user)->get(route($ruta, $parametros))->assertStatus($estado);
    }

    public function test_encargado_no_cambia_estado_ni_gestiona_usuarios(): void
    {
        $encargado = User::factory()->create();
        $otro = User::factory()->create();
        $persona = $this->persona();

        $this->actingAs($encargado)->put(route('personal.estado', $persona->id), ['estado' => 'rechazado'])->assertForbidden();
        $this->actingAs($encargado)->put(route('usuarios.estado', $otro->id), ['estado' => false])->assertForbidden();

        $this->assertSame('aprobado', $persona->fresh()->estado);
        $this->assertTrue($otro->fresh()->estado);
    }

    public function test_rol_nuevo_funciona_solo_con_sus_permisos(): void
    {
        Role::create(['name' => 'auditor'])->givePermissionTo(['panel.acceder', 'personal.ver']);
        $auditor = User::factory()->create(['rol' => 'auditor']);
        $persona = $this->persona();

        $this->actingAs($auditor)->get(route('personal.index'))->assertOk();
        $this->actingAs($auditor)->get(route('personal.show', $persona->id))->assertForbidden();
        $this->actingAs($auditor)->get(route('personal.export'))->assertForbidden();
        $this->actingAs($auditor)->get(route('usuarios.index'))->assertForbidden();
        $this->assertSame(route('personal.index'), $auditor->getDashboardRoute());
    }

    public function test_frontend_recibe_roles_y_permisos(): void
    {
        $encargado = User::factory()->create();

        $this->actingAs($encargado)->get(route('personal.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Personal/Index')
                ->where('auth.user.roles', ['encargado'])
                ->where('auth.user.permisos', fn ($permisos) => collect($permisos)->contains('personal.exportar')
                    && ! collect($permisos)->contains('usuarios.ver')));
    }

    public function test_admin_recibe_todos_los_permisos(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('personal.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('auth.user.permisos', fn ($permisos) => collect($permisos)->contains('usuarios.gestionar')
                    && collect($permisos)->contains('personal.cambiar_estado')));
    }

    public function test_migracion_asigna_roles_a_usuarios_existentes(): void
    {
        DB::table('model_has_roles')->delete();
        $ids = collect(['admin', 'encargado', 'usuario'])->mapWithKeys(fn ($rol) => [$rol => DB::table('users')->insertGetId([
            'nombre' => 'x', 'paterno' => 'y', 'materno' => 'z', 'rol' => $rol, 'estado' => true,
            'email' => "{$rol}@example.com", 'password' => 'x',
        ])]);

        $migracion = require database_path('migrations/2026_10_22_224122_asignar_roles_y_permisos.php');
        $migracion->up();
        $migracion->up();

        foreach ($ids as $rol => $id) {
            $this->assertTrue(User::find($id)->hasRole($rol), "El usuario {$rol} no recibió su rol");
        }
        $this->assertSame(3, DB::table('model_has_roles')->count());
    }
}
