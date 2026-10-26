import React from 'react';

export function clasesCampo(error, className = '') {
    return `w-full px-3.5 py-2 text-sm border rounded-lg bg-white transition-colors placeholder:text-gray-400 focus:outline-none focus:ring-2 disabled:bg-gray-100 disabled:text-gray-500 ${
        error ? 'border-red-400 focus:ring-red-500/40 focus:border-red-500' : 'border-gray-300 focus:ring-marca-500/30 focus:border-marca-500'
    } ${className}`;
}

export default function Campo({ id, label, required = false, error, ayuda, className = 'mb-4', children }) {
    return (
        <div className={className}>
            {label && (
                <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
                    {label} {required && <span className="text-red-500" aria-hidden="true">*</span>}
                </label>
            )}
            {children}
            {ayuda && !error && <p className="mt-1 text-xs text-gray-500">{ayuda}</p>}
            {error && <p id={`${id}-error`} className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}
