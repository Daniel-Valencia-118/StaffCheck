<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Persona;
use App\Support\Auditoria;
use App\Support\FichaPersonal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MiFichaController extends Controller
{
    public function edit(Request $request)
    {
        $persona = $request->user()->persona()
            ->with(['estudios', 'experienciasLaborales', 'referenciasLaborales', 'contactos'])
            ->first();

        if (! $persona) {
            return Inertia::render('MiFicha/Index', ['vinculada' => false]);
        }

        return Inertia::render('MiFicha/Index', [
            'vinculada' => true,
            'estado' => $persona->estado,
            'nombreCompleto' => $persona->nombre_completo,
            'fotoActual' => $persona->url_foto,
            'ficha' => FichaPersonal::paraFormulario($persona),
            'motivo' => $persona->revisiones()->whereNotNull('motivo')->latest('id')->value('motivo'),
        ]);
    }

    public function update(Request $request)
    {
        $persona = $request->user()->persona;
        abort_unless($persona, 404);

        $fotoMaxKb = Configuracion::valor('foto_max_kb');
        $request->merge(['ci' => $persona->ci]);
        $request->validate(FichaPersonal::reglas($fotoMaxKb, $persona), FichaPersonal::mensajes($fotoMaxKb));

        try {
            $cambios = FichaPersonal::actualizar($persona, $request->all(), $request->file('foto'), function (Persona $persona) use ($request) {
                $persona->cambiarEstado('pendiente', 'Ficha corregida por la persona desde su cuenta.', $request->user());
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'No se pudo guardar la ficha. Intente nuevamente.');
        }

        Auditoria::registrar('personal', 'ficha_corregida', 'Corrigió su propia ficha', $persona, [
            'campos' => $cambios,
            'via' => 'cuenta',
        ]);

        return redirect()->route('mi_ficha.edit')->with('success', 'Ficha guardada. Recursos Humanos revisará los cambios.');
    }
}
