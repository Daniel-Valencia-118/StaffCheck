import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Components/Layouts/PublicLayout';
import Aviso from '../../Components/Shared/Aviso';
import FichaWizard from '../Personal/components/FichaWizard';

export default function Correccion({ valido, token, nombreCompleto, fotoActual, ficha, motivo, vence }) {
    if (!valido) {
        return (
            <PublicLayout>
                <Head title="Enlace no válido" />
                <div className="w-full max-w-xl mx-auto py-8 text-center">
                    <div className="bg-white rounded-2xl shadow-sm p-8 border border-gray-200">
                        <h1 className="text-2xl font-bold text-marca-900 mb-3">Enlace no válido</h1>
                        <p className="text-gray-600">
                            El enlace venció, ya fue usado o fue reemplazado por otro. Solicite uno nuevo a Recursos Humanos.
                        </p>
                    </div>
                </div>
            </PublicLayout>
        );
    }

    return (
        <PublicLayout>
            <Head title="Corregir mi ficha" />

            <div className="w-full max-w-5xl mx-auto">
                <h1 className="text-2xl font-bold text-marca-900 uppercase">Corregir ficha: {nombreCompleto}</h1>
                <p className="text-sm text-gray-600 mt-1 mb-4">
                    Este enlace sirve una sola vez y vence el {vence}. Al guardar, su ficha vuelve a revisión de Recursos Humanos.
                </p>

                {motivo && (
                    <Aviso tono="observacion"><strong>Observación de Recursos Humanos:</strong> {motivo}</Aviso>
                )}

                <FichaWizard
                    ficha={ficha}
                    fotoActual={fotoActual}
                    url={route('correccion.update', token)}
                    metodo="post"
                    conTurnstile
                    ciBloqueado
                    textoFinal="Enviar correcciones"
                />
            </div>
        </PublicLayout>
    );
}
