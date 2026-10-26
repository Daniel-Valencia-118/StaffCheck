import React from 'react';
import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import EstadoBadge from '../../Components/Shared/EstadoBadge';
import PageHeader from '../../Components/Shared/PageHeader';
import Aviso from '../../Components/Shared/Aviso';
import FichaWizard from '../Personal/components/FichaWizard';

export default function MiFicha({ vinculada, estado, nombreCompleto, fotoActual, ficha, motivo }) {
    if (!vinculada) {
        return (
            <AuthenticatedLayout>
                <Head title="Mi ficha" />
                <PageHeader title="Mi ficha" />
                <Aviso tono="info">Su cuenta todavía no está vinculada a una ficha de personal. Consulte con Recursos Humanos.</Aviso>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Mi ficha" />

            <PageHeader
                title={<span className="uppercase">{nombreCompleto}</span>}
                subtitle="Al guardar, su ficha vuelve a revisión de Recursos Humanos. El CI no se puede modificar."
            >
                <EstadoBadge estado={estado} />
            </PageHeader>

            {estado === 'observado' && motivo && (
                <Aviso tono="observacion"><strong>Observación de Recursos Humanos:</strong> {motivo}</Aviso>
            )}

            <FichaWizard ficha={ficha} fotoActual={fotoActual} url={route('mi_ficha.update')} ciBloqueado />
        </AuthenticatedLayout>
    );
}
