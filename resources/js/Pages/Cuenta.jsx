import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../Components/Layouts/AuthenticatedLayout';
import Card from '../Components/Forms/Card';
import Input from '../Components/Forms/Input';
import Button from '../Components/Forms/Button';
import PageHeader from '../Components/Shared/PageHeader';

export default function CuentaIndex({ user }) {
    const { data, setData, patch, errors, processing, reset } = useForm({
        nombre: user.nombre || '',
        paterno: user.paterno || '',
        materno: user.materno || '',
        email: user.email || '',
        password: '',
        password_confirmation: '',
        current_password: '',
    });

    const cambiar = (e) => setData(e.target.name, e.target.value);

    const handleSubmit = (e) => {
        e.preventDefault();
        patch(route('cuenta.update'), {
            preserveScroll: true,
            onFinish: () => reset('password', 'password_confirmation', 'current_password'),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Mi cuenta" />
            <PageHeader title="Mi cuenta" subtitle="Sus datos de acceso al sistema." />

            <form onSubmit={handleSubmit} className="max-w-3xl">
                <Card title="Datos personales">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                        <Input label="Nombre" name="nombre" value={data.nombre} onChange={cambiar} error={errors.nombre} required />
                        <Input label="Apellido paterno" name="paterno" value={data.paterno} onChange={cambiar} error={errors.paterno} required />
                        <Input label="Apellido materno" name="materno" value={data.materno} onChange={cambiar} error={errors.materno} />
                        <Input label="Correo electrónico" name="email" type="email" value={data.email} onChange={cambiar} error={errors.email} autoComplete="email" required />
                    </div>
                </Card>

                <Card title="Cambiar contraseña">
                    <p className="text-sm text-gray-500 mb-4">Déjelo vacío si no desea cambiarla.</p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                        <Input
                            label="Nueva contraseña"
                            name="password"
                            type="password"
                            value={data.password}
                            onChange={cambiar}
                            error={errors.password}
                            ayuda="Mínimo 8 caracteres, con letras y números."
                            autoComplete="new-password"
                        />
                        <Input
                            label="Confirmar contraseña"
                            name="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={cambiar}
                            autoComplete="new-password"
                        />
                    </div>
                </Card>

                <Card title="Confirmar cambios">
                    <p className="text-sm text-gray-500 mb-4">Para cambiar el correo o la contraseña, ingrese su contraseña actual.</p>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 items-end">
                        <Input
                            label="Contraseña actual"
                            name="current_password"
                            type="password"
                            value={data.current_password}
                            onChange={cambiar}
                            error={errors.current_password}
                            autoComplete="current-password"
                        />
                        <div className="mb-4 flex md:justify-end">
                            <Button type="submit" loading={processing}>Guardar cambios</Button>
                        </div>
                    </div>
                </Card>
            </form>
        </AuthenticatedLayout>
    );
}
