import { usePage } from '@inertiajs/react';

export default function usePuede() {
    const permisos = usePage().props.auth?.user?.permisos ?? [];

    return (permiso) => permisos.includes(permiso);
}
