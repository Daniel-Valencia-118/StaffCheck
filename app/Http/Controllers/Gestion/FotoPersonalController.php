<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Support\FotosPersonal;
use Illuminate\Http\Request;

class FotoPersonalController extends Controller
{
    public function mostrar(Request $request, int $id)
    {
        $usuario = $request->user();
        $propia = $usuario->id_persona !== null && (int) $usuario->id_persona === $id;

        abort_unless($propia || $usuario->can('personal.ver_detalle') || $usuario->can('personal.editar'), 403);

        $persona = Persona::query()->select(['id', 'url_foto'])->findOrFail($id);

        return FotosPersonal::respuesta($persona->getRawOriginal('url_foto')) ?? abort(404);
    }
}
