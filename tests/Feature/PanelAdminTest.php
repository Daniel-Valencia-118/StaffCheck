<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_crea_rol_y_le_asigna_permisos(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), ['nombre' => 'auditor'])->assertSessionHas('success');
        $rol = Role::findByName('auditor');
        $this->assertTrue($rol->hasPermissionTo('panel.acceder'));

        $this->actingAs($this->admin)->put(route('roles.update', $rol->id), [
            'permisos' => ['panel.acceder', 'auditoria.ver'],
        ])->assertSessionHas('success');

        $this->assertEqualsCanonicalizing(['panel.acceder', 'auditoria.ver'], $rol->fresh()->permissions->pluck('name')->all());
    }

    public function test_nombre_de_rol_invalido_o_repetido(): void
    {
        $this->actingAs($this->admin)->post(route('roles.store'), ['nombre' => 'Jefe Oficina'])->assertSessionHasErrors('nombre');
        $this->actingAs($this->admin)->post(route('roles.store'), ['nombre' => 'encargado'])->assertSessionHasErrors('nombre');
    }

    public function test_roles_del_sistema_y_con_usuarios_no_se_eliminan(): void
    {
        $encargado = Role::findByName('encargado');
        $this->actingAs($this->admin)->delete(route('roles.destroy', $encargado->id))->assertSessionHas('error');

        Role::create(['name' => 'temporal']);
        User::factory()->create(['rol' => 'temporal']);
        $this->actingAs($this->admin)->delete(route('roles.destroy', Role::findByName('temporal')->id))->assertSessionHas('error');

        $vacio = Role::create(['name' => 'vacio']);
        $this->actingAs($this->admin)->delete(route('roles.destroy', $vacio->id))->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['name' => 'vacio']);
    }

    public function test_rol_admin_no_se_edita(): void
    {
        $admin = Role::findByName('admin');

        $this->actingAs($this->admin)->put(route('roles.update', $admin->id), ['permisos' => []])->assertForbidden();
    }

    public function test_quien_gestiona_roles_no_otorga_permisos_que_no_tiene(): void
    {
        Role::create(['name' => 'jefe'])->givePermissionTo(['panel.acceder', 'roles.gestionar']);
        $jefe = User::factory()->create(['rol' => 'jefe']);
        $otro = Role::create(['name' => 'otro']);

        $this->actingAs($jefe)->put(route('roles.update', $otro->id), ['permisos' => ['usuarios.gestionar']])->assertForbidden();
        $this->actingAs($jefe)->put(route('roles.update', $otro->id), ['permisos' => ['panel.acceder']])->assertSessionHas('success');
    }

    public function test_configuracion_se_guarda_y_se_aplica_al_registro_publico(): void
    {
        $this->actingAs($this->admin)->put(route('configuracion.update'), [
            'institucion_nombre' => 'Fundación Prueba',
            'registro_abierto' => false,
            'registro_mensaje_cerrado' => 'Vuelva en enero.',
            'fichas_estado_inicial' => 'pendiente',
            'foto_max_kb' => 1024,
            'registro_cuentas_abierto' => true,
            'enlace_correccion_dias' => 3,
            'registro_max_por_hora' => 50,
            'csp_aplicar' => true,
        ])->assertSessionHas('success');

        $this->assertFalse(Configuracion::valor('registro_abierto'));
        $this->assertSame(1024, Configuracion::valor('foto_max_kb'));

        auth()->logout();

        $this->get(route('registro.personal'))->assertInertia(fn ($page) => $page
            ->where('registroAbierto', false)
            ->where('mensajeCerrado', 'Vuelva en enero.')
            ->where('institucion', 'Fundación Prueba'));

        $this->post(route('registro.personal.store'), ['nombres' => 'x'])
            ->assertSessionHasErrors(['general' => 'Vuelva en enero.']);
    }

    public function test_estado_inicial_pendiente_se_aplica_a_fichas_nuevas(): void
    {
        Configuracion::guardar(['fichas_estado_inicial' => 'pendiente']);
        Storage::fake('local');
        Storage::fake('public');
        $this->turnstileValido();

        $this->post(route('registro.personal.store'), [
            'paterno' => 'Quispe', 'materno' => 'Mamani', 'nombres' => 'Juan', 'ci' => '777',
            'ci_expedicion' => 'LP', 'sexo' => 'M', 'fecha_nacimiento' => '1990-05-10',
            'lugar_nacimiento_provincia' => 'Murillo', 'lugar_nacimiento_ciudad' => 'La Paz',
            'estado_civil' => 'Soltero/a', 'numero_hijos' => 0, 'email' => 'juan@example.com',
            'celular' => '70000000', 'direccion_actual' => 'Calle 1', 'fecha_ingreso_fundacion' => '2020-01-15',
            'cargo_actual' => 'Educador', 'oficina_actual' => 'Oficina Central La Paz',
            'foto' => UploadedFile::fake()->image('foto.jpg', 1600, 1200),
            'latitude' => -16.5, 'longitude' => -68.13, 'cf_turnstile_response' => 'token',
        ])->assertRedirect(route('registro.exitoso'));

        $this->assertSame('pendiente', Persona::firstWhere('ci', '777')->estado);
    }

    public function test_configuracion_valida_valores(): void
    {
        $this->actingAs($this->admin)->put(route('configuracion.update'), [
            'institucion_nombre' => '',
            'registro_abierto' => true,
            'fichas_estado_inicial' => 'rechazado',
            'foto_max_kb' => 99999,
        ])->assertSessionHasErrors(['institucion_nombre', 'fichas_estado_inicial', 'foto_max_kb']);
    }

    public function test_acciones_importantes_quedan_en_auditoria(): void
    {
        $persona = Persona::create([
            'paterno' => 'quispe', 'materno' => 'mamani', 'nombres' => 'juan', 'ci' => '111',
            'ci_expedicion' => 'LP', 'sexo' => 'm', 'fecha_nacimiento' => '1990-01-01', 'estado' => 'aprobado',
        ]);

        $this->actingAs($this->admin)->get(route('personal.show', $persona->id));
        $this->actingAs($this->admin)->put(route('personal.estado', $persona->id), ['estado' => 'observado', 'motivo' => 'Falta la foto']);
        $this->actingAs($this->admin)->get(route('personal.export', ['search' => 'quispe']));

        $eventos = Activity::where('log_name', 'personal')->pluck('event')->all();
        $this->assertEqualsCanonicalizing(['ficha_vista', 'estado_cambiado', 'exportado'], $eventos);

        $cambio = Activity::firstWhere('event', 'estado_cambiado');
        $this->assertTrue($cambio->causer->is($this->admin));
        $this->assertSame('observado', $cambio->properties['despues']);
        $this->assertArrayHasKey('ip', $cambio->properties->all());
    }

    public function test_login_y_login_fallido_se_auditan(): void
    {
        $this->turnstileValido();
        $encargado = User::factory()->create();

        $this->post(route('login.submit'), ['email' => $encargado->email, 'password' => 'mala', 'cf_turnstile_response' => 't']);
        $this->post(route('login.submit'), ['email' => $encargado->email, 'password' => 'password', 'cf_turnstile_response' => 't']);

        $this->assertSame(['login_fallido', 'login'], Activity::where('log_name', 'auth')->orderBy('id')->pluck('event')->all());
        $this->assertSame($encargado->email, Activity::firstWhere('event', 'login_fallido')->properties['email']);
    }

    public function test_auditoria_lista_y_filtra(): void
    {
        activity('usuarios')->event('creado')->log('Creó el usuario a@example.com');
        activity('roles')->event('creado')->log('Creó el rol auditor');

        $this->actingAs($this->admin)->get(route('auditoria.index', ['modulo' => 'roles']))
            ->assertInertia(fn ($page) => $page
                ->component('Auditoria/Index')
                ->has('actividades.data', 1)
                ->where('actividades.data.0.descripcion', 'Creó el rol auditor'));
    }

    public function test_sistema_muestra_estado_y_sincroniza_permisos(): void
    {
        Permission::findByName('auditoria.ver')->delete();
        config(['app.deploy' => true]);

        $this->actingAs($this->admin)->get(route('sistema.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Sistema/Index')
                ->where('despliegue', true)
                ->where('migracionesPendientes', [])
                ->has('aplicacion.php'));

        $this->actingAs($this->admin)->post(route('sistema.permisos'))->assertSessionHas('success');
        $this->assertNotNull(Permission::findByName('auditoria.ver'));

        $this->actingAs($this->admin)->post(route('sistema.cache'))->assertSessionHas('success');
    }

    public function test_sin_modo_despliegue_las_tareas_de_instalacion_estan_bloqueadas(): void
    {
        config(['app.deploy' => false]);

        $this->actingAs($this->admin)->get(route('sistema.index'))
            ->assertInertia(fn ($page) => $page->where('despliegue', false)->where('migracionesPendientes', []));

        foreach (['sistema.cache', 'sistema.permisos', 'sistema.storage', 'sistema.fotos'] as $ruta) {
            $this->actingAs($this->admin)->post(route($ruta))->assertForbidden();
        }

        $this->actingAs($this->admin)->post(route('sistema.auditoria'))->assertSessionHas('success');
    }

    public function test_encargado_no_accede_a_administracion(): void
    {
        $encargado = User::factory()->create();

        foreach (['roles.index', 'configuracion.index', 'auditoria.index', 'sistema.index'] as $ruta) {
            $this->actingAs($encargado)->get(route($ruta))->assertForbidden();
        }
        $this->actingAs($encargado)->post(route('sistema.cache'))->assertForbidden();
        $this->actingAs($encargado)->put(route('configuracion.update'), [])->assertForbidden();
    }
}
