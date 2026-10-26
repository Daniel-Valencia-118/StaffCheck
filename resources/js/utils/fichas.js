const OFICINAS = {
    'oficina central la paz': 'La Paz',
    'oficina santa cruz': 'Santa Cruz',
};

export const nombreOficina = (oficina) => OFICINAS[(oficina ?? '').toLowerCase()] ?? (oficina || '-');
