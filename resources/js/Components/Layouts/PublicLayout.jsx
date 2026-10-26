import React from 'react';
import { usePage } from '@inertiajs/react';
import PublicFooter from './PublicFooter';
import Logo from '../Shared/Logo';

export default function PublicLayout({ children }) {
    const institucion = usePage().props.institucion || 'Hogar de Niños Alalay';

    return (
        <div className="min-h-screen flex flex-col bg-gray-50 text-gray-900">
            <header className="bg-marca-700 text-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-3">
                    <Logo claro className="text-xl" />
                    <p className="text-xs text-marca-100 truncate">{institucion}</p>
                </div>
            </header>
            <main className="flex-1 px-4 py-8 sm:px-6">
                {children}
            </main>
            <PublicFooter institucion={institucion} />
        </div>
    );
}
