<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Support\Auditoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        return Inertia::render('Configuracion/Index', [
            'valores' => Configuracion::todas(),
        ]);
    }

    public function update(Request $request)
    {
        $reglas = array_map(fn (array $definicion) => $definicion['reglas'], Configuracion::DEFINICIONES);
        $datos = $request->validate($reglas, [], [
            'institucion_nombre' => 'nombre de la institución',
            'registro_mensaje_cerrado' => 'mensaje de registro cerrado',
            'fichas_estado_inicial' => 'estado inicial de las fichas',
            'foto_max_kb' => 'tamaño máximo de la foto',
            'registro_max_por_hora' => 'envíos por hora desde una misma red',
        ]);

        $cambios = Configuracion::guardar($datos);

        if ($cambios) {
            Auditoria::registrar('configuracion', 'actualizada', 'Cambió la configuración: '.implode(', ', array_keys($cambios)), null, ['cambios' => $cambios]);
        }

        return back()->with('success', $cambios ? 'Configuración guardada.' : 'No hubo cambios.');
    }
}
