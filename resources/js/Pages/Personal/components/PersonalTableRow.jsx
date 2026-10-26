import React from 'react';
import { Link, router } from '@inertiajs/react';
import usePuede from '../../../hooks/usePuede';
import EstadoBadge from '../../../Components/Shared/EstadoBadge';
import { clasesBoton } from '../../../Components/Forms/Button';
import { nombreOficina } from '../../../utils/fichas';
import { EyeIcon } from '../../../Components/Shared/Icons';

const celda = 'px-4 py-3 whitespace-nowrap text-sm text-gray-800';

export default function PersonalTableRow({ persona, papelera = false }) {
    const puede = usePuede();

    const restaurar = () => {
        if (confirm(`¿Restaurar la ficha de ${persona.nombres} ${persona.paterno}?`)) {
            router.put(route('personal.restore', persona.id), {}, { preserveScroll: true });
        }
    };

    return (
        <tr className="hover:bg-marca-50/40 transition-colors">
            <td className={`${celda} uppercase font-medium text-gray-900`}>{persona.paterno}</td>
            <td className={`${celda} uppercase`}>{persona.materno}</td>
            <td className={`${celda} uppercase`}>{persona.nombres}</td>
            <td className={celda}>{persona.ci} <span className="text-gray-400">{persona.ci_expedicion}</span></td>
            <td className={`${celda} lowercase`}>{persona.email}</td>
            <td className={celda}>{persona.celular}</td>
            <td className={celda}>{nombreOficina(persona.oficina_actual)}</td>
            <td className={`${celda} uppercase`}>{persona.cargo_actual}</td>
            <td className={celda}><EstadoBadge estado={persona.estado} /></td>
            <td className={`${celda} text-right`}>
                {papelera ? (
                    <button type="button" onClick={restaurar} className={clasesBoton('success', 'sm')}>
                        Restaurar
                    </button>
                ) : puede('personal.ver_detalle') && (
                    <Link href={route('personal.show', persona.id)} className={clasesBoton('primary', 'sm')}>
                        <EyeIcon />
                        Ver ficha
                    </Link>
                )}
            </td>
        </tr>
    );
}
