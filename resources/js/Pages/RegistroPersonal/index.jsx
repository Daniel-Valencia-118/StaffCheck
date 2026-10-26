import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../../Components/Layouts/PublicLayout';
import FichaWizard from '../Personal/components/FichaWizard';

const VALORES_INICIALES = {
    paterno: '',
    materno: '',
    nombres: '',
    ci: '',
    ci_expedicion: 'LP',
    sexo: 'M',
    fecha_nacimiento: '',
    lugar_nacimiento_provincia: '',
    lugar_nacimiento_ciudad: 'LA PAZ',
    estado_civil: 'SOLTERO/A',
    numero_hijos: 0,
    email: '',
    telefono: '',
    celular: '',
    direccion_actual: '',
    fecha_ingreso_fundacion: '',
    cargo_actual: '',
    oficina_actual: 'Oficina Central La Paz',
    foto: null,
    url_croquis: '',
    latitude: null,
    longitude: null,
    estudios: [{ tipo: '', titulo_obtenido: '', institucion: '', anio: String(new Date().getFullYear()) }],
    experiencias: [{ institucion: '', cargo: '', fecha_inicio: '', fecha_fin: '' }],
    referencias: [{ nombre_referente: '', institucion: '', telefono_celular: '' }],
    contactos: [
        { nombre: '', paterno: '', materno: '', parentesco_relacion: '', edad: '', telefono_celular: '', es_familiar: true },
        { nombre: '', paterno: '', materno: '', parentesco_relacion: '', edad: '', telefono_celular: '', es_familiar: false },
    ],
};

export default function RegistroPersonal({ registroAbierto = true, mensajeCerrado }) {
    if (!registroAbierto) {
        return (
            <PublicLayout>
                <Head title="Registro de Personal" />
                <div className="w-full max-w-xl mx-auto py-8 text-center">
                    <div className="bg-white rounded-2xl shadow-sm p-8 border border-gray-200">
                        <h1 className="text-2xl font-bold text-marca-900 mb-3">Registro de Personal</h1>
                        <p className="text-gray-600">{mensajeCerrado || 'El registro está cerrado.'}</p>
                    </div>
                </div>
            </PublicLayout>
        );
    }

    return (
        <PublicLayout>
            <Head title="Registro de Personal" />

            <div className="w-full max-w-5xl mx-auto">
                <h1 className="text-2xl font-bold text-marca-900">Registro de Personal</h1>
                <p className="text-sm text-gray-600 mt-1 mb-6">
                    Complete los cinco pasos. Los campos con <span className="text-red-500">*</span> son obligatorios.
                </p>

                <FichaWizard
                    ficha={VALORES_INICIALES}
                    url={route('registro.personal.store')}
                    metodo="post"
                    conTurnstile
                    fotoObligatoria
                    textoFinal="Enviar registro"
                />
            </div>
        </PublicLayout>
    );
}
