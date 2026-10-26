<?php

namespace App\Http\Controllers\Gestion;

use App\Exports\PersonalExport;
use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\EnlaceCorreccion;
use App\Models\Persona;
use App\Models\RevisionFicha;
use App\Support\Auditoria;
use App\Support\Busqueda;
use App\Support\FichaPersonal;
use App\Support\Orden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class PersonalController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = in_array($request->input('estado'), Persona::ESTADOS, true) ? $request->input('estado') : null;
        $papelera = $request->boolean('papelera') && $request->user()->can('personal.eliminar');

        $query = Persona::query()
            ->when($papelera, fn ($query) => $query->onlyTrashed())
            ->select([
                'id', 'paterno', 'materno', 'nombres', 'ci', 'ci_expedicion', 'email',
                'celular', 'cargo_actual', 'oficina_actual', 'estado', 'created_at', 'deleted_at',
            ])
            ->tap(fn ($query) => Busqueda::filtrar($query, Persona::COLUMNAS_BUSQUEDA, $search))
            ->when($estado, fn ($query, $estado) => $query->where('estado', $estado));

        $orden = Orden::aplicar($query, $request->input('orden'), $request->input('dir'), Persona::COLUMNAS_ORDEN, $papelera ? 'deleted_at' : 'created_at');

        return Inertia::render('Personal/Index', [
            'personal' => $query->paginate(10)->withQueryString(),
            'filters' => array_filter(['search' => $search, 'estado' => $estado, 'papelera' => $papelera ? 1 : null, ...$orden]),
            ...Cache::remember(Persona::CACHE_RESUMEN, Persona::SEGUNDOS_RESUMEN, fn () => [
                'stats' => $this->getStats(),
                'porEstado' => Persona::query()->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado')->all(),
            ]),
        ]);
    }

    private function getStats(): array
    {
        $result = Persona::query()
            ->selectRaw('COUNT(*) as total_registrados')
            ->selectRaw('COUNT(CASE WHEN oficina_actual = ? THEN 1 END) as total_la_paz', [Persona::OFICINA_LA_PAZ])
            ->selectRaw('COUNT(CASE WHEN oficina_actual = ? THEN 1 END) as total_santa_cruz', [Persona::OFICINA_SANTA_CRUZ])
            ->selectRaw('COUNT(CASE WHEN sexo = ? THEN 1 END) as total_hombres', ['m'])
            ->selectRaw('COUNT(CASE WHEN sexo = ? THEN 1 END) as total_mujeres', ['f'])
            ->first();

        return [
            'totalRegistrados' => (int) ($result->total_registrados ?? 0),
            'totalLaPaz' => (int) ($result->total_la_paz ?? 0),
            'totalSantaCruz' => (int) ($result->total_santa_cruz ?? 0),
            'totalHombres' => (int) ($result->total_hombres ?? 0),
            'totalMujeres' => (int) ($result->total_mujeres ?? 0),
        ];
    }

    public function show($id)
    {
        $persona = Persona::with(['estudios', 'experienciasLaborales', 'referenciasLaborales', 'contactos'])
            ->findOrFail($id);

        Auditoria::registrar('personal', 'ficha_vista', "Consultó la ficha de {$persona->nombre_completo}", $persona);

        $revisiones = $persona->revisiones()
            ->with('usuario:id,nombre,paterno')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(fn (RevisionFicha $revision) => [
                'id' => $revision->id,
                'fecha' => $revision->created_at?->format('d/m/Y H:i'),
                'usuario' => $revision->usuario ? trim("{$revision->usuario->nombre} {$revision->usuario->paterno}") : 'Usuario eliminado',
                'estado_anterior' => $revision->estado_anterior,
                'estado_nuevo' => $revision->estado_nuevo,
                'motivo' => $revision->motivo,
            ]);

        $enlace = EnlaceCorreccion::activoDe($persona);
        $cuenta = $persona->usuario()->first(['id', 'email']);

        return Inertia::render('Personal/Show', [
            'persona' => $persona,
            'edad' => $persona->fecha_nacimiento?->age,
            'enlaceVence' => $enlace?->expira_at->format('d/m/Y H:i'),
            'cuentaVinculada' => $cuenta?->email,
            'revisiones' => $revisiones,
            'estados' => Persona::ESTADOS,
            'estadosConMotivo' => Persona::ESTADOS_CON_MOTIVO,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);

        $datos = $request->validate([
            'estado' => ['required', Rule::in(Persona::ESTADOS), Rule::notIn([$persona->estado])],
            'motivo' => [Rule::requiredIf(in_array($request->input('estado'), Persona::ESTADOS_CON_MOTIVO, true)), 'nullable', 'string', 'max:1000'],
        ], [
            'estado.not_in' => 'La ficha ya está en ese estado.',
            'motivo.required' => 'Indique el motivo para observar o rechazar la ficha.',
        ]);

        $anterior = $persona->estado;
        $persona->cambiarEstado($datos['estado'], $datos['motivo'] ?? null, $request->user());

        Auditoria::registrar('personal', 'estado_cambiado', "Cambió el estado de {$persona->nombre_completo} a {$persona->estado}", $persona, [
            'antes' => $anterior,
            'despues' => $persona->estado,
        ]);

        return back()->with('success', 'Estado actualizado');
    }

    public function edit($id)
    {
        $persona = Persona::with(['estudios', 'experienciasLaborales', 'referenciasLaborales', 'contactos'])
            ->findOrFail($id);

        return Inertia::render('Personal/Editar', [
            'personaId' => $persona->id,
            'nombreCompleto' => $persona->nombre_completo,
            'fotoActual' => $persona->url_foto,
            'ficha' => FichaPersonal::paraFormulario($persona),
            'fotoMaxKb' => Configuracion::valor('foto_max_kb'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);
        $fotoMaxKb = Configuracion::valor('foto_max_kb');

        $request->validate(FichaPersonal::reglas($fotoMaxKb, $persona), FichaPersonal::mensajes($fotoMaxKb));

        try {
            $cambios = FichaPersonal::actualizar($persona, $request->all(), $request->file('foto'));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo guardar la ficha. Intente nuevamente.');
        }

        Auditoria::registrar('personal', 'ficha_editada', "Editó la ficha de {$persona->nombre_completo}", $persona, [
            'campos' => $cambios,
        ]);

        return redirect()->route('personal.show', $persona->id)->with('success', 'Ficha actualizada.');
    }

    public function generarEnlace(Request $request, $id)
    {
        $persona = Persona::findOrFail($id);
        $dias = Configuracion::valor('enlace_correccion_dias');

        $token = EnlaceCorreccion::generar($persona, $request->user(), $dias);

        Auditoria::registrar('personal', 'enlace_generado', "Generó un enlace de corrección para {$persona->nombre_completo}", $persona, [
            'dias' => $dias,
        ]);

        return back()
            ->with('success', "Enlace generado. Vence en {$dias} días y sirve una sola vez.")
            ->with('enlace', route('correccion.edit', $token));
    }

    public function revocarEnlace($id)
    {
        $persona = Persona::findOrFail($id);
        EnlaceCorreccion::query()->where('id_persona', $persona->id)->whereNull('usado_at')->delete();

        Auditoria::registrar('personal', 'enlace_revocado', "Revocó el enlace de corrección de {$persona->nombre_completo}", $persona);

        return back()->with('success', 'Enlace revocado.');
    }

    public function destroy($id)
    {
        $persona = Persona::findOrFail($id);
        $persona->delete();

        Auditoria::registrar('personal', 'eliminado', "Envió a la papelera la ficha de {$persona->nombre_completo}", $persona);

        return redirect()->route('personal.index')->with('success', 'Ficha enviada a la papelera. Puede restaurarla desde la papelera.');
    }

    public function restore($id)
    {
        $persona = Persona::onlyTrashed()->findOrFail($id);
        $persona->restore();

        Auditoria::registrar('personal', 'restaurado', "Restauró la ficha de {$persona->nombre_completo}", $persona);

        return back()->with('success', 'Ficha restaurada.');
    }

    public function export(Request $request)
    {
        $filtros = $request->boolean('vista') ? array_filter([
            'search' => $request->input('search'),
            'estado' => in_array($request->input('estado'), Persona::ESTADOS, true) ? $request->input('estado') : null,
            'orden' => $request->input('orden'),
            'dir' => $request->input('dir'),
        ]) : [];

        Auditoria::registrar(
            'personal',
            'exportado',
            $filtros ? 'Exportó a Excel la vista actual del personal' : 'Exportó todo el personal a Excel',
            null,
            ['filtro' => $filtros ?: 'todos'],
        );

        return Excel::download(new PersonalExport($filtros), 'personal.xlsx');
    }
}
