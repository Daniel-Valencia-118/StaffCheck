import React from 'react';
import { router, useForm } from '@inertiajs/react';
import Card from '../../../Components/Forms/Card';
import Input from '../../../Components/Forms/Input';
import Button from '../../../Components/Forms/Button';

export default function FichaVinculadaCard({ usuario, ficha, fichaSugerida }) {
    const { data, setData, put, processing, errors } = useForm({ ci: ficha?.ci ?? '' });

    const guardar = (e) => {
        e.preventDefault();
        put(route('usuarios.ficha', usuario.id), { preserveScroll: true });
    };

    const desvincular = () => {
        if (confirm('¿Desvincular la ficha de esta cuenta?')) {
            router.put(route('usuarios.ficha', usuario.id), { ci: '' }, {
                preserveScroll: true,
                onSuccess: () => setData('ci', ''),
            });
        }
    };

    return (
        <Card title="Ficha de personal vinculada">
            <p className="text-sm text-gray-600 mb-4">
                {ficha
                    ? <>Vinculada a <span className="font-medium uppercase">{ficha.nombre}</span> (CI {ficha.ci}). Con el permiso «Mi ficha», esta cuenta puede corregirla.</>
                    : 'Sin ficha vinculada. Indique el CI de la ficha de esta persona.'}
            </p>

            {fichaSugerida && (
                <p className="mb-4 text-sm text-marca-800 bg-marca-50 border border-marca-200 rounded-lg p-3">
                    Sugerencia: la ficha de <span className="font-medium uppercase">{fichaSugerida.nombre}</span> (CI {fichaSugerida.ci}) tiene el mismo correo.{' '}
                    <button type="button" className="underline cursor-pointer" onClick={() => setData('ci', fichaSugerida.ci)}>Usar este CI</button>
                </p>
            )}

            <form onSubmit={guardar} className="grid grid-cols-1 sm:grid-cols-3 gap-x-4 items-end">
                <Input
                    label="CI de la ficha"
                    name="ci"
                    value={data.ci}
                    onChange={(e) => setData('ci', e.target.value)}
                    error={errors.ci}
                />
                <div className="mb-4 flex gap-2 sm:col-span-2">
                    <Button type="submit" variant="secondary" loading={processing}>Vincular</Button>
                    {ficha && <Button type="button" variant="dangerOutline" onClick={desvincular}>Desvincular</Button>}
                </div>
            </form>
        </Card>
    );
}
