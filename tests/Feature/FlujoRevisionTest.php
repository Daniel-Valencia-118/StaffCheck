<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FlujoRevisionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $encargado;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
        $this->encargado = User::factory()->create(['rol' => 'encargado']);
    }

    private function persona(array $cambios = []): Persona
    {
        $persona = Persona::create(array_merge([
            'paterno' => 'quispe', 'materno' => 'mamani', 'nombres' => 'juan', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => 'pendiente',
            'email' => 'juan@example.com', 'estado_civil' => 'soltero/a', 'oficina_actual' => 'oficina central la paz',
            'url_foto' => '/storage/personas/fotos/vieja.jpg',
        ], $cambios));

        $persona->estudios()->createMany([
            ['tipo' => 'bachiller', 'titulo_obtenido' => 'bachiller', 'institucion' => 'colegio', 'anio' => 2008],
            ['tipo' => 'licenciatura', 'titulo_obtenido' => 'lic.', 'institucion' => 'umsa', 'anio' => 2014],
        ]);
        $persona->experienciasLaborales()->create(['institucion' => 'ong', 'cargo' => 'educador', 'fecha_inicio' => '2015-01-01']);

        return $persona;
    }

    private function datosFicha(array $cambios = []): array
    {
        return array_merge([
            '_method' => 'put',
            'paterno' => 'Quispe', 'materno' => 'Mamani', 'nombres' => 'Juan Carlos', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'M', 'fecha_nacimiento' => '1990-01-01',
            'lugar_nacimiento_provincia' => 'Murillo', 'lugar_nacimiento_ciudad' => 'La Paz',
            'estado_civil' => 'CASADO/A', 'numero_hijos' => 1, 'email' => 'juan@example.com',
            'celular' => '70000000', 'direccion_actual' => 'Calle 1', 'fecha_ingreso_fundacion' => '2020-01-15',
            'cargo_actual' => 'Educador', 'oficina_actual' => 'Oficina Central La Paz',
            'latitude' => -16.5, 'longitude' => -68.13,
            'estudios' => [['tipo' => 'maestria', 'titulo_obtenido' => 'msc', 'institucion' => 'umsa', 'anio' => 2020]],
        ], $cambios);
    }

    private function otorgar(string ...$permisos): void
    {
        Role::findByName('encargado')->givePermissionTo($permisos);
    }

    public function test_observar_exige_motivo_y_queda_en_el_historial(): void
    {
        $persona = $this->persona();

        $this->actingAs($this->admin)->put(route('personal.estado', $persona->id), ['estado' => 'observado'])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($this->admin)->put(route('personal.estado', $persona->id), ['estado' => 'observado', 'motivo' => 'Falta el título'])
            ->assertSessionHas('success');

        $revision = $persona->revisiones()->sole();
        $this->assertSame('pendiente', $revision->estado_anterior);
        $this->assertSame('observado', $revision->estado_nuevo);
        $this->assertSame('Falta el título', $revision->motivo);
        $this->assertTrue($revision->usuario->is($this->admin));
        $this->assertSame('observado', $persona->fresh()->estado);
    }

    public function test_aprobar_no_exige_motivo_y_no_repite_estado(): void
    {
        $persona = $this->persona();

        $this->actingAs($this->admin)->put(route('personal.estado', $persona->id), ['estado' => 'aprobado'])
            ->assertSessionHas('success');
        $this->actingAs($this->admin)->put(route('personal.estado', $persona->id), ['estado' => 'aprobado'])
            ->assertSessionHasErrors('estado');

        $this->assertSame(1, $persona->revisiones()->count());
    }

    public function test_ficha_muestra_el_historial(): void
    {
        $persona = $this->persona();
        $persona->cambiarEstado('rechazado', 'Datos falsos', $this->admin);

        $this->actingAs($this->admin)->get(route('personal.show', $persona->id))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->component('Personal/Show')
                ->has('revisiones', 1)
                ->where('revisiones.0.motivo', 'Datos falsos')
                ->where('estadosConMotivo', Persona::ESTADOS_CON_MOTIVO));
    }

    public function test_encargado_edita_solo_con_el_permiso(): void
    {
        $persona = $this->persona();

        $this->actingAs($this->encargado)->get(route('personal.edit', $persona->id))->assertForbidden();
        $this->actingAs($this->encargado)->post(route('personal.update', $persona->id), $this->datosFicha())->assertForbidden();

        $this->otorgar('personal.editar');

        $this->actingAs($this->encargado)->get(route('personal.edit', $persona->id))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->component('Personal/Editar')
                ->where('ficha.sexo', 'M')
                ->where('ficha.estado_civil', 'SOLTERO/A')
                ->where('ficha.oficina_actual', 'Oficina Central La Paz')
                ->where('ficha.fecha_nacimiento', '1990-01-01')
                ->has('ficha.estudios', 2));

        $this->actingAs($this->encargado)->post(route('personal.update', $persona->id), $this->datosFicha())
            ->assertRedirect(route('personal.show', $persona->id));

        $persona->refresh();
        $this->assertSame('juan carlos', $persona->nombres);
        $this->assertSame('pendiente', $persona->estado);
        $this->assertSame(['maestria'], $persona->estudios()->pluck('tipo')->all());
        $this->assertSame(0, $persona->experienciasLaborales()->count());
        $this->assertSame('/storage/personas/fotos/vieja.jpg', $persona->getRawOriginal('url_foto'));
    }

    public function test_editar_con_foto_nueva_borra_la_anterior(): void
    {
        Storage::disk('public')->put('personas/fotos/vieja.jpg', 'x');
        $persona = $this->persona();

        $this->actingAs($this->admin)->post(route('personal.update', $persona->id), $this->datosFicha([
            'foto' => UploadedFile::fake()->image('nueva.jpg', 1600, 1200),
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('personas/fotos/vieja.jpg');
        $this->assertCount(1, Storage::disk('local')->files('personas/fotos'));
        $this->assertStringStartsWith('personas/fotos/', $persona->fresh()->getRawOriginal('url_foto'));
    }

    public function test_editar_valida_ci_y_email_unicos_sin_contar_la_propia_ficha(): void
    {
        $persona = $this->persona();
        $this->persona(['ci' => '222', 'email' => 'otra@example.com']);

        $this->actingAs($this->admin)->post(route('personal.update', $persona->id), $this->datosFicha())
            ->assertSessionHasNoErrors();
        $this->actingAs($this->admin)->post(route('personal.update', $persona->id), $this->datosFicha(['ci' => '222', 'email' => 'otra@example.com']))
            ->assertSessionHasErrors(['ci', 'email']);
    }

    public function test_edicion_se_audita_sin_guardar_los_datos(): void
    {
        $persona = $this->persona();

        $this->actingAs($this->admin)->post(route('personal.update', $persona->id), $this->datosFicha());

        $actividad = Activity::firstWhere('event', 'ficha_editada');
        $this->assertContains('nombres', $actividad->properties['campos']);
        $this->assertNotContains('juan carlos', $actividad->properties->flatten()->all());
    }

    public function test_admin_envia_a_papelera_y_restaura(): void
    {
        $persona = $this->persona();

        $this->actingAs($this->admin)->delete(route('personal.destroy', $persona->id))
            ->assertRedirect(route('personal.index'));

        $this->assertSoftDeleted($persona);
        $this->actingAs($this->admin)->get(route('personal.show', $persona->id))->assertNotFound();
        $this->actingAs($this->admin)->get(route('personal.index'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->has('personal.data', 0));
        $this->actingAs($this->admin)->get(route('personal.index', ['papelera' => 1]))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->has('personal.data', 1));

        $this->actingAs($this->admin)->put(route('personal.restore', $persona->id))->assertSessionHas('success');
        $this->assertNotSoftDeleted($persona);
        $this->assertSame(2, $persona->estudios()->count());
    }

    public function test_papelera_es_exclusiva_del_admin(): void
    {
        $persona = $this->persona();
        Role::findByName('encargado')->givePermissionTo('personal.eliminar');

        $this->actingAs($this->encargado)->delete(route('personal.destroy', $persona->id))->assertForbidden();
        $this->actingAs($this->encargado)->get(route('personal.index', ['papelera' => 1]))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->has('personal.data', 1)->missing('filters.papelera'));
        $this->assertNotSoftDeleted($persona);
    }

    public function test_permiso_de_papelera_no_se_asigna_a_roles(): void
    {
        $rol = Role::findByName('encargado');

        $this->actingAs($this->admin)->put(route('roles.update', $rol->id), ['permisos' => ['panel.acceder', 'personal.eliminar']])
            ->assertSessionHasErrors('permisos.1');

        $this->actingAs($this->admin)->get(route('roles.index'))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->where('permisos', fn ($permisos) => collect($permisos)->pluck('nombre')->doesntContain('personal.eliminar')));
    }

    public function test_lista_filtra_por_estado_y_cuenta_por_estado(): void
    {
        $this->persona();
        $this->persona(['ci' => '222', 'email' => 'otra@example.com', 'estado' => 'aprobado']);

        $this->actingAs($this->encargado)->get(route('personal.index', ['estado' => 'aprobado']))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->has('personal.data', 1)
                ->where('personal.data.0.ci', '222')
                ->where('porEstado.pendiente', 1)
                ->where('porEstado.aprobado', 1));
    }
}
