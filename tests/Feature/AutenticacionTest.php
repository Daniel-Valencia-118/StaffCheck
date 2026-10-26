<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private function login(string $email, string $password = 'password')
    {
        return $this->post(route('login.submit'), [
            'email' => $email,
            'password' => $password,
            'cf_turnstile_response' => 'token',
        ]);
    }

    public function test_admin_inicia_sesion_y_llega_a_su_panel(): void
    {
        $this->turnstileValido();
        $admin = User::factory()->admin()->create();

        $this->login($admin->email)->assertRedirect(route('personal.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_inertia_fuerza_recarga_completa(): void
    {
        $this->turnstileValido();
        $encargado = User::factory()->create();

        $this->withHeader('X-Inertia', 'true')
            ->login($encargado->email)
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('personal.index'));
    }

    public function test_credenciales_incorrectas_no_autentican(): void
    {
        $this->turnstileValido();
        $user = User::factory()->create();

        $this->login($user->email, 'incorrecta')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_usuario_suspendido_no_puede_entrar(): void
    {
        $this->turnstileValido();
        $user = User::factory()->suspendido()->create();

        $this->login($user->email)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_se_bloquea_tras_cinco_intentos(): void
    {
        $this->turnstileValido();
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->login($user->email, 'incorrecta');
        }

        $this->login($user->email)->assertSessionHasErrors([
            'email' => 'Demasiados intentos de inicio de sesión. Espere un minuto e intente nuevamente.',
        ]);
        $this->assertGuest();
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('personal.index'))->assertRedirect(route('login'));
        $this->get(route('usuarios.index'))->assertRedirect(route('login'));
    }

    public function test_sesion_de_usuario_suspendido_se_cierra(): void
    {
        $user = User::factory()->suspendido()->create();

        $this->actingAs($user)->get(route('personal.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_rol_sin_acceso_al_panel_no_puede_iniciar_sesion(): void
    {
        $this->turnstileValido();
        Role::create(['name' => 'sin_acceso', 'guard_name' => 'web']);
        $usuario = User::factory()->create(['rol' => 'sin_acceso']);

        $this->login($usuario->email)->assertSessionHasErrors([
            'email' => 'Tu cuenta no tiene acceso al sistema.',
        ]);
        $this->assertGuest();
    }

    public function test_urls_antiguas_redirigen_al_panel(): void
    {
        $this->get('/admin/personal')->assertRedirect(url('/panel/personal'))->assertStatus(301);
        $this->get('/rrhh/personal/15')->assertRedirect(url('/panel/personal/15'));
        $this->get('/admin')->assertRedirect(url('/panel'));
    }
}
