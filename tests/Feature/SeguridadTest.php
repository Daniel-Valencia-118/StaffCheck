<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_respuestas_incluyen_cabeceras_de_seguridad(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_paginas_publicas_no_exponen_rutas_internas(): void
    {
        $html = $this->get(route('registro.personal'))->assertOk()->getContent();

        $this->assertStringContainsString('registro.personal.store', $html);
        $this->assertStringNotContainsString('usuarios.index', $html);
        $this->assertStringNotContainsString('personal.export', $html);
    }

    public function test_usuarios_autenticados_reciben_sus_rutas(): void
    {
        $admin = User::factory()->admin()->create();

        $html = $this->actingAs($admin)->get(route('usuarios.index'))->assertOk()->getContent();

        $this->assertStringContainsString('usuarios.estado', $html);
    }

    public function test_cambiar_contrasena_exige_la_actual(): void
    {
        $user = User::factory()->create();
        $datos = [
            'nombre' => $user->nombre,
            'paterno' => $user->paterno,
            'materno' => $user->materno,
            'email' => $user->email,
            'password' => 'nueva1234',
            'password_confirmation' => 'nueva1234',
        ];

        $this->actingAs($user)->patch(route('cuenta.update'), $datos)
            ->assertSessionHasErrors('current_password');

        $this->actingAs($user)->patch(route('cuenta.update'), $datos + ['current_password' => 'password'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('nueva1234', $user->fresh()->password));
    }

    public function test_cambiar_solo_el_nombre_no_exige_contrasena(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch(route('cuenta.update'), [
            'nombre' => 'Otro',
            'paterno' => $user->paterno,
            'materno' => $user->materno,
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        $this->assertSame('otro', $user->fresh()->nombre);
    }
}
