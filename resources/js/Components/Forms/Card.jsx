import React from 'react';

export default function Card({ title, acciones, children, className = 'mb-6' }) {
    return (
        <section className={`bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 ${className}`}>
            {(title || acciones) && (
                <div className="flex flex-wrap items-center justify-between gap-2 mb-4 pb-3 border-b border-gray-100">
                    {title && <h2 className="text-base font-semibold text-marca-800">{title}</h2>}
                    {acciones}
                </div>
            )}
            {children}
        </section>
    );
}
