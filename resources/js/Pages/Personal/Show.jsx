import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import MapViewer from '../../Components/Forms/MapViewer';
import Card from '../../Components/Forms/Card';
import { clasesBoton } from '../../Components/Forms/Button';
import PageHeader from '../../Components/Shared/PageHeader';
import EstadoBadge from '../../Components/Shared/EstadoBadge';
import { FileTextIcon, PencilIcon, TrashIcon } from '../../Components/Shared/Icons';
import usePuede from '../../hooks/usePuede';
import { formatFecha } from '../../utils/dateUtils';
import DatosPersonales from './components/DatosPersonales';
import TablaDetalle from './components/TablaDetalle';
import RevisionCard from './components/RevisionCard';
import EnlaceCorreccionCard from './components/EnlaceCorreccionCard';

const nombreDe = (contacto) => [contacto.nombre, contacto.paterno, contacto.materno].filter(Boolean).join(' ');

const COLUMNAS_ESTUDIOS = [
    { titulo: 'Título obtenido', valor: (e) => e.titulo_obtenido },
    { titulo: 'Tipo', valor: (e) => e.tipo },
    { titulo: 'Institución', valor: (e) => e.institucion },
    { titulo: 'Año', valor: (e) => e.anio },
];

const COLUMNAS_EXPERIENCIAS = [
    { titulo: 'Institución', valor: (e) => e.institucion },
    { titulo: 'Cargo', valor: (e) => e.cargo },
    { titulo: 'Fecha inicio', valor: (e) => formatFecha(e.fecha_inicio) },
    { titulo: 'Fecha fin', valor: (e) => (e.fecha_fin ? formatFecha(e.fecha_fin) : 'Actualidad') },
];

const COLUMNAS_REFERENCIAS = [
    { titulo: 'Nombre del referente', valor: (r) => r.nombre_referente },
    { titulo: 'Teléfono / celular', valor: (r) => r.telefono_celular },
    { titulo: 'Institución', valor: (r) => r.institucion },
];

const COLUMNAS_FAMILIARES = [
    { titulo: 'Nombre', valor: nombreDe },
    { titulo: 'Parentesco', valor: (c) => c.parentesco_relacion },
    { titulo: 'Edad', valor: (c) => c.edad },
    { titulo: 'Teléfono', valor: (c) => c.telefono_celular },
];

const COLUMNAS_REFERENCIAS_PERSONALES = [
    { titulo: 'Nombre', valor: nombreDe },
    { titulo: 'Relación', valor: (c) => c.parentesco_relacion },
    { titulo: 'Teléfono', valor: (c) => c.telefono_celular },
];

export default function PersonalShow({ persona, edad, enlaceVence, cuentaVinculada, revisiones = [], estados = [], estadosConMotivo = [] }) {
    const puede = usePuede();
    const contactos = persona.contactos ?? [];
    const nombre = `${persona.nombres} ${persona.paterno} ${persona.materno ?? ''}`.trim();

    const enviarAPapelera = () => {
        if (confirm(`¿Enviar a la papelera la ficha de ${persona.nombres} ${persona.paterno}? Podrá restaurarla después.`)) {
            router.delete(route('personal.destroy', persona.id));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={`Ficha: ${persona.nombres} ${persona.paterno}`} />

            <PageHeader
                title={<span className="uppercase">{nombre}</span>}
                subtitle={
                    <span className="flex flex-wrap items-center gap-2 uppercase">
                        <EstadoBadge estado={persona.estado} />
                        CI {persona.ci} {persona.ci_expedicion} · {persona.cargo_actual || 'Sin cargo asignado'}
                    </span>
                }
                volver={{ href: route('personal.index'), texto: 'Volver al personal' }}
            >
                {puede('personal.editar') && (
                    <Link href={route('personal.edit', persona.id)} className={clasesBoton('primary')}>
                        <PencilIcon />
                        Editar ficha
                    </Link>
                )}
                {puede('personal.pdf') && (
                    <a href={route('personal.ficha.pdf', persona.id)} target="_blank" rel="noopener noreferrer" className={clasesBoton('pdf')}>
                        <FileTextIcon />
                        Descargar PDF
                    </a>
                )}
                {puede('personal.eliminar') && (
                    <button type="button" onClick={enviarAPapelera} className={clasesBoton('dangerOutline')}>
                        <TrashIcon />
                        Enviar a papelera
                    </button>
                )}
            </PageHeader>

            <div className="space-y-6">
                <DatosPersonales persona={persona} edad={edad} />

                <div className="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
                    <RevisionCard persona={persona} revisiones={revisiones} estados={estados} estadosConMotivo={estadosConMotivo} />
                    {puede('personal.editar') && (
                        <EnlaceCorreccionCard persona={persona} enlaceVence={enlaceVence} cuentaVinculada={cuentaVinculada} />
                    )}
                </div>

                {persona.latitude && persona.longitude && (
                    <Card title="Ubicación del domicilio" className="">
                        <MapViewer lat={persona.latitude} lng={persona.longitude} height="400px" />
                    </Card>
                )}

                <TablaDetalle titulo="Estudios y formación académica" columnas={COLUMNAS_ESTUDIOS} filas={persona.estudios ?? []} vacio="No registra estudios." />
                <TablaDetalle titulo="Experiencia laboral" columnas={COLUMNAS_EXPERIENCIAS} filas={persona.experiencias_laborales ?? []} vacio="No registra experiencia laboral." />
                <TablaDetalle titulo="Referencias laborales" columnas={COLUMNAS_REFERENCIAS} filas={persona.referencias_laborales ?? []} vacio="No registra referencias laborales." />

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <TablaDetalle titulo="Familiares" columnas={COLUMNAS_FAMILIARES} filas={contactos.filter((c) => c.es_familiar)} vacio="No registra familiares." />
                    <TablaDetalle titulo="Referencias personales" columnas={COLUMNAS_REFERENCIAS_PERSONALES} filas={contactos.filter((c) => !c.es_familiar)} vacio="No registra referencias personales." />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
