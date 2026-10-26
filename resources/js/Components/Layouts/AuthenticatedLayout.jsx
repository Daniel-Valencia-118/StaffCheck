import React, { useEffect, useState } from 'react';
import { router } from '@inertiajs/react';
import Sidebar from '../Sidebar/Sidebar';
import FlashToaster from '../Shared/FlashToaster';
import { MenuIcon } from '../Shared/Icons';
import Logo from '../Shared/Logo';

const CLAVE_COLAPSADO = 'staffcheck.menu.colapsado';

const leerColapsado = () => {
    try {
        return window.localStorage.getItem(CLAVE_COLAPSADO) === '1';
    } catch {
        return false;
    }
};

const guardarColapsado = (valor) => {
    try {
        window.localStorage.setItem(CLAVE_COLAPSADO, valor ? '1' : '0');
    } catch {
        return;
    }
};

export default function AuthenticatedLayout({ children }) {
    const [colapsado, setColapsado] = useState(leerColapsado);
    const [abierto, setAbierto] = useState(false);

    useEffect(() => router.on('navigate', () => setAbierto(false)), []);

    useEffect(() => {
        if (!abierto) return undefined;
        const alPresionar = (e) => e.key === 'Escape' && setAbierto(false);
        window.addEventListener('keydown', alPresionar);
        return () => window.removeEventListener('keydown', alPresionar);
    }, [abierto]);

    const alternarColapsado = () => {
        guardarColapsado(!colapsado);
        setColapsado(!colapsado);
    };

    return (
        <div className="flex h-screen bg-gray-50 text-gray-900 overflow-hidden">
            <FlashToaster />

            <Sidebar colapsado={colapsado} onColapsar={alternarColapsado} abierto={abierto} onCerrar={() => setAbierto(false)} />

            {abierto && (
                <div className="fixed inset-0 z-40 bg-gray-900/30 backdrop-blur-xs md:hidden" onClick={() => setAbierto(false)} aria-hidden="true" />
            )}

            <div className="flex flex-col flex-1 min-w-0">
                <header className="h-14 bg-white border-b border-gray-200 flex items-center gap-3 px-4 shrink-0 md:hidden">
                    <button
                        type="button"
                        onClick={() => setAbierto(true)}
                        aria-label="Abrir menú"
                        aria-expanded={abierto}
                        aria-controls="menu-lateral"
                        className="p-2 -ml-2 rounded-lg text-gray-600 hover:text-marca-700 hover:bg-marca-50 cursor-pointer"
                    >
                        <MenuIcon className="w-5 h-5" />
                    </button>
                    <Logo className="text-lg" />
                </header>

                <main {...{ 'scroll-region': '' }} className="flex-1 overflow-y-auto p-4 md:p-8">
                    <div className="max-w-7xl mx-auto">{children}</div>
                </main>
            </div>
        </div>
    );
}
