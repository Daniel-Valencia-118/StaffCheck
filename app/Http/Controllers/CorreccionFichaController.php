<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\EnlaceCorreccion;
use App\Models\Persona;
use App\Rules\TurnstileRule;
use App\Support\Auditoria;
use App\Support\FichaPersonal;
use App\Support\FotosPersonal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CorreccionFichaController extends Controller
{
    public function edit(string $token)
    {
        $enlace = EnlaceCorreccion::vigente($token);

        if (! $enlace) {
            return Inertia::render('Correccion/Index', ['valido' => false])
                ->toResponse(request())
                ->setStatusCode(404)
                ->header('Cache-Control', 'no-store');
        }

        $persona = $enlace->persona->load(['estudios', 'experienciasLaborales', 'referenciasLaborales', 'contactos']);

        return Inertia::render('Correccion/Index', [
            'valido' => true,
            'token' => $token,
            'nombreCompleto' => $persona->nombre_completo,
            'fotoActual' => $persona->getRawOriginal('url_foto') ? route('correccion.foto', $token) : null,
            'ficha' => FichaPersonal::paraFormulario($persona),
            'motivo' => $persona->revisiones()->whereNotNull('motivo')->latest('id')->value('motivo'),
            'vence' => $enlace->expira_at->format('d/m/Y H:i'),
        ])->toResponse(request())->header('Cache-Control', 'no-store');
    }

    public function foto(string $token)
    {
        $enlace = EnlaceCorreccion::vigente($token);

        return ($enlace ? FotosPersonal::respuesta($enlace->persona->getRawOriginal('url_foto'), 'private, no-store') : null)
            ?? abort(404);
    }

    public function update(Request $request, string $token)
    {
        $enlace = EnlaceCorreccion::vigente($token);

        if (! $enlace) {
            return redirect()->route('correccion.edit', $token);
        }

        $persona = $enlace->persona;
        $fotoMaxKb = Configuracion::valor('foto_max_kb');
        $request->merge(['ci' => $persona->ci]);

        $request->validate(
            [...FichaPersonal::reglas($fotoMaxKb, $persona), 'cf_turnstile_response' => ['required', new TurnstileRule]],
            FichaPersonal::mensajes($fotoMaxKb, publico: true),
        );

        try {
            $cambios = FichaPersonal::actualizar($persona, $request->all(), $request->file('foto'), function (Persona $persona) use ($enlace) {
                $enlace->forceFill(['usado_at' => now()])->save();
                $persona->cambiarEstado('pendiente', 'Ficha corregida por la persona mediante enlace.', null);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['general' => 'No se pudo guardar la ficha. Intente nuevamente.']);
        }

        Auditoria::registrar('personal', 'ficha_corregida', "La persona corrigió su ficha ({$persona->nombre_completo}) con un enlace", $persona, [
            'campos' => $cambios,
            'via' => 'enlace',
        ]);

        return redirect()->route('registro.exitoso');
    }
}
