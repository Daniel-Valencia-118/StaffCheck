import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Table from '../../Components/Tables/Table';
import Pagination from '../../Components/Tables/Pagination';
import Select from '../../Components/Forms/Select';
import Input from '../../Components/Forms/Input';
import Button from '../../Components/Forms/Button';
import PageHeader from '../../Components/Shared/PageHeader';
import Badge from '../../Components/Shared/Badge';

const resumenDetalles = (detalles) => {
    if (!detalles) return '';
    return Object.entries(detalles)
        .filter(([clave]) => clave !== 'ip')
        .map(([clave, valor]) => `${clave}: ${typeof valor === 'object' ? JSON.stringify(valor) : valor}`)
        .join(' · ');
};

export default function AuditoriaIndex({ actividades, filters, modulos }) {
    const [filtros, setFiltros] = useState({
        modulo: filters.modulo ?? '',
        search: filters.search ?? '',
        desde: filters.desde ?? '',
        hasta: filters.hasta ?? '',
    });

    const opcionesModulo = Object.entries(modulos).map(([value, label]) => ({ value, label }));

    const cambiar = (e) => setFiltros({ ...filtros, [e.target.name]: e.target.value });

    const filtrar = (e) => {
        e.preventDefault();
        router.get(route('auditoria.index'), Object.fromEntries(Object.entries(filtros).filter(([, valor]) => valor)), { preserveState: true });
    };

    const limpiar = () => {
        setFiltros({ modulo: '', search: '', desde: '', hasta: '' });
        router.get(route('auditoria.index'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Auditoría" />
            <PageHeader title="Auditoría" subtitle="Registro de acciones sensibles realizadas en el sistema." />

            <form onSubmit={filtrar} className="bg-white rounded-xl shadow-sm border border-gray-200 p-4 pb-0 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-x-3 items-end mb-4">
                <Select label="Módulo" name="modulo" value={filtros.modulo} onChange={cambiar} options={opcionesModulo} placeholder="Todos los módulos" />
                <Input label="Descripción" name="search" value={filtros.search} onChange={cambiar} placeholder="Buscar…" />
                <Input label="Desde" name="desde" type="date" value={filtros.desde} onChange={cambiar} />
                <Input label="Hasta" name="hasta" type="date" value={filtros.hasta} onChange={cambiar} />
                <div className="flex gap-2 mb-4">
                    <Button type="submit">Filtrar</Button>
                    <Button type="button" variant="secondary" onClick={limpiar}>Limpiar</Button>
                </div>
            </form>

            <Table headers={['Fecha', 'Usuario', 'Módulo', 'Acción', 'Detalle', 'IP']} vacio="No hay registros con esos filtros.">
                {actividades.data.map((a) => (
                    <tr key={a.id} className="hover:bg-marca-50/40 align-top">
                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{a.fecha}</td>
                        <td className="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {a.usuario ? <span className="capitalize">{a.usuario}</span> : <span className="text-gray-400">—</span>}
                            {a.email && <span className="block text-xs text-gray-400">{a.email}</span>}
                        </td>
                        <td className="px-4 py-3 whitespace-nowrap text-sm"><Badge tono="marca">{a.modulo}</Badge></td>
                        <td className="px-4 py-3 text-sm text-gray-900">{a.descripcion}</td>
                        <td className="px-4 py-3 text-xs text-gray-500 max-w-xs wrap-break-word">{resumenDetalles(a.detalles)}</td>
                        <td className="px-4 py-3 whitespace-nowrap text-xs text-gray-500">{a.detalles?.ip}</td>
                    </tr>
                ))}
            </Table>

            <Pagination paginador={actividades} ruta="auditoria.index" params={filters} />
        </AuthenticatedLayout>
    );
}
