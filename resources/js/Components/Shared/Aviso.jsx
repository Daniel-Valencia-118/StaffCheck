import React from 'react';

const TONOS = {
    info: 'bg-marca-50 border-marca-200 text-marca-800',
    exito: 'bg-emerald-50 border-emerald-200 text-emerald-800',
    alerta: 'bg-amber-50 border-amber-200 text-amber-900',
    observacion: 'bg-orange-50 border-orange-200 text-orange-800',
    error: 'bg-red-50 border-red-200 text-red-700',
};

export default function Aviso({ tono = 'info', className = 'mb-4', children }) {
    return (
        <div role={tono === 'error' ? 'alert' : undefined} className={`p-3 rounded-lg border text-sm ${TONOS[tono] ?? TONOS.info} ${className}`}>
            {children}
        </div>
    );
}
