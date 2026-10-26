import React from 'react';
import { Head, Link } from '@inertiajs/react';
import usePuede from '../../hooks/usePuede';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Table from '../../Components/Tables/Table';
import Pagination from '../../Components/Tables/Pagination';
import SearchBar from '../../Components/Shared/SearchBar';
import KpiCards from '../../Components/Shared/KpiCards';
import PageHeader from '../../Components/Shared/PageHeader';
import { clasesBoton } from '../../Components/Forms/Button';
import PersonalTableRow from './components/PersonalTableRow';
import { UsersIcon, MapPinIcon, BuildingIcon, UserIcon, UserRoundIcon, ExcelIcon, TrashIcon } from '../../Components/Shared/Icons';

const ESTADOS = ['pendiente', 'aprobado', 'observado', 'rechazado'];

const COLUMNAS = [
    { label: 'Paterno', campo: 'paterno' },
    { label: 'Materno', campo: 'materno' },
    { label: 'Nombres', campo: 'nombres' },
    { label: 'CI', campo: 'ci' },
    { label: 'Correo', campo: 'email' },
    { label: 'Celular', campo: 'celular' },
    { label: 'Oficina', campo: 'oficina_actual' },
    { label: 'Cargo', campo: 'cargo_actual' },
    { label: 'Estado', campo: 'estado' },
    '',
];

export default function PersonalIndex({ personal, filters, stats, porEstado = {} }) {
    const puede = usePuede();
    const papelera = Boolean(filters.papelera);

    const cards = [
        { label: 'Total registrados', value: stats.totalRegistrados, icon: UsersIcon, color: 'text-marca-700', bg: 'bg-marca-100' },
        { label: 'Oficina La Paz', value: stats.totalLaPaz, icon: MapPinIcon, color: 'text-emerald-600', bg: 'bg-emerald-100' },
        { label: 'Oficina Santa Cruz', value: stats.totalSantaCruz, icon: BuildingIcon, color: 'text-violet-600', bg: 'bg-violet-100' },
        { label: 'Hombres', value: stats.totalHombres, icon: UserIcon, color: 'text-sky-600', bg: 'bg-sky-100' },
        { label: 'Mujeres', value: stats.totalMujeres, icon: UserRoundIcon, color: 'text-pink-600', bg: 'bg-pink-100' },
    ];

    const vistaActual = { vista: 1, search: filters.search, estado: filters.estado, orden: filters.orden, dir: filters.dir };
    const exportar = !papelera && puede('personal.exportar');

    return (
        <AuthenticatedLayout>
            <Head title="Personal" />
            <PageHeader title={papelera ? 'Papelera de fichas' : 'Personal'} subtitle={papelera ? 'Fichas enviadas a la papelera. Puede restaurarlas.' : 'Fichas registradas del personal.'}>
                {puede('personal.eliminar') && (
                    <Link
                        href={route('personal.index', papelera ? {} : { papelera: 1 })}
                        className={clasesBoton(papelera ? 'secondary' : 'dangerOutline')}
                    >
                        <TrashIcon />
                        {papelera ? 'Salir de la papelera' : 'Papelera'}
                    </Link>
                )}
            </PageHeader>

            {!papelera && (
                <div className="mb-6">
                    <KpiCards cards={cards} />
                </div>
            )}

            {!papelera && (
                <div className="flex flex-wrap items-center gap-2 mb-4" role="group" aria-label="Filtrar por estado">
                    <FiltroEstado filters={filters} etiqueta="Todos" total={stats.totalRegistrados} />
                    {ESTADOS.map((estado) => (
                        <FiltroEstado key={estado} filters={filters} estado={estado} etiqueta={estado} total={porEstado[estado] ?? 0} />
                    ))}
                </div>
            )}

            <SearchBar initialSearch={filters.search || ''} ruta="personal.index" params={filters}>
                {exportar && (
                    <>
                        <a href={route('personal.export')} className={clasesBoton('excel')} title="Descarga un archivo Excel (.xlsx) con todas las fichas, sin filtros">
                            <ExcelIcon />
                            Excel: todo el personal
                        </a>
                        <a href={route('personal.export', vistaActual)} className={clasesBoton('excelOutline')} title="Descarga un archivo Excel (.xlsx) con lo que muestra la lista: búsqueda, estado y orden actuales">
                            <ExcelIcon />
                            Excel: vista actual
                        </a>
                    </>
                )}
            </SearchBar>

            <Table
                headers={COLUMNAS}
                ruta="personal.index"
                params={filters}
                vacio={filters.search || filters.estado ? 'No hay fichas que coincidan con la búsqueda o el filtro.' : 'Todavía no hay fichas registradas.'}
            >
                {personal.data.map((persona) => (
                    <PersonalTableRow key={persona.id} persona={persona} papelera={papelera} />
                ))}
            </Table>

            <Pagination paginador={personal} ruta="personal.index" params={filters} />
        </AuthenticatedLayout>
    );
}

function FiltroEstado({ filters, estado, etiqueta, total }) {
    const activo = (filters.estado ?? null) === (estado ?? null);

    return (
        <Link
            href={route('personal.index', { ...filters, estado, page: undefined })}
            preserveState
            preserveScroll
            aria-current={activo ? 'true' : undefined}
            className={`px-3 py-1 rounded-full text-sm border capitalize transition-colors ${
                activo ? 'bg-marca-700 text-white border-marca-700' : 'bg-white text-gray-700 border-gray-300 hover:bg-marca-50 hover:border-marca-200'
            }`}
        >
            {etiqueta} <span className={activo ? 'text-marca-100' : 'text-gray-400'}>({total})</span>
        </Link>
    );
}
