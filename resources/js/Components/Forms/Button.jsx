import React from 'react';

const VARIANTES = {
    primary: 'bg-marca-700 hover:bg-marca-800 text-white shadow-sm',
    secondary: 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 shadow-xs',
    soft: 'bg-marca-50 hover:bg-marca-100 text-marca-700',
    agregar: 'bg-marca-600 hover:bg-marca-700 text-white shadow-sm',
    excel: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
    excelOutline: 'bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-600',
    pdf: 'bg-red-600 hover:bg-red-700 text-white shadow-sm',
    success: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm',
    danger: 'bg-red-600 hover:bg-red-700 text-white shadow-sm',
    dangerOutline: 'bg-white hover:bg-red-50 text-red-700 border border-red-300',
};

const TAMANOS = {
    md: 'px-4 py-2 text-sm',
    sm: 'px-2.5 py-1 text-xs',
};

export function clasesBoton(variant = 'primary', size = 'md') {
    return `inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-colors cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-marca-500 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed ${VARIANTES[variant] ?? VARIANTES.primary} ${TAMANOS[size] ?? TAMANOS.md}`;
}

export default function Button({
    type = 'button',
    children,
    variant = 'primary',
    size = 'md',
    loading = false,
    disabled = false,
    className = '',
    ...props
}) {
    return (
        <button
            type={type}
            disabled={disabled || loading}
            className={`${clasesBoton(variant, size)} ${className}`}
            {...props}
        >
            {loading && (
                <svg className="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            )}
            {children}
        </button>
    );
}
