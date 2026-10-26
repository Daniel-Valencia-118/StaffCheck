import React from 'react';
import Card from '../../../Components/Forms/Card';
import { formatFecha } from '../../../utils/dateUtils';

function Dato({ etiqueta, children, className = '' }) {
    return (
        <div className={className}>
            <dt className="text-xs font-semibold text-gray-500 normal-case">{etiqueta}</dt>
            <dd className="mt-0.5 text-gray-900 wrap-break-word">{children || '-'}</dd>
        </div>
    );
}

export default function DatosPersonales({ persona, edad }) {
    const nombre = `${persona.nombres} ${persona.paterno} ${persona.materno ?? ''}`.trim();

    return (
        <Card title="Datos personales" className="">
            <div className="flex flex-col md:flex-row gap-6 items-start">
                {persona.url_foto ? (
                    <img
                        src={persona.url_foto}
                        alt={`Foto de ${nombre}`}
                        className="w-36 h-36 rounded-xl object-cover border border-gray-200 shadow-sm shrink-0"
                    />
                ) : (
                    <div className="w-36 h-36 rounded-xl bg-marca-50 flex items-center justify-center text-marca-700 font-bold text-3xl border border-marca-100 shrink-0 uppercase">
                        {persona.nombres?.[0]}{persona.paterno?.[0]}
                    </div>
                )}

                <dl className="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-sm uppercase">
                    <Dato etiqueta="Nombre completo">{nombre}</Dato>
                    <Dato etiqueta="Documento de identidad">{persona.ci} {persona.ci_expedicion}</Dato>
                    <Dato etiqueta="Sexo">{persona.sexo}</Dato>
                    <Dato etiqueta="Fecha de nacimiento">{formatFecha(persona.fecha_nacimiento)}</Dato>
                    <Dato etiqueta="Edad">{edad != null ? `${edad} años` : null}</Dato>
                    <Dato etiqueta="Lugar de nacimiento">
                        {[persona.lugar_nacimiento_ciudad, persona.lugar_nacimiento_provincia].filter(Boolean).join(', ')}
                    </Dato>
                    <Dato etiqueta="Estado civil">{persona.estado_civil}</Dato>
                    <Dato etiqueta="Número de hijos">{String(persona.numero_hijos ?? 0)}</Dato>
                    <Dato etiqueta="Correo electrónico"><span className="lowercase">{persona.email}</span></Dato>
                    <Dato etiqueta="Celular">{persona.celular}</Dato>
                    <Dato etiqueta="Teléfono">{persona.telefono}</Dato>
                    <Dato etiqueta="Dirección actual">{persona.direccion_actual}</Dato>
                    <Dato etiqueta="Fecha de ingreso a la fundación">{formatFecha(persona.fecha_ingreso_fundacion)}</Dato>
                    <Dato etiqueta="Cargo actual">{persona.cargo_actual}</Dato>
                    <Dato etiqueta="Oficina actual">{persona.oficina_actual}</Dato>
                </dl>
            </div>
        </Card>
    );
}
