import React, { useState } from 'react';
import { useForm, Head, Link, usePage } from '@inertiajs/react';
import PublicLayout from '../../Components/Layouts/PublicLayout';
import Input from '../../Components/Forms/Input';
import Button from '../../Components/Forms/Button';
import Verificacion from '../../Components/Forms/Verificacion';
import Aviso from '../../Components/Shared/Aviso';
import Logo from '../../Components/Shared/Logo';

export default function Login({ registroCuentasAbierto = false }) {
    const { flash } = usePage().props;
    const [reinicio, setReinicio] = useState(0);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
        cf_turnstile_response: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onError: () => {
                reset('password', 'cf_turnstile_response');
                setReinicio((n) => n + 1);
            },
        });
    };

    return (
        <PublicLayout>
            <Head title="Iniciar sesión" />

            <div className="flex items-center justify-center py-6">
                <div className="w-full max-w-md bg-white rounded-2xl shadow-sm p-8 border border-gray-200">
                    <div className="text-center mb-6">
                        <Logo className="text-3xl" />
                        <h1 className="text-base font-semibold text-gray-700 mt-3">Iniciar sesión</h1>
                        <p className="text-sm text-gray-500 mt-1">Acceso para el personal autorizado de la institución.</p>
                    </div>

                    {flash?.success && <Aviso tono="exito">{flash.success}</Aviso>}
                    {flash?.error && <Aviso tono="error">{flash.error}</Aviso>}

                    <form onSubmit={submit}>
                        <Input
                            label="Correo electrónico"
                            name="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            error={errors.email}
                            placeholder="ejemplo@fundacion.org"
                            autoComplete="username"
                            required
                        />
                        <Input
                            label="Contraseña"
                            name="password"
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            error={errors.password}
                            autoComplete="current-password"
                            required
                        />

                        <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                className="h-4 w-4 rounded border-gray-300 accent-marca-700 cursor-pointer"
                            />
                            Recordar sesión
                        </label>

                        <Verificacion
                            reinicio={reinicio}
                            onToken={(token) => setData('cf_turnstile_response', token)}
                            error={errors.cf_turnstile_response}
                        />

                        <Button type="submit" className="w-full" loading={processing} disabled={!data.cf_turnstile_response}>
                            {processing ? 'Ingresando...' : 'Ingresar'}
                        </Button>
                    </form>

                    {registroCuentasAbierto && (
                        <p className="text-center text-sm text-gray-600 mt-6">
                            ¿Trabaja en la institución y no tiene cuenta?{' '}
                            <Link href={route('registro.cuenta')} className="text-marca-700 hover:underline font-medium">Solicitar acceso</Link>
                        </p>
                    )}
                </div>
            </div>
        </PublicLayout>
    );
}
