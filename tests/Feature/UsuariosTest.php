<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'paterno' => 'Pérez',
            'materno' => 'Rojas',
            'email' => 'ana@example.com',
            'rol' => 'encargado',
            'password' => 'clave1234',
            'password_confirmation' => 'clave1234',
        ], $cambios);
    }

    private function gestorSinAdmin(): User
    {
        Role::create(['name' => 'gestor'])->givePermissionTo(['panel.acceder', 'usuarios.ver', 'usuarios.gestionar', 'personal.ver']);

        return User::factory()->create(['rol' => 'gestor']);
    }

    public function test_admin_crea_usuario_con_rol_sin_perder_su_sesion(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('usuarios.store'), $this->datos())
            ->assertRedirect(route('usuarios.index'));

        $this->assertAuthenticatedAs($admin);
        $creado = User::firstWhere('email', 'ana@example.com');
        $this->assertTrue($creado->hasRole('encargado'));
        $this->assertSame('encargado', $creado->rol);
    }

    public function test_crear_usuario_exige_contrasena_segura_y_rol_valido(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('usuarios.store'), $this->datos([
            'password' => 'soloLetras',
            'password_confirmation' => 'soloLetras',
            'rol' => 'inexistente',
        ]))->assertSessionHasErrors(['password', 'rol']);
    }

    public function test_admin_edita_datos_y_rol(): void
    {
        $admin = User::factory()->admin()->create();
        $usuario = User::factory()->create();

        $this->actingAs($admin)->put(route('usuarios.update', $usuario->id), [
            'nombre' => 'Nuevo', 'paterno' => 'Apellido', 'materno' => 'Otro',
            'email' => 'nuevo@example.com', 'rol' => 'admin',
        ])->assertRedirect(route('usuarios.index'));

        $usuario->refresh();
        $this->assertSame('nuevo@example.com', $usuario->email);
        $this->assertTrue($usuario->hasRole('admin'));
        $this->assertFalse($usuario->hasRole('encargado'));
    }

    public function test_nadie_cambia_su_propio_rol(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('usuarios.update', $admin->id), [
            'nombre' => $admin->nombre, 'paterno' => $admin->paterno, 'materno' => $admin->materno,
            'email' => $admin->email, 'rol' => 'encargado',
        ])->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_admin_no_puede_suspenderse_a_si_mismo(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('usuarios.estado', $admin->id), ['estado' => false])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->estado);
    }

    public function test_admin_suspende_y_reactiva_encargado(): void
    {
        $admin = User::factory()->admin()->create();
        $encargado = User::factory()->create();

        $this->actingAs($admin)->put(route('usuarios.estado', $encargado->id), ['estado' => false]);
        $this->assertFalse($encargado->fresh()->estado);

        $this->actingAs($admin)->put(route('usuarios.estado', $encargado->id), ['estado' => true]);
        $this->assertTrue($encargado->fresh()->estado);
    }

    public function test_admin_restablece_contrasena(): void
    {
        $admin = User::factory()->admin()->create();
        $usuario = User::factory()->create();

        $this->actingAs($admin)->put(route('usuarios.contrasena', $usuario->id), [
            'password' => 'Temporal123',
            'password_confirmation' => 'Temporal123',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('Temporal123', $usuario->fresh()->password));
    }

    public function test_gestor_no_puede_crear_admins_ni_tocar_admins(): void
    {
        $gestor = $this->gestorSinAdmin();
        $admin = User::factory()->admin()->create();

        $this->actingAs($gestor)->post(route('usuarios.store'), $this->datos(['rol' => 'admin']))->assertForbidden();
        $this->actingAs($gestor)->put(route('usuarios.estado', $admin->id), ['estado' => false])->assertForbidden();
        $this->actingAs($gestor)->get(route('usuarios.edit', $admin->id))->assertForbidden();
        $this->actingAs($gestor)->put(route('usuarios.contrasena', $admin->id), [
            'password' => 'Temporal123', 'password_confirmation' => 'Temporal123',
        ])->assertForbidden();

        $this->assertTrue($admin->fresh()->estado);
        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
    }

    public function test_gestor_no_asigna_roles_con_mas_permisos_que_el(): void
    {
        $gestor = $this->gestorSinAdmin();

        $this->actingAs($gestor)->post(route('usuarios.store'), $this->datos(['rol' => 'encargado']))->assertForbidden();

        $this->actingAs($gestor)->get(route('usuarios.create'))
            ->assertInertia(fn ($page) => $page->where('roles', fn ($roles) => collect($roles)->pluck('value')->sort()->values()->all() === ['gestor']));
    }
}
