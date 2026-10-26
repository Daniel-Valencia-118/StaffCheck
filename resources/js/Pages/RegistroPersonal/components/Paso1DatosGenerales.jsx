import React, { memo } from 'react';
import Input from '../../../Components/Forms/Input';
import Select from '../../../Components/Forms/Select';
import FileUpload from '../../../Components/Forms/FileUpload';
import MapPicker from '../../../Components/Forms/MapPicker';
import Card from '../../../Components/Forms/Card';

const SEXO_OPTIONS = [
    { value: 'M', label: 'Masculino' },
    { value: 'F', label: 'Femenino' },
];

const ESTADO_CIVIL_OPTIONS = [
    { value: 'SOLTERO/A', label: 'Soltero/a' },
    { value: 'CASADO/A', label: 'Casado/a' },
    { value: 'DIVORCIADO/A', label: 'Divorciado/a' },
    { value: 'VIUDO/A', label: 'Viudo/a' },
    { value: 'UNION LIBRE', label: 'Unión Libre' },
];

const EXPEDICION_OPTIONS = [
    { value: 'LP', label: 'La Paz (LP)' },
    { value: 'CB', label: 'Cochabamba (CB)' },
    { value: 'SC', label: 'Santa Cruz (SC)' },
    { value: 'OR', label: 'Oruro (OR)' },
    { value: 'PT', label: 'Potosí (PT)' },
    { value: 'TJ', label: 'Tarija (TJ)' },
    { value: 'CH', label: 'Chuquisaca (CH)' },
    { value: 'BE', label: 'Beni (BE)' },
    { value: 'PD', label: 'Pando (PD)' },
];

const GENERAL_FIELDS = [
    { name: 'paterno', label: 'Apellido Paterno', placeholder: 'Ingrese su apellido paterno', required: true },
    { name: 'materno', label: 'Apellido Materno', placeholder: 'Ingrese su apellido materno', required: true },
    { name: 'nombres', label: 'Nombres', placeholder: 'Ingrese sus nombres', required: true },
    { name: 'ci', label: 'CI', placeholder: 'Ingrese su número de Carnet de Identidad', required: true },
    { name: 'ci_expedicion', label: 'Expedición', type: 'select', options: EXPEDICION_OPTIONS, required: true },
    { name: 'sexo', label: 'Sexo', type: 'select', options: SEXO_OPTIONS, required: true },
    { name: 'fecha_nacimiento', label: 'Fecha de Nacimiento', type: 'date', required: true },
    { name: 'lugar_nacimiento_provincia', label: 'Provincia de Nacimiento', placeholder: 'Ingrese la provincia', required: true },
    { name: 'lugar_nacimiento_ciudad', label: 'Ciudad de Nacimiento', placeholder: 'Ingrese la ciudad', required: true },
    { name: 'estado_civil', label: 'Estado Civil', type: 'select', options: ESTADO_CIVIL_OPTIONS, required: true },
    { name: 'numero_hijos', label: 'Número de Hijos', type: 'number', min: 0, step: 1 },
    { name: 'email', label: 'Correo electrónico', type: 'email', placeholder: 'Ej: usuario@dominio.com', required: true },
    { name: 'telefono', label: 'Teléfono', placeholder: 'Ingrese su número de teléfono' },
    { name: 'celular', label: 'Celular', placeholder: 'Ingrese su número de celular', required: true },
    { name: 'direccion_actual', label: 'Dirección Actual', placeholder: 'Ej: Av. Principal, Zona Sur, #123', required: true },
];

const Paso1DatosGenerales = memo(({ form, handleChange, handleLocationChange, errors = {}, fotoActual = null, ciBloqueado = false, fotoObligatoria = false }) => {
    return (
        <Card title="Datos Personales y Ubicación">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
                {GENERAL_FIELDS.map((field) => {
                    if (field.type === 'select') {
                        return (
                            <Select
                                key={field.name}
                                label={field.label}
                                name={field.name}
                                value={form[field.name]}
                                onChange={handleChange}
                                options={field.options}
                                error={errors[field.name]}
                                required={field.required}
                            />
                        );
                    }

                    return (
                        <Input
                            key={field.name}
                            label={field.label}
                            name={field.name}
                            type={field.type || 'text'}
                            placeholder={field.placeholder}
                            value={form[field.name]}
                            onChange={handleChange}
                            min={field.min}
                            step={field.step}
                            error={errors[field.name]}
                            required={field.required}
                            disabled={ciBloqueado && field.name === 'ci'}
                            ayuda={ciBloqueado && field.name === 'ci' ? 'El CI no se puede modificar.' : undefined}
                        />
                    );
                })}
            </div>

            <div className="mt-5 border-t border-gray-100 pt-5">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <FileUpload
                        label="Foto de Perfil"
                        name="foto"
                        value={form.foto ?? fotoActual}
                        onChange={handleChange}
                        error={errors.foto}
                        required={fotoObligatoria}
                        ayuda="Formato JPG o PNG, de frente y con buena luz."
                        accept=".png, .jpg, .jpeg, image/png, image/jpeg"
                    />
                </div>
            </div>

            <div className="space-y-2 mt-4">
                <p className="block text-sm font-medium text-gray-700">
                    Ubicación de su domicilio <span className="text-red-500" aria-hidden="true">*</span>
                </p>
                <p className="text-xs text-gray-500">Haga clic en el mapa sobre su domicilio para marcarlo.</p>

                <MapPicker
                    lat={form.latitude}
                    lng={form.longitude}
                    onChange={(coords) => handleLocationChange('latitude', 'longitude', coords)}
                    height="350px"
                />

                {(errors.latitude || errors.longitude || errors.location) && (
                    <p className="text-red-600 text-sm mt-1">
                        {errors.latitude || errors.longitude || errors.location}
                    </p>
                )}
            </div>
        </Card>
    );
});

Paso1DatosGenerales.displayName = 'Paso1DatosGenerales';
export default Paso1DatosGenerales;