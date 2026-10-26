<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\EnlaceCorreccion;
use App\Models\Persona;
use App\Models\User;
use App\Support\MapaEstatico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class CuentasYCorreccionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->turnstileValido();
        $this->admin = User::factory()->admin()->create();
    }

    private function persona(array $cambios = []): Persona
    {
        return Persona::create(array_merge([
            'paterno' => 'quispe', 'materno' => 'mamani', 'nombres' => 'juan', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => 'observado',
            'email' => 'juan@example.com', 'estado_civil' => 'soltero/a', 'oficina_actual' => 'oficina central la paz',
        ], $cambios));
    }

    private function datosFicha(array $cambios = []): array
    {
        return array_merge([
            'paterno' => 'Quispe', 'materno' => 'Mamani', 'nombres' => 'Juan Corregido', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'M', 'fecha_nacimiento' => '1990-01-01',
            'lugar_nacimiento_provincia' => 'Murillo', 'lugar_nacimiento_ciudad' => 'La Paz',
            'estado_civil' => 'SOLTERO/A', 'numero_hijos' => 0, 'email' => 'juan@example.com',
            'celular' => '70000000', 'direccion_actual' => 'Calle 2', 'fecha_ingreso_fundacion' => '2020-01-15',
            'cargo_actual' => 'Educador', 'oficina_actual' => 'Oficina Central La Paz',
            'latitude' => -16.5, 'longitude' => -68.13, 'cf_turnstile_response' => 'token',
        ], $cambios);
    }

    private function datosSolicitud(): array
    {
        return [
            'nombre' => 'Ana', 'paterno' => 'Rojas', 'materno' => 'Paz', 'email' => 'ana@example.com',
            'password' => 'Segura123', 'password_confirmation' => 'Segura123', 'cf_turnstile_response' => 'token',
        ];
    }

    public function test_registro_de_cuentas_esta_cerrado_por_defecto(): void
    {
        $this->get(route('registro.cuenta'))->assertRedirect(route('login'));
        $this->post(route('registro.cuenta.store'), $this->datosSolicitud())->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'ana@example.com']);
    }

    public function test_solicitud_queda_pendiente_y_no_puede_entrar_hasta_aprobarse(): void
    {
        Configuracion::guardar(['registro_cuentas_abierto' => true]);

        $this->post(route('registro.cuenta.store'), $this->datosSolicitud())->assertRedirect(route('login'));

        $ana = User::firstWhere('email', 'ana@example.com');
        $this->assertTrue($ana->pendiente);
        $this->assertFalse($ana->estado);
        $this->assertTrue($ana->hasRole('usuario'));

        $this->post(route('login.submit'), ['email' => 'ana@example.com', 'password' => 'Segura123', 'cf_turnstile_response' => 'token'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta está pendiente de aprobación por un administrador.']);
        $this->assertGuest();

        $this->actingAs($this->admin)->put(route('usuarios.estado', $ana->id), ['estado' => true])->assertSessionHas('error');
        $this->actingAs($this->admin)->put(route('usuarios.aprobar', $ana->id), ['rol' => 'encargado'])->assertSessionHas('success');

        $ana->refresh();
        $this->assertFalse($ana->pendiente);
        $this->assertTrue($ana->estado);
        $this->assertTrue($ana->hasRole('encargado'));
    }

    public function test_rechazar_elimina_solo_solicitudes_pendientes(): void
    {
        $pendiente = User::factory()->create(['rol' => 'usuario', 'estado' => false]);
        $pendiente->forceFill(['pendiente' => true])->save();
        $activo = User::factory()->create();

        $this->actingAs($this->admin)->delete(route('usuarios.rechazar', $activo->id))->assertNotFound();
        $this->actingAs($this->admin)->delete(route('usuarios.rechazar', $pendiente->id))->assertSessionHas('success');

        $this->assertModelMissing($pendiente);
        $this->assertModelExists($activo);
    }

    public function test_vincular_ficha_por_ci_y_evitar_duplicados(): void
    {
        $persona = $this->persona();
        $cuenta = User::factory()->create(['rol' => 'usuario']);
        $otra = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($this->admin)->put(route('usuarios.ficha', $cuenta->id), ['ci' => '999'])->assertSessionHasErrors('ci');
        $this->actingAs($this->admin)->put(route('usuarios.ficha', $cuenta->id), ['ci' => '111'])->assertSessionHas('success');
        $this->assertSame($persona->id, $cuenta->fresh()->id_persona);

        $this->actingAs($this->admin)->put(route('usuarios.ficha', $otra->id), ['ci' => '111'])->assertSessionHasErrors('ci');

        $this->actingAs($this->admin)->put(route('usuarios.ficha', $cuenta->id), ['ci' => ''])->assertSessionHas('success');
        $this->assertNull($cuenta->fresh()->id_persona);
    }

    public function test_cuenta_vinculada_corrige_su_ficha_y_vuelve_a_revision(): void
    {
        $persona = $this->persona();
        $cuenta = User::factory()->create(['rol' => 'usuario']);
        $cuenta->forceFill(['id_persona' => $persona->id])->save();

        $this->actingAs($cuenta)->get(route('mi_ficha.edit'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->component('MiFicha/Index')->where('vinculada', true));

        $this->actingAs($cuenta)->post(route('mi_ficha.update'), [...$this->datosFicha(['ci' => '999']), '_method' => 'put'])
            ->assertRedirect(route('mi_ficha.edit'));

        $persona->refresh();
        $this->assertSame('juan corregido', $persona->nombres);
        $this->assertSame('111', $persona->ci);
        $this->assertSame('pendiente', $persona->estado);
        $this->assertSame('Ficha corregida por la persona desde su cuenta.', $persona->revisiones()->latest('id')->value('motivo'));
    }

    public function test_cuenta_sin_ficha_no_puede_guardar(): void
    {
        $cuenta = User::factory()->create(['rol' => 'usuario']);

        $this->actingAs($cuenta)->get(route('mi_ficha.edit'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('vinculada', false));
        $this->actingAs($cuenta)->put(route('mi_ficha.update'), $this->datosFicha())->assertNotFound();
    }

    public function test_enlace_de_correccion_funciona_una_sola_vez(): void
    {
        $persona = $this->persona();

        $respuesta = $this->actingAs($this->admin)->post(route('personal.enlace', $persona->id))->assertSessionHas('enlace');
        $url = $respuesta->getSession()->get('enlace');
        $token = basename($url);

        $this->assertDatabaseMissing('enlaces_correccion', ['token_hash' => $token]);
        $this->assertDatabaseHas('enlaces_correccion', ['token_hash' => hash('sha256', $token)]);

        auth()->logout();

        $this->get($url)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->component('Correccion/Index')->where('valido', true));

        $this->post(route('correccion.update', $token), $this->datosFicha(['ci' => '999']))
            ->assertRedirect(route('registro.exitoso'));

        $persona->refresh();
        $this->assertSame('juan corregido', $persona->nombres);
        $this->assertSame('111', $persona->ci);
        $this->assertSame('pendiente', $persona->estado);
        $this->assertNull($persona->revisiones()->latest('id')->value('id_usuario'));

        $this->get($url)->assertNotFound();
        $this->post(route('correccion.update', $token), $this->datosFicha(['nombres' => 'Otra vez']))->assertRedirect($url);
        $this->assertSame('juan corregido', $persona->fresh()->nombres);
    }

    public function test_enlace_vencido_o_reemplazado_no_sirve(): void
    {
        $persona = $this->persona();
        $viejo = EnlaceCorreccion::generar($persona, $this->admin, 7);
        $nuevo = EnlaceCorreccion::generar($persona, $this->admin, 7);

        $this->get(route('correccion.edit', $viejo))->assertNotFound();
        $this->get(route('correccion.edit', $nuevo))->assertOk();

        $this->travel(8)->days();
        $this->get(route('correccion.edit', $nuevo))->assertNotFound();
    }

    public function test_revocar_y_permiso_para_generar_enlaces(): void
    {
        $persona = $this->persona();
        $encargado = User::factory()->create(['rol' => 'encargado']);

        $this->actingAs($encargado)->post(route('personal.enlace', $persona->id))->assertForbidden();

        $token = EnlaceCorreccion::generar($persona, $this->admin, 7);
        $this->actingAs($this->admin)->delete(route('personal.enlace.revocar', $persona->id))->assertSessionHas('success');
        $this->get(route('correccion.edit', $token))->assertNotFound();
    }

    public function test_pdf_se_genera_en_el_servidor_con_mapa(): void
    {
        $tile = imagecreatetruecolor(256, 256);
        ob_start();
        imagepng($tile);
        $png = ob_get_clean();
        Http::fake(['tile.openstreetmap.org/*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);

        $persona = $this->persona(['latitude' => -16.5, 'longitude' => -68.13]);

        $this->actingAs($this->admin)->get(route('personal.ficha.pdf', $persona->id))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'tile.openstreetmap.org/'.MapaEstatico::ZOOM.'/'));
        $this->assertTrue(Activity::where('event', 'pdf_generado')->exists());
    }
}
