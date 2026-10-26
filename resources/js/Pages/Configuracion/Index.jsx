import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Card from '../../Components/Forms/Card';
import Input from '../../Components/Forms/Input';
import Select from '../../Components/Forms/Select';
import Button from '../../Components/Forms/Button';
import Textarea from '../../Components/Forms/Textarea';
import PageHeader from '../../Components/Shared/PageHeader';

const ESTADOS_INICIALES = [
    { value: 'aprobado', label: 'Aprobado automáticamente' },
    { value: 'pendiente', label: 'Pendiente de revisión' },
];

export default function ConfiguracionIndex({ valores }) {
    const { data, setData, put, errors, processing, isDirty, setDefaults } = useForm(valores);

    const guardar = (e) => {
        e.preventDefault();
        put(route('configuracion.update'), { preserveScroll: true, onSuccess: () => setDefaults() });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Configuración" />
            <PageHeader title="Configuración" subtitle="Ajustes del sistema. Los cambios se aplican al guardar." />

            <form onSubmit={guardar}>
                <Card title="Institución">
                    <Input
                        label="Nombre de la institución"
                        name="institucion_nombre"
                        value={data.institucion_nombre}
                        onChange={(e) => setData('institucion_nombre', e.target.value)}
                        error={errors.institucion_nombre}
                        required
                    />
                </Card>

                <Card title="Formulario público de registro">
                    <label className="flex items-center gap-3 mb-4 cursor-pointer">
                        <input
                            className="h-4 w-4 accent-marca-700"
                            type="checkbox"
                            checked={data.registro_abierto}
                            onChange={(e) => setData('registro_abierto', e.target.checked)}
                        />
                        <span className="text-sm font-medium text-gray-700">Registro abierto</span>
                    </label>

                    <Textarea
                        label="Mensaje cuando el registro está cerrado"
                        name="registro_mensaje_cerrado"
                        rows={2}
                        value={data.registro_mensaje_cerrado}
                        onChange={(e) => setData('registro_mensaje_cerrado', e.target.value)}
                        error={errors.registro_mensaje_cerrado}
                    />

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <Select
                                label="Estado inicial de las fichas nuevas"
                                name="fichas_estado_inicial"
                                value={data.fichas_estado_inicial}
                                onChange={(e) => setData('fichas_estado_inicial', e.target.value)}
                                options={ESTADOS_INICIALES}
                                error={errors.fichas_estado_inicial}
                                required
                            />
                            {data.fichas_estado_inicial === 'pendiente' && (
                                <p className="text-xs text-amber-700 -mt-3 mb-4">
                                    Las fichas quedarán pendientes hasta que alguien con permiso para cambiar el estado las apruebe.
                                </p>
                            )}
                        </div>
                        <Input
                            label="Envíos por hora desde una misma red"
                            name="registro_max_por_hora"
                            type="number"
                            min={5}
                            max={500}
                            value={data.registro_max_por_hora}
                            onChange={(e) => setData('registro_max_por_hora', e.target.value)}
                            error={errors.registro_max_por_hora}
                            required
                        />
                        <Input
                            label="Tamaño máximo de la foto (KB)"
                            name="foto_max_kb"
                            type="number"
                            min={256}
                            max={5120}
                            value={data.foto_max_kb}
                            onChange={(e) => setData('foto_max_kb', e.target.value)}
                            error={errors.foto_max_kb}
                            required
                        />
                    </div>
                </Card>

                <Card title="Cuentas y correcciones">
                    <label className="flex items-center gap-3 mb-2 cursor-pointer">
                        <input
                            className="h-4 w-4 accent-marca-700"
                            type="checkbox"
                            checked={data.registro_cuentas_abierto}
                            onChange={(e) => setData('registro_cuentas_abierto', e.target.checked)}
                        />
                        <span className="text-sm font-medium text-gray-700">Permitir que el personal solicite una cuenta</span>
                    </label>
                    <p className="text-xs text-gray-500 mb-4">
                        Las solicitudes quedan pendientes hasta que un administrador las apruebe y asigne un rol en Usuarios.
                    </p>
                    <Input
                        label="Validez del enlace de corrección (días)"
                        name="enlace_correccion_dias"
                        type="number"
                        min={1}
                        max={30}
                        value={data.enlace_correccion_dias}
                        onChange={(e) => setData('enlace_correccion_dias', e.target.value)}
                        error={errors.enlace_correccion_dias}
                        required
                    />
                </Card>

                <Card title="Seguridad del navegador">
                    <label className="flex items-center gap-3 mb-2 cursor-pointer">
                        <input
                            className="h-4 w-4 accent-marca-700"
                            type="checkbox"
                            checked={data.csp_aplicar}
                            onChange={(e) => setData('csp_aplicar', e.target.checked)}
                        />
                        <span className="text-sm font-medium text-gray-700">Aplicar la política de contenido (CSP)</span>
                    </label>
                    <p className="text-xs text-gray-500">
                        Apagada, el navegador solo informa en su consola lo que bloquearía. Actívela cuando haya revisado que
                        el registro, el panel, los mapas y los PDF funcionan sin avisos.
                    </p>
                </Card>

                <div className="sticky bottom-0 -mx-4 md:-mx-8 px-4 md:px-8 py-3 bg-gray-50/90 backdrop-blur-xs border-t border-gray-200 flex items-center justify-end gap-3">
                    {isDirty && <span className="text-sm text-amber-700">Hay cambios sin guardar.</span>}
                    <Button type="submit" loading={processing} disabled={!isDirty}>Guardar configuración</Button>
                </div>
            </form>
        </AuthenticatedLayout>
    );
}
