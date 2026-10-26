import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Card from '../../Components/Forms/Card';
import PageHeader from '../../Components/Shared/PageHeader';
import Input from '../../Components/Forms/Input';
import Button from '../../Components/Forms/Button';
import UsuarioForm from './components/UsuarioForm';
import SolicitudCard from './components/SolicitudCard';
import FichaVinculadaCard from './components/FichaVinculadaCard';

export default function UsuariosEditar({ usuario, roles, esPropio, ficha = null, fichaSugerida = null }) {
    const form = useForm({
        nombre: usuario.nombre,
        paterno: usuario.paterno,
        materno: usuario.materno,
        email: usuario.email,
        rol: usuario.rol,
    });

    const contrasena = useForm({ password: '', password_confirmation: '' });

    const guardar = (e) => {
        e.preventDefault();
        form.put(route('usuarios.update', usuario.id));
    };

    const restablecer = (e) => {
        e.preventDefault();
        contrasena.put(route('usuarios.contrasena', usuario.id), {
            preserveScroll: true,
            onFinish: () => contrasena.reset(),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Editar usuario" />

            <PageHeader title="Editar usuario" volver={{ href: route('usuarios.index'), texto: 'Volver a usuarios' }} />

            {usuario.pendiente && <SolicitudCard usuario={usuario} roles={roles} />}

            <Card title="Datos y rol">
                <UsuarioForm
                    form={form}
                    roles={roles}
                    onSubmit={guardar}
                    rolBloqueado={esPropio}
                    textoBoton="Guardar cambios"
                />
            </Card>

            <FichaVinculadaCard usuario={usuario} ficha={ficha} fichaSugerida={fichaSugerida} />

            <Card title="Restablecer contraseña">
                <p className="text-sm text-gray-500 mb-4">
                    Defina una contraseña temporal y comuníquela al usuario por un medio seguro.
                </p>
                <form onSubmit={restablecer} className="grid grid-cols-1 sm:grid-cols-3 gap-x-4 items-end">
                    <Input
                        label="Nueva contraseña"
                        name="password"
                        type="password"
                        value={contrasena.data.password}
                        onChange={(e) => contrasena.setData('password', e.target.value)}
                        error={contrasena.errors.password}
                        required
                    />
                    <Input
                        label="Confirmar"
                        name="password_confirmation"
                        type="password"
                        value={contrasena.data.password_confirmation}
                        onChange={(e) => contrasena.setData('password_confirmation', e.target.value)}
                        required
                    />
                    <div className="mb-4">
                        <Button type="submit" variant="secondary" loading={contrasena.processing}>
                            Restablecer
                        </Button>
                    </div>
                </form>
            </Card>
        </AuthenticatedLayout>
    );
}
