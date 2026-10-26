import React, { useMemo, useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../Components/Layouts/AuthenticatedLayout';
import Card from '../../Components/Forms/Card';
import Input from '../../Components/Forms/Input';
import Button from '../../Components/Forms/Button';
import PageHeader from '../../Components/Shared/PageHeader';
import Aviso from '../../Components/Shared/Aviso';

const MODULOS = {
    panel: 'Panel',
    personal: 'Personal',
    mi_ficha: 'Mi ficha',
    usuarios: 'Usuarios',
    roles: 'Roles y permisos',
    configuracion: 'Configuración',
    auditoria: 'Auditoría',
    sistema: 'Sistema',
};

export default function RolesIndex({ roles, permisos }) {
    const [seleccionadoId, setSeleccionadoId] = useState(roles.find((r) => !r.esAdmin)?.id ?? roles[0]?.id);
    const seleccionado = roles.find((r) => r.id === seleccionadoId);

    const [marcados, setMarcados] = useState(seleccionado?.permisos ?? []);
    const [guardando, setGuardando] = useState(false);
    const nuevoRol = useForm({ nombre: '' });

    const grupos = useMemo(() => {
        const orden = Object.keys(MODULOS);
        const porModulo = permisos.reduce((acc, permiso) => {
            (acc[permiso.modulo] ??= []).push(permiso);
            return acc;
        }, {});

        return Object.fromEntries(Object.entries(porModulo).sort(([a], [b]) => {
            const ia = orden.indexOf(a) === -1 ? orden.length : orden.indexOf(a);
            const ib = orden.indexOf(b) === -1 ? orden.length : orden.indexOf(b);
            return ia - ib;
        }));
    }, [permisos]);

    const seleccionar = (rol) => {
        setSeleccionadoId(rol.id);
        setMarcados(rol.permisos);
    };

    const alternar = (nombre) => {
        setMarcados((actuales) => actuales.includes(nombre)
            ? actuales.filter((p) => p !== nombre)
            : [...actuales, nombre]);
    };

    const guardarPermisos = () => {
        router.put(route('roles.update', seleccionado.id), { permisos: marcados }, {
            preserveScroll: true,
            onStart: () => setGuardando(true),
            onFinish: () => setGuardando(false),
        });
    };

    const crearRol = (e) => {
        e.preventDefault();
        nuevoRol.post(route('roles.store'), {
            preserveScroll: true,
            onSuccess: () => nuevoRol.reset(),
        });
    };

    const eliminarRol = () => {
        if (confirm(`¿Eliminar el rol "${seleccionado.nombre}"? Esta acción no se puede deshacer.`)) {
            router.delete(route('roles.destroy', seleccionado.id), {
                preserveScroll: true,
                onSuccess: () => {
                    const siguiente = roles.find((r) => r.id !== seleccionado.id && !r.esAdmin);
                    if (siguiente) seleccionar(siguiente);
                },
            });
        }
    };

    const sinCambios = seleccionado
        && marcados.length === seleccionado.permisos.length
        && marcados.every((p) => seleccionado.permisos.includes(p));

    return (
        <AuthenticatedLayout>
            <Head title="Roles y permisos" />
            <PageHeader title="Roles y permisos" subtitle="Elija un rol para ver y cambiar lo que puede hacer." />

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div>
                    <Card title="Roles">
                        <ul className="space-y-1 mb-4">
                            {roles.map((rol) => (
                                <li key={rol.id}>
                                    <button
                                        type="button"
                                        onClick={() => seleccionar(rol)}
                                        className={`w-full flex items-center justify-between px-3 py-2 rounded-md text-sm text-left transition-colors cursor-pointer ${
                                            rol.id === seleccionadoId ? 'bg-marca-700 text-white font-semibold' : 'hover:bg-marca-50 text-gray-700'
                                        }`}
                                    >
                                        <span>{rol.nombre}</span>
                                        <span className={`text-xs ${rol.id === seleccionadoId ? 'text-marca-100' : 'text-gray-400'}`}>
                                            {rol.usuarios} {rol.usuarios === 1 ? 'usuario' : 'usuarios'}
                                        </span>
                                    </button>
                                </li>
                            ))}
                        </ul>

                        <form onSubmit={crearRol} className="border-t border-gray-100 pt-4">
                            <Input
                                label="Nuevo rol"
                                name="nombre"
                                value={nuevoRol.data.nombre}
                                onChange={(e) => nuevoRol.setData('nombre', e.target.value.toLowerCase())}
                                error={nuevoRol.errors.nombre}
                                placeholder="ej.: jefe_oficina"
                            />
                            <Button type="submit" loading={nuevoRol.processing} className="w-full">Crear rol</Button>
                        </form>
                    </Card>
                </div>

                <div className="lg:col-span-2">
                    {seleccionado && (
                        <Card title={`Permisos de "${seleccionado.nombre}"`}>
                            {seleccionado.esAdmin ? (
                                <p className="text-sm text-gray-600">
                                    El rol <strong>admin</strong> tiene todos los permisos, incluidos los que se agreguen en el futuro. No se edita.
                                </p>
                            ) : (
                                <>
                                    <div className="space-y-5">
                                        {Object.entries(grupos).map(([modulo, lista]) => (
                                            <fieldset key={modulo}>
                                                <legend className="text-xs font-semibold uppercase tracking-wider text-marca-700 mb-2">
                                                    {MODULOS[modulo] ?? modulo}
                                                </legend>
                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                    {lista.map((permiso) => (
                                                        <label key={permiso.nombre} className="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                                                            <input
                                                                type="checkbox"
                                                                className="mt-0.5 h-4 w-4 accent-marca-700"
                                                                checked={marcados.includes(permiso.nombre)}
                                                                onChange={() => alternar(permiso.nombre)}
                                                            />
                                                            <span>
                                                                {permiso.descripcion}
                                                                <span className="block text-xs text-gray-400">{permiso.nombre}</span>
                                                            </span>
                                                        </label>
                                                    ))}
                                                </div>
                                            </fieldset>
                                        ))}
                                    </div>

                                    {!marcados.includes('panel.acceder') && (
                                        <Aviso tono="alerta" className="mt-4">
                                            Sin «Acceder al panel», los usuarios de este rol no podrán iniciar sesión.
                                        </Aviso>
                                    )}

                                    <div className="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
                                        {!seleccionado.protegido ? (
                                            <Button type="button" variant="dangerOutline" onClick={eliminarRol}>Eliminar rol</Button>
                                        ) : (
                                            <span className="text-xs text-gray-400">Rol del sistema: no se puede eliminar.</span>
                                        )}
                                        <Button type="button" onClick={guardarPermisos} loading={guardando} disabled={sinCambios}>
                                            Guardar permisos
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Card>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
