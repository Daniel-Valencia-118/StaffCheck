<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\Persona;
use App\Rules\TurnstileRule;
use App\Support\FichaPersonal;
use App\Support\FotosPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class RegistroPersonalController extends Controller
{
    public function create()
    {
        return Inertia::render('RegistroPersonal/index', [
            'registroAbierto' => Configuracion::valor('registro_abierto'),
            'mensajeCerrado' => Configuracion::valor('registro_mensaje_cerrado'),
            'fotoMaxKb' => Configuracion::valor('foto_max_kb'),
        ]);
    }

    public function store(Request $request)
    {
        if (! Configuracion::valor('registro_abierto')) {
            return back()->withErrors(['general' => Configuracion::valor('registro_mensaje_cerrado') ?: 'El registro está cerrado.']);
        }

        $fotoMaxKb = Configuracion::valor('foto_max_kb');

        $validator = Validator::make(
            $request->all(),
            [...FichaPersonal::reglas($fotoMaxKb), 'cf_turnstile_response' => ['required', new TurnstileRule]],
            FichaPersonal::mensajes($fotoMaxKb, publico: true),
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rutaFoto = null;

        DB::beginTransaction();
        try {
            $personaData = $request->only(FichaPersonal::CAMPOS);
            $personaData['estado'] = Configuracion::valor('fichas_estado_inicial');
            $personaData['numero_hijos'] = $personaData['numero_hijos'] ?? 0;

            $rutaFoto = FotosPersonal::guardar($request->file('foto'));
            $personaData['url_foto'] = $rutaFoto;

            $persona = Persona::create($personaData);

            FichaPersonal::guardarRelaciones($persona, $request->only(array_keys(FichaPersonal::RELACIONES)));

            DB::commit();

            return redirect()->route('registro.exitoso')->with('success', '¡Registro completado con éxito! Tus datos han sido enviados para revisión.');
        } catch (\Throwable $e) {
            DB::rollBack();

            FotosPersonal::borrar($rutaFoto);

            report($e);

            return back()->withErrors(['general' => 'No se pudo guardar el registro. Intente nuevamente o contacte a Recursos Humanos.'])->withInput();
        }
    }
}
