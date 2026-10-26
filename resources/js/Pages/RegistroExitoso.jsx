import React from 'react';
import { Head } from '@inertiajs/react';
import PublicLayout from '../Components/Layouts/PublicLayout';

export default function RegistroExitoso() {
    return (
        <PublicLayout>
            <Head title="Datos enviados" />
            <div className="w-full max-w-md mx-auto py-8">
                <div className="bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center">
                    <div className="mx-auto mb-4 w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5" aria-hidden="true">
                            <path strokeLinecap="round" strokeLinejoin="round" d="m5 13 4 4L19 7" />
                        </svg>
                    </div>
                    <h1 className="text-2xl font-bold text-marca-900 mb-2">¡Datos enviados!</h1>
                    <p className="text-gray-600">
                        Sus datos fueron guardados correctamente. Recursos Humanos los revisará y se comunicará con usted si falta algo.
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
