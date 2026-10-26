<?php

namespace Tests\Feature;

use App\Exports\PersonalExport;
use App\Models\Estudio;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class InterfazPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    private function persona(string $paterno, string $ci, string $estado = 'aprobado'): Persona
    {
        return Persona::create([
            'paterno' => $paterno, 'materno' => 'mamani', 'nombres' => 'juan', 'ci' => $ci,
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => $estado,
            'email' => "{$ci}@example.com",
        ]);
    }

    public function test_el_listado_se_ordena_por_columna_permitida(): void
    {
        $this->persona('zapata', '1');
        $this->persona('alvarez', '2');
        $this->persona('mendoza', '3');

        $this->actingAs($this->admin)->get(route('personal.index', ['orden' => 'paterno', 'dir' => 'asc']))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina
                ->where('personal.data.0.paterno', 'alvarez')
                ->where('personal.data.2.paterno', 'zapata')
                ->where('filters.orden', 'paterno')
                ->where('filters.dir', 'asc'));

        $this->actingAs($this->admin)->get(route('personal.index', ['orden' => 'paterno', 'dir' => 'desc']))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('personal.data.0.paterno', 'zapata'));
    }

    public function test_el_orden_ignora_columnas_no_permitidas(): void
    {
        $this->persona('quispe', '1');

        $this->actingAs($this->admin)->get(route('personal.index', ['orden' => 'id; drop table personas', 'dir' => 'asc']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->missing('filters.orden')->where('personal.total', 1));

        $this->actingAs($this->admin)->get(route('usuarios.index', ['orden' => 'password']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->missing('filters.orden'));
    }

    public function test_exportar_todo_ignora_filtros_y_la_vista_actual_los_respeta(): void
    {
        Excel::fake();
        $this->persona('quispe', '1', 'aprobado');
        $this->persona('mamani', '2', 'pendiente');
        $this->persona('choque', '3', 'pendiente');

        $this->actingAs($this->admin)->get(route('personal.export', ['estado' => 'pendiente']));
        Excel::assertDownloaded('personal.xlsx', fn (PersonalExport $export) => $export->query()->count() === 3);

        $this->actingAs($this->admin)->get(route('personal.export', ['vista' => 1, 'estado' => 'pendiente', 'orden' => 'paterno', 'dir' => 'asc']));
        Excel::assertDownloaded('personal.xlsx', fn (PersonalExport $export) => $export->query()->pluck('paterno')->all() === ['choque', 'mamani']);

        $filtros = Activity::where('event', 'exportado')->orderBy('id')->get()->map(fn ($a) => $a->properties['filtro'])->all();
        $this->assertSame('todos', $filtros[0]);
        $this->assertSame(['estado' => 'pendiente', 'orden' => 'paterno', 'dir' => 'asc'], $filtros[1]);
    }

    public function test_cuenta_sin_apellido_materno_puede_guardar_sus_datos(): void
    {
        $usuario = User::factory()->create(['materno' => '']);

        $this->actingAs($usuario)->patch(route('cuenta.update'), [
            'nombre' => 'Ana', 'paterno' => 'Rojas', 'materno' => '', 'email' => $usuario->email,
        ])->assertSessionHasNoErrors();

        $this->assertSame('', $usuario->fresh()->materno);
        $this->assertSame('ana', $usuario->fresh()->nombre);
    }

    public function test_al_editar_el_tipo_de_estudio_coincide_con_las_opciones(): void
    {
        $persona = $this->persona('quispe', '1');
        Estudio::create(['id_persona' => $persona->id, 'tipo' => 'Maestría', 'titulo_obtenido' => 'x', 'institucion' => 'umsa', 'anio' => 2020]);

        $this->assertSame('maestría', $persona->estudios()->first()->getRawOriginal('tipo'));

        $this->actingAs($this->admin)->get(route('personal.edit', $persona->id))
            ->assertInertia(fn (AssertableInertia $pagina) => $pagina->where('ficha.estudios.0.tipo', 'Maestría'));
    }

    public function test_los_mensajes_de_validacion_estan_en_espanol(): void
    {
        $this->get(route('login'))->assertSee('lang="es"', false);

        $this->actingAs($this->admin)->patch(route('cuenta.update'), [
            'nombre' => 'Ana', 'paterno' => 'Rojas', 'email' => 'no-es-correo',
        ])->assertSessionHasErrors(['email' => 'El campo correo electrónico debe ser un correo electrónico válido.']);
    }
}
