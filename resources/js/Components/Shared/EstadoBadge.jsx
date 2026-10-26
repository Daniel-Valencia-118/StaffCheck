import React from 'react';
import Badge from './Badge';

const TONO_ESTADO = {
    aprobado: 'verde',
    pendiente: 'ambar',
    observado: 'naranja',
    rechazado: 'rojo',
};

export default function EstadoBadge({ estado }) {
    return <Badge tono={TONO_ESTADO[estado] ?? 'gris'}>{estado ?? 'sin estado'}</Badge>;
}
