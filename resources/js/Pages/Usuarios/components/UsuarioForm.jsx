import React from 'react';
import Input from '../../../Components/Forms/Input';
import Select from '../../../Components/Forms/Select';
import Button from '../../../Components/Forms/Button';

export default function UsuarioForm({ form, roles, onSubmit, conContrasena = false, rolBloqueado = false, textoBoton }) {
    const { data, setData, errors, processing } = form;
    const cambiar = (e) => setData(e.target.name, e.target.value);

    return (
        <form onSubmit={onSubmit} className="space-y-1">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-x-4">
                <Input label="Nombre" name="nombre" value={data.nombre} onChange={cambiar} error={errors.nombre} required />
                <Input label="Apellido Paterno" name="paterno" value={data.paterno} onChange={cambiar} error={errors.paterno} required />
                <Input label="Apellido Materno" name="materno" value={data.materno} onChange={cambiar} error={errors.materno} />
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                <Input label="Correo Electrónico" name="email" type="email" value={data.email} onChange={cambiar} error={errors.email} required />
                <Select
                    label="Rol"
                    name="rol"
                    value={data.rol}
                    onChange={cambiar}
                    options={roles}
                    error={errors.rol}
                    disabled={rolBloqueado}
                    ayuda={rolBloqueado ? 'No puede cambiar su propio rol.' : undefined}
                    required
                />
            </div>

            {conContrasena && (
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                    <Input label="Contraseña" name="password" type="password" value={data.password} onChange={cambiar} error={errors.password} ayuda="Mínimo 8 caracteres, con letras y números." autoComplete="new-password" required />
                    <Input label="Confirmar Contraseña" name="password_confirmation" type="password" value={data.password_confirmation} onChange={cambiar} required />
                </div>
            )}

            <div className="flex justify-end pt-2">
                <Button type="submit" loading={processing}>{textoBoton}</Button>
            </div>
        </form>
    );
}
