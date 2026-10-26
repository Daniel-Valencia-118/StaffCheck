export const formatFecha = (fechaString) => {
    if (!fechaString) return '-';

    const parteFecha = fechaString.split('T')[0];
    const partes = parteFecha.split('-');
    
    if (partes.length !== 3) return '-';

    const [anio, mes, dia] = partes;

    return `${dia}/${mes}/${anio}`;
};

export const hoyLocal = () => {
    const ahora = new Date();
    const dos = (n) => String(n).padStart(2, '0');

    return `${ahora.getFullYear()}-${dos(ahora.getMonth() + 1)}-${dos(ahora.getDate())}`;
};
