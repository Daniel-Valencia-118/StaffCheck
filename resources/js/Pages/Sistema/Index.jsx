import React from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Card from '../../Components/Forms/Card';
import Button from '../../Components/Forms/Button';
import PageHeader from '../../Components/Shared/PageHeader';
import Aviso from '../../Components/Shared/Aviso';
import Badge from '../../Components/Shared/Badge';

const formatoBytes = (bytes) => {
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
};

const Fila = ({ etiqueta, valor, alerta = false }) => (
    <div className="flex justify-between gap-4 py-2 border-b border-gray-100 last:border-0 text-sm">
        <span className="text-gray-500">{etiqueta}</span>
        <span className={alerta ? 'font-semibold text-red-600 text-right' : 'font-medium text-gray-800 text-right'}>{valor}</span>
    </div>
);

const Tarea = ({ descripcion, children }) => (
    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
        <p className="text-gray-600">{descripcion}</p>
        <div className="shrink-0 w-full sm:w-48 [&>button]:w-full">{children}</div>
    </div>
);

export default function SistemaIndex({ despliegue = false, aplicacion, migracionesPendientes, almacenamiento, auditoria }) {
    const ejecutar = (ruta, pregunta) => {
        if (!pregunta || confirm(pregunta)) {
            router.post(route(ruta), {}, { preserveScroll: true });
        }
    };

    const debugEnProduccion = aplicacion.debug && aplicacion.entorno === 'production';

    return (
        <AuthenticatedLayout>
            <Head title="Sistema" />
            <PageHeader title="Sistema" subtitle="Estado de la instalación y tareas de mantenimiento.">
                {/* <Badge tono={despliegue ? 'ambar' : 'gris'}>{despliegue ? 'Modo despliegue activo' : 'Modo despliegue apagado'}</Badge> */}
            </PageHeader>

            {despliegue && migracionesPendientes.length > 0 && (
                <Aviso tono="alerta" className="mb-6">
                    <p className="font-semibold mb-2">
                        Hay {migracionesPendientes.length} migración(es) sin aplicar en esta base de datos.
                        Importe el SQL correspondiente en phpMyAdmin:
                    </p>
                    <ul className="list-disc pl-5 font-mono text-xs space-y-0.5">
                        {migracionesPendientes.map((m) => <li key={m}>{m}</li>)}
                    </ul>
                </Aviso>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-x-6">
                <Card title="Aplicación">
                    <Fila etiqueta="Versión desplegada" valor={aplicacion.version ?? 'sin version.txt'} />
                    <Fila etiqueta="Entorno" valor={aplicacion.entorno} />
                    <Fila etiqueta="Modo depuración" valor={aplicacion.debug ? 'Activado' : 'Desactivado'} alerta={debugEnProduccion} />
                    <Fila etiqueta="PHP / Laravel" valor={`${aplicacion.php} / ${aplicacion.laravel}`} />
                    <Fila etiqueta="Zona horaria" valor={aplicacion.zona_horaria} />
                    <Fila etiqueta="Caché / sesiones" valor={`${aplicacion.cache} / ${aplicacion.sesiones}`} />
                    {despliegue && (
                        <Fila etiqueta="Migraciones pendientes" valor={migracionesPendientes.length} alerta={migracionesPendientes.length > 0} />
                    )}
                </Card>

                <Card title="Almacenamiento y auditoría">
                    {despliegue && (
                        <>
                        <Fila etiqueta="Fotos protegidas" valor={`${almacenamiento.privadas.fotos} (${formatoBytes(almacenamiento.privadas.bytes)})`} />
                        <Fila
                            etiqueta="Fotos aún públicas"
                            valor={`${almacenamiento.publicas.fotos} (${formatoBytes(almacenamiento.publicas.bytes)})`}
                            alerta={almacenamiento.publicas.fotos > 0}
                        />
                        <Fila etiqueta="Mapas del PDF en caché" valor={almacenamiento.mapas} />
                        <Fila etiqueta="Carpeta pública de archivos" valor={almacenamiento.enlace_publico ? 'Presente' : 'No existe (las fotos no la necesitan)'} />
                        </>
                    )}
                    <Fila etiqueta="Mapa del PDF (extensión GD)" valor={almacenamiento.gd ? 'Disponible' : 'Falta la extensión GD: el PDF saldrá sin mapa'} alerta={!almacenamiento.gd} />
                    <Fila etiqueta="Registros de auditoría" valor={auditoria.registros} />
                    <Fila etiqueta="Registro más antiguo" valor={auditoria.mas_antiguo ?? '—'} />
                    <Fila etiqueta="Retención" valor={`${auditoria.dias_retencion} días`} />
                </Card>
            </div>

            {despliegue ? (
                <Card title="Despliegue y actualización">
                    {/* <Aviso tono="alerta">
                        Estas tareas son para la instalación inicial y para después de subir una versión nueva. Al terminar,
                        vuelva a poner <code>APP_DEPLOY=false</code> en el <code>.env</code> del servidor.
                    </Aviso> */}
                    <div className="divide-y divide-gray-100 text-sm">
                        <Tarea descripcion="Limpia la caché de configuración, rutas, vistas, permisos y mapas del PDF. Úselo después de cada despliegue o cambio hecho por SQL.">
                            <Button onClick={() => ejecutar('sistema.cache')}>Limpiar caché</Button>
                        </Tarea>
                        <Tarea descripcion="Crea los permisos nuevos del catálogo. No quita permisos a ningún rol.">
                            <Button variant="secondary" onClick={() => ejecutar('sistema.permisos')}>Sincronizar permisos</Button>
                        </Tarea>
                        <Tarea
                            descripcion="Mueve las fotos subidas antes de la versión de fotos privadas a la carpeta privada. Copia cada foto, comprueba la copia y recién entonces borra la pública. No modifica ninguna ficha y se puede repetir."
                        >
                            <Button
                                variant="secondary"
                                onClick={() => ejecutar('sistema.fotos', '¿Mover las fotos públicas a la carpeta privada?')}
                                disabled={almacenamiento.publicas.fotos === 0}
                            >
                                Proteger fotos
                            </Button>
                        </Tarea>
                        <Tarea descripcion={<>Crea el enlace <code>public/storage</code> para archivos públicos. Las fotos del personal ya no lo usan.</>}>
                            <Button variant="secondary" onClick={() => ejecutar('sistema.storage')}>Crear enlace de archivos</Button>
                        </Tarea>
                    </div>
                </Card>
            ) : (
                <></>
                // <Aviso tono="info" className="mb-6">
                //     Las tareas de instalación y actualización (caché, permisos, migraciones, fotos y enlace de archivos) están ocultas.
                //     Quien administra el servidor las habilita con <code>APP_DEPLOY=true</code> en el <code>.env</code>.
                // </Aviso>
            )}

            <Card title="Mantenimiento">
                <div className="text-sm">
                    <Tarea descripcion={`Borra los registros de auditoría con más de ${auditoria.dias_retencion} días.`}>
                        <Button
                            variant="dangerOutline"
                            onClick={() => ejecutar('sistema.auditoria', `¿Eliminar los registros de auditoría con más de ${auditoria.dias_retencion} días?`)}
                        >
                            Depurar auditoría
                        </Button>
                    </Tarea>
                </div>
            </Card>
        </AuthenticatedLayout>
    );
}
