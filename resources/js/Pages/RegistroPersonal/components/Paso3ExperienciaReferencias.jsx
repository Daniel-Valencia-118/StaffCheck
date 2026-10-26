import React, { useMemo, useCallback, memo } from 'react';
import Input from '../../../Components/Forms/Input';
import Select from '../../../Components/Forms/Select';
import Button from '../../../Components/Forms/Button';
import Card from '../../../Components/Forms/Card';
import { PlusIcon, TrashIcon } from '../../../Components/Shared/Icons';
import { generateId, getFieldError } from '../../../utils/formulario';
import { hoyLocal } from '../../../utils/dateUtils';

const ExperienciaItem = memo(function ExperienciaItem({
    item,
    index,
    canDelete,
    onFieldChange,
    removeItem,
    errors = {}
}) {
    const baseName = `experiencias.${index}`;

    return (
        <div className="border-b border-gray-100 pb-5 mb-5 last:border-0 last:pb-0 last:mb-0">
            <div className="flex justify-between items-center mb-3">
                <h4 className="text-xs font-bold uppercase tracking-wider text-gray-400">
                    Experiencia #{index + 1}
                </h4>
                {canDelete && (
                    <Button
                        type="button"
                        variant="danger"
                        size="sm"
                        onClick={() => removeItem('experiencias', index)}
                    >
                        <TrashIcon />
                        Eliminar
                    </Button>
                )}
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
                <Input
                    label="Institución / Empresa"
                    id={`exp-${index}-institucion`}
                    name={`${baseName}.institucion`}
                    placeholder="Ej: Empresa XYZ S.A."
                    value={item.institucion ?? ''}
                    onChange={(e) => onFieldChange('experiencias', index, 'institucion', e.target.value)}
                    error={getFieldError(errors, `${baseName}.institucion`)}
                    required
                />
                <Input
                    label="Cargo"
                    id={`exp-${index}-cargo`}
                    name={`${baseName}.cargo`}
                    placeholder="Ej: Gerente de Ventas"
                    value={item.cargo ?? ''}
                    onChange={(e) => onFieldChange('experiencias', index, 'cargo', e.target.value)}
                    error={getFieldError(errors, `${baseName}.cargo`)}
                    required
                />
                <Input
                    label="Fecha Inicio"
                    type="date"
                    id={`exp-${index}-fecha-inicio`}
                    name={`${baseName}.fecha_inicio`}
                    value={item.fecha_inicio ?? ''}
                    max={hoyLocal()}
                    onChange={(e) => onFieldChange('experiencias', index, 'fecha_inicio', e.target.value)}
                    error={getFieldError(errors, `${baseName}.fecha_inicio`)}
                    required
                />
                <Input
                    label="Fecha Fin"
                    ayuda="Déjela vacía si todavía trabaja allí."
                    type="date"
                    id={`exp-${index}-fecha-fin`}
                    name={`${baseName}.fecha_fin`}
                    value={item.fecha_fin ?? ''}
                    min={item.fecha_inicio || undefined}
                    max={hoyLocal()}
                    onChange={(e) => onFieldChange('experiencias', index, 'fecha_fin', e.target.value)}
                    error={getFieldError(errors, `${baseName}.fecha_fin`)}
                />
            </div>
        </div>
    );
});

const ReferenciaLaboralItem = memo(function ReferenciaLaboralItem({
    item,
    index,
    canDelete,
    institucionesOptions,
    onFieldChange,
    removeItem,
    errors = {}
}) {
    const baseName = `referencias.${index}`;

    const optionsForThisItem = useMemo(() => {
        const selectedValue = (item.institucion || '').trim();
        if (!selectedValue) return institucionesOptions;

        const exists = institucionesOptions.some((opt) => opt.value.toLowerCase() === selectedValue.toLowerCase());
        if (!exists) {
            return [{ value: selectedValue, label: `${selectedValue} (No listada)` }, ...institucionesOptions];
        }
        return institucionesOptions;
    }, [institucionesOptions, item.institucion]);

    return (
        <div className="border-b border-gray-100 pb-5 mb-5 last:border-0 last:pb-0 last:mb-0">
            <div className="flex justify-between items-center mb-3">
                <h4 className="text-xs font-bold uppercase tracking-wider text-gray-400">
                    Referencia #{index + 1}
                </h4>
                {canDelete && (
                    <Button
                        type="button"
                        variant="danger"
                        size="sm"
                        onClick={() => removeItem('referencias', index)}
                    >
                        <TrashIcon />
                        Eliminar
                    </Button>
                )}
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
                <Input
                    label="Nombre del Referente"
                    id={`ref-${index}-nombre`}
                    name={`${baseName}.nombre_referente`}
                    placeholder="Ej: Juan Pérez"
                    value={item.nombre_referente ?? ''}
                    onChange={(e) => onFieldChange('referencias', index, 'nombre_referente', e.target.value)}
                    error={getFieldError(errors, `${baseName}.nombre_referente`)}
                    required
                />
                <Select
                    label="Institución (Experiencia relacionada)"
                    id={`ref-${index}-institucion`}
                    name={`${baseName}.institucion`}
                    placeholder="Seleccione la institución"
                    value={item.institucion ?? ''}
                    onChange={(e) => onFieldChange('referencias', index, 'institucion', e.target.value)}
                    options={optionsForThisItem}
                    error={getFieldError(errors, `${baseName}.institucion`)}
                    required
                />
                <Input
                    label="Teléfono / Celular de Contacto"
                    type="tel"
                    id={`ref-${index}-telefono`}
                    name={`${baseName}.telefono_celular`}
                    placeholder="Ej: 71234567"
                    value={item.telefono_celular ?? ''}
                    onChange={(e) => onFieldChange('referencias', index, 'telefono_celular', e.target.value)}
                    error={getFieldError(errors, `${baseName}.telefono_celular`)}
                    required
                />
            </div>
        </div>
    );
});

export default function Paso3ExperienciaReferencias({
    experiencias = [],
    referencias = [],
    handleNestedChange,
    addItem,
    removeItem,
    errors = {}
}) {
    const institucionesOptions = useMemo(() => {
        const unicos = new Set();
        const opciones = [];

        experiencias.forEach((exp) => {
            const nombre = (exp.institucion || '').trim();
            if (nombre && !unicos.has(nombre.toLowerCase())) {
                unicos.add(nombre.toLowerCase());
                opciones.push({ value: nombre, label: nombre });
            }
        });

        return opciones;
    }, [experiencias]);

    const handleRemoveExperiencia = useCallback((key, index) => {
        removeItem('experiencias', index);
        if (experiencias.length === 1 && referencias.length > 0) {
            Array.from({ length: referencias.length }).forEach(() => removeItem('referencias', 0));
        }
    }, [experiencias.length, referencias.length, removeItem]);

    const handleAddExperiencia = useCallback(() => {
        const esPrimeraExperiencia = experiencias.length === 0;

        addItem('experiencias', {
            temp_id: generateId(),
            institucion: '',
            cargo: '',
            fecha_inicio: '',
            fecha_fin: ''
        });

        if (esPrimeraExperiencia && referencias.length === 0) {
            addItem('referencias', {
                temp_id: generateId(),
                nombre_referente: '',
                institucion: '',
                telefono_celular: ''
            });
        }
    }, [addItem, experiencias.length, referencias.length]);

    const handleAddReferencia = useCallback(() => {
        addItem('referencias', {
            temp_id: generateId(),
            nombre_referente: '',
            institucion: '',
            telefono_celular: ''
        });
    }, [addItem]);

    return (
        <>
            <Card title="Experiencia Laboral">
                {experiencias.length === 0 ? (
                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg mb-4">
                        <p className="text-sm text-gray-500">
                            No registra experiencia laboral previa.
                        </p>
                    </div>
                ) : (
                    experiencias.map((item, index) => (
                        <ExperienciaItem
                            key={item.temp_id || item.id || `exp-${index}`}
                            item={item}
                            index={index}
                            canDelete={experiencias.length > 0}
                            onFieldChange={handleNestedChange}
                            removeItem={handleRemoveExperiencia}
                            errors={errors}
                        />
                    ))
                )}
                <div className="mt-4">
                    <Button type="button" variant="agregar" size="sm" onClick={handleAddExperiencia}>
                        <PlusIcon />Agregar Experiencia
                    </Button>
                </div>
            </Card>

            <Card title="Referencias Laborales">
                <p className="text-xs text-gray-500 mb-4">
                    Seleccione la institución de la experiencia laboral relacionada previamente.
                </p>
                {referencias.length === 0 ? (
                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg mb-4">
                        <p className="text-sm text-gray-500">
                            No registra referencias laborales.
                        </p>
                    </div>
                ) : (
                    referencias.map((item, index) => (
                        <ReferenciaLaboralItem
                            key={item.temp_id || item.id || `ref-${index}`}
                            item={item}
                            index={index}
                            canDelete={referencias.length > 1}
                            institucionesOptions={institucionesOptions}
                            onFieldChange={handleNestedChange}
                            removeItem={removeItem}
                            errors={errors}
                        />
                    ))
                )}
                {experiencias.length > 0 ? (
                    <div className="mt-4">
                        <Button type="button" variant="agregar" size="sm" onClick={handleAddReferencia}>
                            <PlusIcon />Agregar Referencia
                        </Button>
                    </div>
                ) : (
                    <p className="text-xs text-gray-500">Agregue primero una experiencia laboral.</p>
                )}
            </Card>
        </>
    );
}