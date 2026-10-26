import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import PageHeader from '../../Components/Shared/PageHeader';
import FichaWizard from './components/FichaWizard';

export default function PersonalEditar({ personaId, nombreCompleto, fotoActual, ficha }) {
    return (
        <AuthenticatedLayout>
            <Head title={`Editar ficha: ${nombreCompleto}`} />

            <PageHeader
                title={<span className="uppercase">Editar ficha: {nombreCompleto}</span>}
                volver={{ href: route('personal.show', personaId), texto: 'Volver a la ficha' }}
            />

            <FichaWizard ficha={ficha} fotoActual={fotoActual} url={route('personal.update', personaId)} />
        </AuthenticatedLayout>
    );
}
