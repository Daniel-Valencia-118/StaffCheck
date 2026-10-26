import React, { useState } from 'react';
import { useForm, Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Components/Layouts/PublicLayout';
import Input from '../../Components/Forms/Input';
import Button from '../../Components/Forms/Button';
import Verificacion from '../../Components/Forms/Verificacion';

export default function Registro() {
    const [reinicio, setReinicio] = useState(0);
    const { data, setData, post, processing, errors, reset } = useForm({
        nombre: '',
        paterno: '',
        materno: '',
        email: '',
        password: '',
        password_confirmation: '',
        cf_turnstile_response: '',
    });

    const cambiar = (e) => setData(e.target.name, e.target.value);

    const enviar = (e) => {
        e.preventDefault();
        post(route('registro.cuenta.store'), {
            onError: () => {
                reset('password', 'password_confirmation', 'cf_turnstile_response');
                setReinicio((n) => n + 1);
            },
        });
    };

    return (
        <PublicLayout>
            <Head title="Solicitar cuenta" />

            <div className="flex items-center justify-center py-6">
                <div className="w-full max-w-lg bg-white rounded-2xl shadow-sm p-8 border border-gray-200">
                    <div className="text-center mb-6">
                        <h1 className="text-2xl font-bold text-marca-900">Solicitar cuenta</h1>
                        <p className="text-sm text-gray-500 mt-1">
                            Un administrador revisará su solicitud y le asignará acceso.
                        </p>
                    </div>

                    <form onSubmit={enviar}>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-x-3">
                            <Input label="Nombre" name="nombre" value={data.nombre} onChange={cambiar} error={errors.nombre} required />
                            <Input label="Apellido paterno" name="paterno" value={data.paterno} onChange={cambiar} error={errors.paterno} required />
                            <Input label="Apellido materno" name="materno" value={data.materno} onChange={cambiar} error={errors.materno} />
                        </div>
                        <Input label="Correo electrónico" name="email" type="email" value={data.email} onChange={cambiar} error={errors.email} autoComplete="email" required />
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-3">
                            <Input
                                label="Contraseña"
                                name="password"
                                type="password"
                                value={data.password}
                                onChange={cambiar}
                                error={errors.password}
                                ayuda="Mínimo 8 caracteres, con letras y números."
                                autoComplete="new-password"
                                required
                            />
                            <Input label="Confirmar contraseña" name="password_confirmation" type="password" value={data.password_confirmation} onChange={cambiar} autoComplete="new-password" required />
                        </div>

                        <Verificacion
                            reinicio={reinicio}
                            onToken={(token) => setData('cf_turnstile_response', token)}
                            error={errors.cf_turnstile_response}
                        />

                        <Button type="submit" className="w-full" loading={processing} disabled={!data.cf_turnstile_response}>
                            {processing ? 'Enviando...' : 'Enviar solicitud'}
                        </Button>
                    </form>

                    <p className="text-center text-sm text-gray-600 mt-6">
                        ¿Ya tiene cuenta? <Link href={route('login')} className="text-marca-700 hover:underline font-medium">Iniciar sesión</Link>
                    </p>
                </div>
            </div>
        </PublicLayout>
    );
}
