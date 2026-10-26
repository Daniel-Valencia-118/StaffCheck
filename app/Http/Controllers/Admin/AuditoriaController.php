<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Auditoria;
use App\Support\Busqueda;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate([
            'modulo' => ['nullable', Rule::in(array_keys(Auditoria::MODULOS))],
            'search' => ['nullable', 'string', 'max:100'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $actividades = Activity::query()
            ->with('causer:id,nombre,paterno,email')
            ->when($filtros['modulo'] ?? null, fn ($q, $modulo) => $q->where('log_name', $modulo))
            ->tap(fn ($q) => Busqueda::filtrar($q, ['description'], $filtros['search'] ?? null))
            ->when($filtros['desde'] ?? null, fn ($q, $desde) => $q->where('created_at', '>=', $desde.' 00:00:00'))
            ->when($filtros['hasta'] ?? null, fn ($q, $hasta) => $q->where('created_at', '<=', $hasta.' 23:59:59'))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Activity $a) => [
                'id' => $a->id,
                'fecha' => $a->created_at?->format('d/m/Y H:i'),
                'modulo' => Auditoria::MODULOS[$a->log_name] ?? $a->log_name,
                'evento' => $a->event,
                'descripcion' => $a->description,
                'usuario' => $a->causer ? trim("{$a->causer->nombre} {$a->causer->paterno}") : null,
                'email' => $a->causer?->email,
                'detalles' => $a->properties,
            ]);

        return Inertia::render('Auditoria/Index', [
            'actividades' => $actividades,
            'filters' => $filtros,
            'modulos' => Auditoria::MODULOS,
        ]);
    }
}
