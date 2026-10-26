import React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeftIcon, ChevronRightIcon } from '../Shared/Icons';

const paginasVisibles = (actual, ultima) => {
    const paginas = new Set([1, ultima, actual - 1, actual, actual + 1]);
    const lista = [...paginas].filter((p) => p >= 1 && p <= ultima).sort((a, b) => a - b);

    return lista.flatMap((pagina, i) => (i > 0 && pagina - lista[i - 1] > 1 ? ['…', pagina] : [pagina]));
};

export default function Pagination({ paginador, ruta, params = {} }) {
    const { current_page: actual, last_page: ultima, from, to, total } = paginador;

    if (!total) return null;

    const enlace = (pagina) => route(ruta, { ...params, page: pagina > 1 ? pagina : undefined });
    const base = 'min-w-9 h-9 px-2 inline-flex items-center justify-center rounded-lg text-sm border transition-colors';

    return (
        <nav className="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4" aria-label="Paginación">
            <p className="text-sm text-gray-600">
                Mostrando <span className="font-medium">{from}</span>–<span className="font-medium">{to}</span> de <span className="font-medium">{total}</span>
            </p>
            {ultima > 1 && (
                <div className="flex items-center gap-1">
                    {actual > 1 ? (
                        <Link href={enlace(actual - 1)} preserveState className={`${base} bg-white border-gray-300 text-gray-700 hover:bg-gray-50`} aria-label="Página anterior">
                            <ChevronLeftIcon />
                        </Link>
                    ) : (
                        <span className={`${base} bg-white border-gray-200 text-gray-300`} aria-hidden="true"><ChevronLeftIcon /></span>
                    )}
                    {paginasVisibles(actual, ultima).map((pagina, i) => (pagina === '…' ? (
                        <span key={`salto-${i}`} className="px-1 text-gray-400">…</span>
                    ) : (
                        <Link
                            key={pagina}
                            href={enlace(pagina)}
                            preserveState
                            aria-current={pagina === actual ? 'page' : undefined}
                            className={`${base} ${pagina === actual ? 'bg-marca-700 border-marca-700 text-white' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'}`}
                        >
                            {pagina}
                        </Link>
                    )))}
                    {actual < ultima ? (
                        <Link href={enlace(actual + 1)} preserveState className={`${base} bg-white border-gray-300 text-gray-700 hover:bg-gray-50`} aria-label="Página siguiente">
                            <ChevronRightIcon />
                        </Link>
                    ) : (
                        <span className={`${base} bg-white border-gray-200 text-gray-300`} aria-hidden="true"><ChevronRightIcon /></span>
                    )}
                </div>
            )}
        </nav>
    );
}
