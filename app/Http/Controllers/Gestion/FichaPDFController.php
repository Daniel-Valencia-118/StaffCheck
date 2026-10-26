<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Persona;
use App\Support\Auditoria;
use App\Support\FotosPersonal;
use App\Support\MapaEstatico;
use Barryvdh\DomPDF\Facade\Pdf;

class FichaPDFController extends Controller
{
    public function generate($id)
    {
        $persona = Persona::with(['estudios', 'experienciasLaborales', 'referenciasLaborales', 'contactos'])->findOrFail($id);

        $pdf = Pdf::setOption(['isRemoteEnabled' => false, 'isPhpEnabled' => false])
            ->loadView('pdf.ficha_personal', [
                'persona' => $persona,
                'foto' => FotosPersonal::dataUriRecortada($persona->getRawOriginal('url_foto'), 315, 345),
                'mapa' => MapaEstatico::dataUri($persona->latitude, $persona->longitude),
                'institucion' => Configuracion::valor('institucion_nombre'),
            ])
            ->setPaper('letter', 'portrait');

        Auditoria::registrar('personal', 'pdf_generado', "Generó el PDF de la ficha de {$persona->nombre_completo}", $persona);

        return $pdf->stream("ficha_{$persona->nombre_completo}_{$persona->ci}.pdf");
    }
}
