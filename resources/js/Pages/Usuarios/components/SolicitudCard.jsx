import React from 'react';
import { router, useForm } from '@inertiajs/react';
import Card from '../../../Components/Forms/Card';
import Select from '../../../Components/Forms/Select';
import Button from '../../../Components/Forms/Button';

export default function SolicitudCard({ usuario, roles }) {
    const { data, setData, put, processing, errors } = useForm({ rol: usuario.rol });

    const aprobar = (e) => {
        e.preventDefault();
        put(route('usuarios.aprobar', usuario.id), { preserveScroll: true });
    };

    const rechazar = () => {
        if (confirm(`¿Rechazar y eliminar la solicitud de ${usuario.email}?`)) {
            router.delete(route('usuarios.rechazar', usuario.id));
        }
    };

    return (
        <Card title="Solicitud de cuenta pendiente" className="mb-6 ring-2 ring-amber-200">
            <p className="text-sm text-gray-600 mb-4">
                Esta persona solicitó una cuenta desde el formulario público. Verifique su identidad, elija el rol y apruébela.
                Si es personal de la institución, vincule además su ficha más abajo para que pueda corregirla desde «Mi ficha».
            </p>
            <form onSubmit={aprobar} className="grid grid-cols-1 sm:grid-cols-3 gap-x-4 items-end">
                <Select
                    label="Rol"
                    id="rol_aprobacion"
                    name="rol"
                    value={data.rol}
                    onChange={(e) => setData('rol', e.target.value)}
                    options={roles}
                    error={errors.rol}
                    required
                />
                <div className="mb-4 flex gap-2 sm:col-span-2">
                    <Button type="submit" variant="success" loading={processing}>Aprobar y activar</Button>
                    <Button type="button" variant="dangerOutline" onClick={rechazar}>Rechazar</Button>
                </div>
            </form>
        </Card>
    );
}
