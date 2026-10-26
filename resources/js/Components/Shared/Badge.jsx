import React from 'react';

const TONOS = {
    gris: 'bg-gray-100 text-gray-700 ring-gray-200',
    marca: 'bg-marca-50 text-marca-700 ring-marca-200',
    verde: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    ambar: 'bg-amber-50 text-amber-800 ring-amber-200',
    naranja: 'bg-orange-50 text-orange-700 ring-orange-200',
    rojo: 'bg-red-50 text-red-700 ring-red-200',
    violeta: 'bg-violet-50 text-violet-700 ring-violet-200',
};

export default function Badge({ tono = 'gris', children }) {
    return (
        <span className={`inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium uppercase tracking-wide ring-1 ring-inset ${TONOS[tono] ?? TONOS.gris}`}>
            {children}
        </span>
    );
}
