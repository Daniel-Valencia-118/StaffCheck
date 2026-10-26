import React from 'react';
import { useForm } from '@inertiajs/react';
import Select from '../../../Components/Forms/Select';
import Textarea from '../../../Components/Forms/Textarea';
import Button from '../../../Components/Forms/Button';
import Card from '../../../Components/Forms/Card';
import EstadoBadge from '../../../Components/Shared/EstadoBadge';
import usePuede from '../../../hooks/usePuede';

export default function RevisionCard({ persona, revisiones, estados, estadosConMotivo }) {
    const puede = usePuede();
    const { data, setData, put, processing, errors, reset } = useForm({ estado: '', motivo: '' });
    const exigeMotivo = estadosConMotivo.includes(data.estado);

    const opciones = estados
        .filter((estado) => estado !== persona.estado)
        .map((estado) => ({ value: estado, label: estado.charAt(0).toUpperCase() + estado.slice(1) }));

    const enviar = (e) => {
        e.preventDefault();
        put(route('personal.estado', persona.id), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <Card title="Revisión de la ficha" acciones={<EstadoBadge estado={persona.estado} />} className="">

            {puede('personal.cambiar_estado') && (
                <form onSubmit={enviar} className="grid grid-cols-1 md:grid-cols-3 gap-x-4 mb-4 pb-4 border-b border-gray-100">
                    <Select
                        label="Nuevo estado"
                        name="estado"
                        value={data.estado}
                        onChange={(e) => setData('estado', e.target.value)}
                        options={opciones}
                        error={errors.estado}
                        required
                    />
                    <div className="md:col-span-2">
                        <Textarea
                            label={exigeMotivo ? 'Motivo (obligatorio)' : 'Motivo (opcional)'}
                            name="motivo"
                            value={data.motivo}
                            onChange={(e) => setData('motivo', e.target.value)}
                            error={errors.motivo}
                            required={exigeMotivo}
                            maxLength={1000}
                            rows={2}
                            placeholder="Explique qué debe corregirse o por qué se rechaza"
                        />
                    </div>
                    <div className="md:col-span-3 flex justify-end">
                        <Button type="submit" loading={processing} disabled={!data.estado}>Cambiar estado</Button>
                    </div>
                </form>
            )}

            <h3 className="text-sm font-semibold text-marca-800 mb-2">Historial</h3>
            {revisiones.length > 0 ? (
                <ol className="space-y-3 max-h-80 overflow-y-auto pr-1">
                    {revisiones.map((revision) => (
                        <li key={revision.id} className="text-sm border-l-2 border-marca-200 pl-3">
                            <div className="flex flex-wrap items-center gap-2">
                                {revision.estado_anterior && (
                                    <>
                                        <EstadoBadge estado={revision.estado_anterior} />
                                        <span className="text-gray-400">→</span>
                                    </>
                                )}
                                <EstadoBadge estado={revision.estado_nuevo} />
                                <span className="text-gray-500">{revision.fecha} · {revision.usuario}</span>
                            </div>
                            {revision.motivo && <p className="mt-1 text-gray-700 whitespace-pre-line">{revision.motivo}</p>}
                        </li>
                    ))}
                </ol>
            ) : (
                <p className="text-sm text-gray-500 italic">Sin cambios de estado registrados.</p>
            )}
        </Card>
    );
}
