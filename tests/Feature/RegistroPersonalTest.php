<?php

namespace Tests\Feature;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistroPersonalTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'paterno' => 'Quispe',
            'materno' => 'Mamani',
            'nombres' => 'Juan',
            'ci' => '1234567',
            'ci_expedicion' => 'LP',
            'sexo' => 'M',
            'fecha_nacimiento' => '1990-05-10',
            'lugar_nacimiento_provincia' => 'Murillo',
            'lugar_nacimiento_ciudad' => 'La Paz',
            'estado_civil' => 'Soltero/a',
            'numero_hijos' => 0,
            'email' => 'juan@example.com',
            'celular' => '70000000',
            'direccion_actual' => 'Calle 1',
            'fecha_ingreso_fundacion' => '2020-01-15',
            'cargo_actual' => 'Educador',
            'oficina_actual' => 'Oficina Central La Paz',
            'foto' => UploadedFile::fake()->image('foto.jpg', 1600, 1200),
            'latitude' => -16.5,
            'longitude' => -68.13,
            'cf_turnstile_response' => 'token',
        ], $cambios);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->turnstileValido();
    }

    public function test_registro_valido_crea_la_ficha_y_guarda_la_foto(): void
    {
        $this->post(route('registro.personal.store'), $this->datos())
            ->assertRedirect(route('registro.exitoso'));

        $persona = Persona::firstWhere('ci', '1234567');
        $this->assertNotNull($persona);
        $this->assertCount(1, Storage::disk('local')->files('personas/fotos'));
        $this->assertCount(0, Storage::disk('public')->files('personas/fotos'));
        $this->assertMatchesRegularExpression('#^personas/fotos/[A-Za-z0-9]{40}\.jpg$#', $persona->getRawOriginal('url_foto'));

        [$ancho, $alto] = getimagesizefromstring(Storage::disk('local')->get($persona->getRawOriginal('url_foto')));
        $this->assertSame([1024, 768], [$ancho, $alto]);
    }

    public function test_rechaza_email_invalido_y_fecha_de_nacimiento_futura(): void
    {
        $this->post(route('registro.personal.store'), $this->datos([
            'email' => 'no-es-un-email',
            'fecha_nacimiento' => now()->addDay()->toDateString(),
        ]))->assertSessionHasErrors(['email', 'fecha_nacimiento']);

        $this->assertDatabaseCount('personas', 0);
    }

    public function test_error_al_guardar_no_expone_detalles_y_limpia_la_foto(): void
    {
        Persona::creating(fn () => throw new \RuntimeException('SQLSTATE detalle interno'));

        $respuesta = $this->post(route('registro.personal.store'), $this->datos());

        $respuesta->assertSessionHasErrors('general');
        $this->assertStringNotContainsString('SQLSTATE', session('errors')->first('general'));
        $this->assertCount(0, Storage::disk('local')->files('personas/fotos'));
    }

    public function test_registro_tiene_limite_de_envios_por_red(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post(route('registro.personal.store'), ['email' => 'x']);
        }

        $this->post(route('registro.personal.store'), $this->datos())
            ->assertSessionHasErrors(['general' => 'Demasiados envíos desde esta red. Espere un minuto e intente nuevamente.']);
    }
}
