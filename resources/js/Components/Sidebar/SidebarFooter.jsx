import React from 'react';
import { LogoutIcon } from '../Shared/Icons';

export default function SidebarFooter({ user, colapsado, onLogout }) {
    const iniciales = `${user?.nombre?.[0] || 'U'}${user?.paterno?.[0] || ''}`.toUpperCase();
    const nombre = `${user?.nombre ?? ''} ${user?.paterno ?? ''}`.trim();

    return (
        <div className={`p-3 border-t border-gray-100 bg-gray-50/60 flex items-center gap-3 ${colapsado ? 'md:flex-col md:gap-2' : ''}`}>
            <div
                className="w-9 h-9 rounded-lg bg-marca-100 text-marca-800 flex items-center justify-center text-xs font-bold tracking-wider shrink-0"
                title={colapsado ? nombre : undefined}
            >
                {iniciales}
            </div>
            <div className={`min-w-0 flex-1 ${colapsado ? 'md:hidden' : ''}`}>
                <p className="text-xs font-semibold text-gray-900 truncate capitalize">{nombre}</p>
                <p className="text-[11px] text-gray-500 truncate">{user?.email}</p>
            </div>
            <button
                type="button"
                onClick={onLogout}
                title="Cerrar sesión"
                aria-label="Cerrar sesión"
                className="inline-flex items-center gap-1.5 p-2 text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 border border-red-200 rounded-lg transition-colors shrink-0 cursor-pointer"
            >
                <LogoutIcon className="w-5 h-5" />
                <span className={`text-xs font-semibold ${colapsado ? 'md:hidden' : ''}`}>Salir</span>
            </button>
        </div>
    );
}
