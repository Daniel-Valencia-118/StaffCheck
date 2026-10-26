import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Table from '../../Components/Tables/Table';
import Pagination from '../../Components/Tables/Pagination';
import SearchBar from '../../Components/Shared/SearchBar';
import PageHeader from '../../Components/Shared/PageHeader';
import Badge from '../../Components/Shared/Badge';
import { clasesBoton } from '../../Components/Forms/Button';
import usePuede from '../../hooks/usePuede';

const COLUMNAS = [
    { label: 'Nombre', campo: 'nombre' },
    { label: 'Paterno', campo: 'paterno' },
    { label: 'Materno', campo: 'materno' },
    { label: 'Correo', campo: 'email' },
    { label: 'Rol', campo: 'rol' },
    { label: 'Estado', campo: 'estado' },
    { label: 'Registro', campo: 'created_at' },
    '',
];

const celda = 'px-4 py-3 whitespace-nowrap text-sm text-gray-800';

const chip = (activo, tonoActivo) => `px-3 py-1 rounded-full text-sm border transition-colors ${
    activo ? tonoActivo : 'bg-white text-gray-700 border-gray-300 hover:bg-marca-50 hover:border-marca-200'
}`;

function EstadoCuenta({ usuario }) {
    if (usuario.pendiente) return <Badge tono="ambar">Pendiente</Badge>;
    return usuario.estado ? <Badge tono="verde">Activo</Badge> : <Badge tono="rojo">Suspendido</Badge>;
}

export default function UsuariosIndex({ usuarios, filters, totalPendientes = 0 }) {
    const puede = usePuede();

    const alternarEstado = (usuario) => {
        const activar = !usuario.estado;
        if (confirm(`¿${activar ? 'Activar' : 'Suspender'} la cuenta de ${usuario.email}?`)) {
            router.put(route('usuarios.estado', { id: usuario.id }), { estado: activar }, { preserveState: true, preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Usuarios" />
            <PageHeader title="Usuarios" subtitle="Cuentas con acceso al panel.">
                {puede('usuarios.gestionar') && (
                    <Link href={route('usuarios.create')} className={clasesBoton('primary')}>Nuevo usuario</Link>
                )}
            </PageHeader>

            <div className="flex flex-wrap gap-2 mb-4">
                <Link href={route('usuarios.index')} className={chip(!filters.pendientes, 'bg-marca-700 text-white border-marca-700')}>
                    Todos
                </Link>
                <Link href={route('usuarios.index', { pendientes: 1 })} className={chip(filters.pendientes, 'bg-amber-500 text-white border-amber-500')}>
                    Solicitudes pendientes ({totalPendientes})
                </Link>
            </div>

            <SearchBar initialSearch={filters.search || ''} ruta="usuarios.index" params={filters} placeholder="Buscar por nombre o correo..." />

            <Table
                headers={COLUMNAS}
                ruta="usuarios.index"
                params={filters}
                vacio={filters.pendientes ? 'No hay solicitudes pendientes.' : 'No hay usuarios que coincidan con la búsqueda.'}
            >
                {usuarios.data.map((usuario) => (
                    <tr key={usuario.id} className="hover:bg-marca-50/40 transition-colors">
                        <td className={`${celda} capitalize font-medium text-gray-900`}>{usuario.nombre}</td>
                        <td className={`${celda} capitalize`}>{usuario.paterno}</td>
                        <td className={`${celda} capitalize`}>{usuario.materno || '-'}</td>
                        <td className={`${celda} lowercase`}>{usuario.email}</td>
                        <td className={celda}><Badge tono={usuario.rol === 'admin' ? 'violeta' : 'marca'}>{usuario.rol}</Badge></td>
                        <td className={celda}><EstadoCuenta usuario={usuario} /></td>
                        <td className={`${celda} text-gray-500`}>{usuario.registrado}</td>
                        <td className={`${celda} text-right space-x-2`}>
                            {puede('usuarios.gestionar') && usuario.editable && (
                                <>
                                    <Link href={route('usuarios.edit', usuario.id)} className={clasesBoton(usuario.pendiente ? 'primary' : 'soft', 'sm')}>
                                        {usuario.pendiente ? 'Revisar' : 'Editar'}
                                    </Link>
                                    {!usuario.pendiente && (
                                        <button
                                            type="button"
                                            onClick={() => alternarEstado(usuario)}
                                            className={clasesBoton(usuario.estado ? 'dangerOutline' : 'success', 'sm')}
                                        >
                                            {usuario.estado ? 'Suspender' : 'Activar'}
                                        </button>
                                    )}
                                </>
                            )}
                        </td>
                    </tr>
                ))}
            </Table>

            <Pagination paginador={usuarios} ruta="usuarios.index" params={filters} />
        </AuthenticatedLayout>
    );
}
