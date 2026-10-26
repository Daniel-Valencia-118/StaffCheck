import React from 'react';
import { Link } from '@inertiajs/react';
import { SortIcon } from '../Shared/Icons';

function Encabezado({ columna, ruta, params }) {
    const { label, campo } = typeof columna === 'string' ? { label: columna } : columna;
    const clases = 'px-4 py-3 text-left text-xs font-semibold text-marca-800 uppercase tracking-wider whitespace-nowrap';

    if (!campo || !ruta) {
        return <th scope="col" className={clases}>{label}</th>;
    }

    const activo = params.orden === campo;
    const direccion = activo ? params.dir : null;
    const siguiente = activo && direccion === 'asc' ? 'desc' : 'asc';

    return (
        <th scope="col" className={clases} aria-sort={activo ? (direccion === 'asc' ? 'ascending' : 'descending') : 'none'}>
            <Link
                href={route(ruta, { ...params, orden: campo, dir: siguiente, page: undefined })}
                preserveState
                preserveScroll
                className={`inline-flex items-center gap-1 hover:text-marca-600 ${activo ? 'text-marca-700' : ''}`}
                title={`Ordenar por ${label.toLowerCase()}`}
            >
                {label}
                <SortIcon direccion={direccion} className="w-3.5 h-3.5" />
            </Link>
        </th>
    );
}

export default function Table({ headers, children, ruta, params = {}, vacio = 'No hay registros para mostrar.', className = '' }) {
    const sinFilas = React.Children.count(children) === 0;

    return (
        <div className={`overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200 ${className}`}>
            <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-marca-50/60">
                    <tr>
                        {headers.map((columna, index) => (
                            <Encabezado key={index} columna={columna} ruta={ruta} params={params} />
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {sinFilas ? (
                        <tr>
                            <td colSpan={headers.length} className="px-4 py-10 text-center text-sm text-gray-500">{vacio}</td>
                        </tr>
                    ) : children}
                </tbody>
            </table>
        </div>
    );
}
