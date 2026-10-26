import React, { useMemo, useRef, useState } from 'react';
import Wizard from '../../../Components/Wizard/Wizard';
import Aviso from '../../../Components/Shared/Aviso';
import { useFormWizard } from '../../RegistroPersonal/hooks/useFormWizard';
import { generateId } from '../../../utils/formulario';
import Paso1DatosGenerales from '../../RegistroPersonal/components/Paso1DatosGenerales';
import Paso2Estudios from '../../RegistroPersonal/components/Paso2Estudios';
import Paso3ExperienciaReferencias from '../../RegistroPersonal/components/Paso3ExperienciaReferencias';
import Paso4Contactos from '../../RegistroPersonal/components/Paso4Contactos';
import Paso5InformacionInstitucional from '../../RegistroPersonal/components/Paso5InformacionInstitucional';

const PASOS = [
    {
        titulo: 'Datos Generales',
        campos: [
            'paterno', 'materno', 'nombres', 'ci', 'ci_expedicion', 'sexo', 'fecha_nacimiento', 'lugar_nacimiento_provincia',
            'lugar_nacimiento_ciudad', 'estado_civil', 'numero_hijos', 'email', 'telefono', 'celular', 'direccion_actual',
            'foto', 'url_croquis', 'latitude', 'longitude',
        ],
    },
    { titulo: 'Estudios', campos: ['estudios'] },
    { titulo: 'Experiencia y Referencias', campos: ['experiencias', 'referencias'] },
    { titulo: 'Contactos', campos: ['contactos'] },
    { titulo: 'Información Institucional', campos: ['fecha_ingreso_fundacion', 'cargo_actual', 'oficina_actual', 'cf_turnstile_response'] },
];

const TITULOS = PASOS.map((paso) => paso.titulo);

const SECCIONES = ['estudios', 'experiencias', 'referencias', 'contactos'];

const pasoDelCampo = (clave) => PASOS.findIndex((paso) => paso.campos.includes(clave.split('.')[0]));

const valoresIniciales = (ficha, metodo, conTurnstile) => ({
    ...ficha,
    ...(metodo === 'put' ? { _method: 'put' } : {}),
    ...(conTurnstile ? { cf_turnstile_response: '' } : {}),
    ...Object.fromEntries(SECCIONES.map((seccion) => [
        seccion,
        (ficha[seccion] ?? []).map((fila) => ({ ...fila, temp_id: fila.temp_id ?? generateId() })),
    ])),
});

export default function FichaWizard({
    ficha,
    fotoActual,
    url,
    metodo = 'put',
    conTurnstile = false,
    ciBloqueado = false,
    fotoObligatoria = false,
    textoFinal = 'Guardar cambios',
}) {
    const { form, errors, processing, post, handleChange, handleNestedChange, handleLocationChange, addItem, removeItem } = useFormWizard(
        valoresIniciales(ficha, metodo, conTurnstile),
    );
    const [paso, setPaso] = useState(0);
    const [reinicioVerificacion, setReinicioVerificacion] = useState(0);
    const contenedor = useRef(null);

    const pasosConError = useMemo(
        () => [...new Set(Object.keys(errors).map(pasoDelCampo).filter((indice) => indice >= 0))].sort(),
        [errors],
    );
    const cantidadErrores = Object.keys(errors).filter((clave) => clave !== 'general').length;

    const guardar = () => {
        post(url, {
            forceFormData: true,
            preserveScroll: true,
            onError: (errores) => {
                if (conTurnstile) {
                    handleChange('cf_turnstile_response', '');
                    setReinicioVerificacion((n) => n + 1);
                }

                const primero = Math.min(...Object.keys(errores).map(pasoDelCampo).filter((indice) => indice >= 0));
                if (Number.isFinite(primero)) setPaso(primero);
                contenedor.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            },
        });
    };

    return (
        <div ref={contenedor} className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 scroll-mt-4">
            {errors.general && <Aviso tono="error">{errors.general}</Aviso>}
            {cantidadErrores > 0 && (
                <Aviso tono="error">
                    Hay {cantidadErrores} campo(s) por corregir. Los pasos marcados en rojo tienen errores.
                </Aviso>
            )}

            <Wizard
                stepTitles={TITULOS}
                paso={paso}
                onCambiarPaso={setPaso}
                pasosConError={pasosConError}
                onComplete={guardar}
                isSubmitting={processing}
                puedeFinalizar={!conTurnstile || Boolean(form.cf_turnstile_response)}
                textoFinal={textoFinal}
            >
                {(actual) => {
                    switch (actual) {
                        case 0:
                            return (
                                <Paso1DatosGenerales
                                    form={form}
                                    handleChange={handleChange}
                                    handleLocationChange={handleLocationChange}
                                    errors={errors}
                                    fotoActual={fotoActual}
                                    ciBloqueado={ciBloqueado}
                                    fotoObligatoria={fotoObligatoria}
                                />
                            );
                        case 1:
                            return (
                                <Paso2Estudios
                                    estudios={form.estudios}
                                    handleNestedChange={handleNestedChange}
                                    addItem={addItem}
                                    removeItem={removeItem}
                                    errors={errors}
                                />
                            );
                        case 2:
                            return (
                                <Paso3ExperienciaReferencias
                                    experiencias={form.experiencias}
                                    referencias={form.referencias}
                                    handleNestedChange={handleNestedChange}
                                    addItem={addItem}
                                    removeItem={removeItem}
                                    errors={errors}
                                />
                            );
                        case 3:
                            return (
                                <Paso4Contactos
                                    contactos={form.contactos}
                                    handleNestedChange={handleNestedChange}
                                    addItem={addItem}
                                    removeItem={removeItem}
                                    errors={errors}
                                />
                            );
                        case 4:
                            return (
                                <Paso5InformacionInstitucional
                                    form={form}
                                    handleChange={handleChange}
                                    errors={errors}
                                    conTurnstile={conTurnstile}
                                    reinicioVerificacion={reinicioVerificacion}
                                />
                            );
                        default:
                            return null;
                    }
                }}
            </Wizard>
        </div>
    );
}
