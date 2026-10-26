import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Card from '../../Components/Forms/Card';
import PageHeader from '../../Components/Shared/PageHeader';
import UsuarioForm from './components/UsuarioForm';

export default function UsuariosCrear({ roles }) {
    const form = useForm({
        nombre: '',
        paterno: '',
        materno: '',
        email: '',
        rol: roles.some((r) => r.value === 'encargado') ? 'encargado' : '',
        password: '',
        password_confirmation: '',
    });

    const guardar = (e) => {
        e.preventDefault();
        form.post(route('usuarios.store'), {
            onError: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Nuevo usuario" />

            <PageHeader title="Nuevo usuario" volver={{ href: route('usuarios.index'), texto: 'Volver a usuarios' }} />

            <Card>
                <UsuarioForm form={form} roles={roles} onSubmit={guardar} conContrasena textoBoton="Crear usuario" />
            </Card>
        </AuthenticatedLayout>
    );
}
