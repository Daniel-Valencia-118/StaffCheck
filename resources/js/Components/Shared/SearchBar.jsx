import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from '../Forms/Button';
import { SearchIcon, XIcon } from './Icons';

export default function SearchBar({ initialSearch = '', ruta, params = {}, placeholder = 'Buscar por nombre, CI o correo...', children }) {
    const [search, setSearch] = useState(initialSearch);

    const buscar = (texto) => {
        router.get(route(ruta), { ...params, search: texto || undefined, page: undefined }, { preserveState: true });
    };

    const limpiar = () => {
        setSearch('');
        buscar('');
    };

    return (
        <form onSubmit={(e) => { e.preventDefault(); buscar(search); }} className="flex flex-wrap items-center gap-2 mb-4">
            <div className="relative flex-1 min-w-56">
                <SearchIcon className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                    type="search"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    placeholder={placeholder}
                    aria-label="Buscar"
                    className="w-full pl-9 pr-9 py-2 text-sm border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-marca-500/30 focus:border-marca-500 [&::-webkit-search-cancel-button]:hidden"
                />
                {initialSearch && (
                    <button type="button" onClick={limpiar} aria-label="Limpiar búsqueda" className="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 cursor-pointer">
                        <XIcon className="w-4 h-4" />
                    </button>
                )}
            </div>
            <Button type="submit">Buscar</Button>
            {children}
        </form>
    );
}
