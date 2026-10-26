<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Support\FotosPersonal;
use App\Support\MapaEstatico;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class SistemaController extends Controller
{
    public function index()
    {
        $despliegue = (bool) config('app.deploy');

        return Inertia::render('Sistema/Index', [
            'despliegue' => $despliegue,
            'aplicacion' => [
                'version' => $this->version(),
                'entorno' => app()->environment(),
                'debug' => (bool) config('app.debug'),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'zona_horaria' => config('app.timezone'),
                'cache' => config('cache.default'),
                'sesiones' => config('session.driver'),
            ],
            'migracionesPendientes' => $despliegue ? $this->migracionesPendientes() : [],
            'almacenamiento' => [
                ...FotosPersonal::resumen(),
                'mapas' => count(Storage::disk(FotosPersonal::DISCO)->files(MapaEstatico::CARPETA_CACHE)),
                'enlace_publico' => is_dir(public_path('storage')),
                'gd' => MapaEstatico::disponible(),
            ],
            'auditoria' => [
                'registros' => Activity::count(),
                'mas_antiguo' => Activity::min('created_at'),
                'dias_retencion' => (int) config('activitylog.delete_records_older_than_days'),
            ],
        ]);
    }

    public function limpiarCache()
    {
        Artisan::call('optimize:clear');
        Storage::disk(FotosPersonal::DISCO)->deleteDirectory(MapaEstatico::CARPETA_CACHE);

        Auditoria::registrar('sistema', 'cache_limpiada', 'Limpió las cachés de la aplicación');

        return back()->with('success', 'Cachés limpiadas (configuración, rutas, vistas, permisos, configuración del sistema y mapas del PDF).');
    }

    public function protegerFotos()
    {
        ['movidas' => $movidas, 'fallidas' => $fallidas] = FotosPersonal::protegerAntiguas();

        Auditoria::registrar('sistema', 'fotos_protegidas', "Movió {$movidas} fotos a la carpeta privada", null, ['movidas' => $movidas, 'fallidas' => $fallidas]);

        if ($fallidas > 0) {
            return back()->with('error', "Se movieron {$movidas} fotos, pero {$fallidas} no se pudieron copiar y siguen en la carpeta pública. Revise el espacio en disco y vuelva a intentarlo.");
        }

        return back()->with('success', $movidas > 0 ? "Se movieron {$movidas} fotos a la carpeta privada." : 'No había fotos en la carpeta pública.');
    }

    public function sincronizarPermisos()
    {
        (new RolesPermisosSeeder)->run();

        Auditoria::registrar('sistema', 'permisos_sincronizados', 'Sincronizó el catálogo de permisos');

        return back()->with('success', 'Catálogo de permisos sincronizado.');
    }

    public function crearEnlaceArchivos()
    {
        if (is_dir(public_path('storage'))) {
            return back()->with('success', 'La carpeta public/storage ya existe.');
        }

        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'El hosting no permitió crear el enlace. Cree la carpeta public/storage manualmente desde el administrador de archivos.');
        }

        if (! is_dir(public_path('storage'))) {
            return back()->with('error', 'El hosting no permitió crear el enlace. Cree la carpeta public/storage manualmente desde el administrador de archivos.');
        }

        Auditoria::registrar('sistema', 'enlace_archivos_creado', 'Creó el enlace public/storage');

        return back()->with('success', 'Enlace de archivos creado.');
    }

    public function limpiarAuditoria()
    {
        $antes = Activity::count();
        Artisan::call('activitylog:clean', ['--force' => true]);
        $borrados = $antes - Activity::count();

        Auditoria::registrar('sistema', 'auditoria_depurada', "Depuró {$borrados} registros de auditoría antiguos");

        return back()->with('success', "Se eliminaron {$borrados} registros de auditoría antiguos.");
    }

    private function version(): ?string
    {
        $archivo = base_path('version.txt');

        return is_file($archivo) ? trim(File::get($archivo)) : null;
    }

    private function migracionesPendientes(): array
    {
        $ejecutadas = DB::table('migrations')->pluck('migration');

        return collect(File::files(database_path('migrations')))
            ->map(fn ($archivo) => $archivo->getFilenameWithoutExtension())
            ->diff($ejecutadas)
            ->sort()
            ->values()
            ->all();
    }
}
