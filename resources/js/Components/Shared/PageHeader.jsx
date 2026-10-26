import React from 'react';
import { Link } from '@inertiajs/react';
import { ChevronLeftIcon } from './Icons';

export default function PageHeader({ title, subtitle, volver, children }) {
    return (
        <div className="mb-6">
            {volver && (
                <Link href={volver.href} className="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-marca-700 mb-2">
                    <ChevronLeftIcon className="w-4 h-4" />
                    {volver.texto}
                </Link>
            )}
            <div className="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                <div className="min-w-0 max-w-full">
                    <h1 className="text-2xl font-bold text-marca-900 wrap-break-word">{title}</h1>
                    {subtitle && <div className="mt-1 text-sm text-gray-500">{subtitle}</div>}
                </div>
                {children && <div className="flex flex-wrap items-center gap-2">{children}</div>}
            </div>
        </div>
    );
}
