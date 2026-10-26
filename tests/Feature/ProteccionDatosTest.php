<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\EnlaceCorreccion;
use App\Models\Persona;
use App\Models\User;
use App\Support\FichaPersonal;
use App\Support\FotosPersonal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProteccionDatosTest extends TestCase
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
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => 'aprobado',
            'email' => 'juan@example.com', 'estado_civil' => 'soltero/a', 'oficina_actual' => 'oficina central la paz',
        ], $cambios));
    }

    private function datosRegistro(array $cambios = []): array
    {
        return array_merge([
            'paterno' => 'Rojas', 'materno' => 'Paz', 'nombres' => 'Ana', 'ci' => '222',
            'ci_expedicion' => 'LP', 'sexo' => 'F', 'fecha_nacimiento' => '1992-03-04',
            'lugar_nacimiento_provincia' => 'Murillo', 'lugar_nacimiento_ciudad' => 'La Paz',
            'estado_civil' => 'SOLTERO/A', 'numero_hijos' => 0, 'email' => 'ana@example.com',
            'celular' => '70000001', 'direccion_actual' => 'Calle 3', 'fecha_ingreso_fundacion' => '2021-02-01',
            'cargo_actual' => 'Educadora', 'oficina_actual' => 'Oficina Central La Paz',
            'foto' => UploadedFile::fake()->image('foto.jpg', 400, 300),
            'latitude' => -16.5, 'longitude' => -68.13, 'cf_turnstile_response' => 'token',
        ], $cambios);
    }

    public function test_la_foto_solo_se_ve_con_permiso_o_siendo_la_propia_persona(): void
    {
        Storage::disk('local')->put('personas/fotos/nueva.jpg', UploadedFile::fake()->image('x.jpg')->getContent());
        $persona = $this->persona(['url_foto' => 'personas/fotos/nueva.jpg']);
        $url = route('personal.foto', $persona->id);

        $this->get($url)->assertRedirect(route('login'));

        $ajena = User::factory()->create(['rol' => 'usuario']);
        $this->actingAs($ajena)->get($url)->assertForbidden();

        $propia = User::factory()->create(['rol' => 'usuario']);
        $propia->forceFill(['id_persona' => $persona->id])->save();
        $this->actingAs($propia)->get($url)->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $this->actingAs($this->admin)->get($url)
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=86400, private');
    }

    public function test_fotos_antiguas_se_siguen_viendo_y_se_protegen_sin_tocar_la_ficha(): void
    {
        config(['app.deploy' => true]);
        Storage::disk('public')->put('personas/fotos/antigua.jpg', 'contenido');
        $persona = $this->persona(['url_foto' => '/storage/personas/fotos/antigua.jpg']);

        $this->actingAs($this->admin)->get(route('personal.foto', $persona->id))->assertOk();

        $this->actingAs($this->admin)->post(route('sistema.fotos'))->assertSessionHas('success');

        Storage::disk('public')->assertMissing('personas/fotos/antigua.jpg');
        $this->assertSame('contenido', Storage::disk('local')->get('personas/fotos/antigua.jpg'));
        $this->assertSame('/storage/personas/fotos/antigua.jpg', $persona->fresh()->getRawOriginal('url_foto'));
        $this->actingAs($this->admin)->get(route('personal.foto', $persona->id))->assertOk();

        $this->actingAs($this->admin)->post(route('sistema.fotos'))->assertSessionHas('success', 'No había fotos en la carpeta pública.');
    }

    public function test_la_ficha_entrega_la_url_protegida_de_la_foto(): void
    {
        $persona = $this->persona(['url_foto' => '/storage/personas/fotos/antigua.jpg']);

        $this->actingAs($this->admin)->get(route('personal.show', $persona->id))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->where('persona.url_foto', fn ($url) => str_starts_with($url, route('personal.foto', $persona->id))));
    }

    public function test_el_enlace_de_correccion_muestra_la_foto_solo_mientras_es_valido(): void
    {
        Storage::disk('local')->put('personas/fotos/nueva.jpg', 'x');
        $persona = $this->persona(['url_foto' => 'personas/fotos/nueva.jpg']);
        $token = EnlaceCorreccion::generar($persona, $this->admin, 7);

        $this->get(route('correccion.edit', $token))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('fotoActual', route('correccion.foto', $token)));
        $this->get(route('correccion.foto', $token))->assertOk()->assertHeader('Cache-Control', 'no-store, private');

        EnlaceCorreccion::activoDe($persona)->forceFill(['usado_at' => now()])->save();
        $this->get(route('correccion.foto', $token))->assertNotFound();
    }

    public function test_rechaza_imagenes_que_no_se_pueden_leer(): void
    {
        $this->post(route('registro.personal.store'), $this->datosRegistro([
            'foto' => UploadedFile::fake()->createWithContent('foto.jpg', "\xFF\xD8\xFF\xE0basura"),
        ]))->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('personas', 0);
    }

    public function test_el_registro_publico_no_dice_que_dato_ya_existe(): void
    {
        $this->persona(['ci' => '222']);

        $this->post(route('registro.personal.store'), $this->datosRegistro())
            ->assertSessionHasErrors(['ci' => FichaPersonal::MENSAJE_DUPLICADO]);

        $persona = $this->persona(['ci' => '333', 'email' => 'otra@example.com']);
        $this->actingAs($this->admin)->post(route('personal.update', $persona->id), [...$this->datosRegistro(['ci' => '222', 'foto' => null]), '_method' => 'put'])
            ->assertSessionHasErrors(['ci' => 'Ya existe una ficha con este número de CI.']);
    }

    public function test_solicitud_de_cuenta_con_correo_existente_responde_igual_sin_crear_nada(): void
    {
        Configuracion::guardar(['registro_cuentas_abierto' => true]);
        User::factory()->create(['email' => 'ana@example.com']);
        $datos = [
            'nombre' => 'Ana', 'paterno' => 'Rojas', 'email' => 'Ana@Example.com',
            'password' => 'Segura123', 'password_confirmation' => 'Segura123', 'cf_turnstile_response' => 'token',
        ];

        $this->post(route('registro.cuenta.store'), $datos)
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame(1, User::where('email', 'ana@example.com')->count());
    }

    public function test_limite_por_hora_del_registro_publico_es_configurable(): void
    {
        Configuracion::guardar(['registro_max_por_hora' => 5]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('registro.personal.store'), ['cf_turnstile_response' => 'token'])->assertSessionDoesntHaveErrors('general');
        }

        $this->post(route('registro.personal.store'), ['cf_turnstile_response' => 'token'])
            ->assertSessionHasErrors(['general' => 'Se alcanzó el límite de envíos por hora desde esta red. Intente más tarde o comuníquese con Recursos Humanos.']);
    }

    public function test_csp_va_en_modo_informe_y_se_puede_aplicar(): void
    {
        $respuesta = $this->get(route('login'));
        $politica = $respuesta->headers->get('Content-Security-Policy-Report-Only');

        $this->assertNotNull($politica);
        $this->assertFalse($respuesta->headers->has('Content-Security-Policy'));
        $this->assertMatchesRegularExpression("/'nonce-([^']+)'/", $politica);
        preg_match("/'nonce-([^']+)'/", $politica, $nonce);
        $this->assertStringContainsString('nonce="'.$nonce[1].'"', $respuesta->getContent());

        Configuracion::guardar(['csp_aplicar' => true]);
        $this->assertTrue($this->get(route('login'))->headers->has('Content-Security-Policy'));
    }

    public function test_la_busqueda_trata_comodines_como_texto(): void
    {
        $this->persona();

        $this->actingAs($this->admin)->get(route('personal.index', ['search' => '%']))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('personal.total', 0));
        $this->actingAs($this->admin)->get(route('personal.index', ['search' => 'quis']))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('personal.total', 1));
    }

    public function test_los_kpis_se_actualizan_al_guardar_una_ficha(): void
    {
        $this->persona();
        $this->actingAs($this->admin)->get(route('personal.index'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('stats.totalRegistrados', 1));

        $this->persona(['ci' => '999', 'email' => 'otro@example.com']);
        $this->actingAs($this->admin)->get(route('personal.index'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('stats.totalRegistrados', 2));
    }

    public function test_la_foto_del_pdf_se_recorta_sin_deformarse(): void
    {
        Storage::disk('local')->put('personas/fotos/ancha.jpg', UploadedFile::fake()->image('x.jpg', 800, 300)->getContent());

        $uri = FotosPersonal::dataUriRecortada('personas/fotos/ancha.jpg', 315, 345);
        [$ancho, $alto] = getimagesizefromstring(base64_decode(substr($uri, strlen('data:image/jpeg;base64,'))));

        $this->assertSame([315, 345], [$ancho, $alto]);
        $this->assertNull(FotosPersonal::dataUriRecortada(null, 315, 345));
    }

    public function test_el_mapa_del_pdf_se_guarda_y_no_se_vuelve_a_pedir(): void
    {
        $tile = imagecreatetruecolor(256, 256);
        ob_start();
        imagepng($tile);
        Http::fake(['tile.openstreetmap.org/*' => Http::response(ob_get_clean(), 200, ['Content-Type' => 'image/png'])]);

        $persona = $this->persona(['latitude' => -16.5, 'longitude' => -68.13]);

        $this->actingAs($this->admin)->get(route('personal.ficha.pdf', $persona->id))->assertOk();
        $pedidos = count(Http::recorded(fn ($request) => str_contains($request->url(), 'tile.openstreetmap.org')));

        $this->actingAs($this->admin)->get(route('personal.ficha.pdf', $persona->id))->assertOk();

        $this->assertGreaterThan(0, $pedidos);
        $this->assertCount($pedidos, Http::recorded(fn ($request) => str_contains($request->url(), 'tile.openstreetmap.org')));
        $this->assertCount(1, Storage::disk('local')->files('mapas'));
    }
}
