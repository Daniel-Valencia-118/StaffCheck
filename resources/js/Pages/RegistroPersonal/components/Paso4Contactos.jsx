import React, { useMemo, useCallback, memo } from 'react';
import Input from '../../../Components/Forms/Input';
import Select from '../../../Components/Forms/Select';
import Button from '../../../Components/Forms/Button';
import Card from '../../../Components/Forms/Card';
import { PlusIcon, TrashIcon } from '../../../Components/Shared/Icons';
import { generateId, getFieldError } from '../../../utils/formulario';

const ContactoItem = memo(function ContactoItem({
    item,
    globalIndex,
    displayIndex,
    titlePrefix,
    canDelete,
    handleNestedChange,
    removeItem,
    familiaresList = [],
    familiaresOptions = [],
    errors = {}
}) {
    const handleChange = (field, value) => {
        handleNestedChange('contactos', globalIndex, field, value);
    };

    const baseName = `contactos.${globalIndex}`;

    const handleSelectFamiliarToCopy = (e) => {
        const selectedId = e.target.value;
        if (!selectedId) return;

        const familiarEncontrado = familiaresList.find(
            (f) => String(f.temp_id || f.id || f.id_familiar) === String(selectedId)
        );

        if (familiarEncontrado) {
            const camposACopiar = [
                'nombre',
                'paterno',
                'materno',
                'parentesco_relacion',
                'edad',
                'telefono_celular'
            ];

            camposACopiar.forEach((field) => {
                handleNestedChange('contactos', globalIndex, field, familiarEncontrado[field] ?? '');
            });
        }
    };

    return (
        <div className="border-b border-gray-100 pb-5 mb-5 last:border-0 last:pb-0 last:mb-0">
            <div className="flex justify-between items-center mb-3">
                <h4 className="text-xs font-bold uppercase tracking-wider text-gray-400">
                    {titlePrefix} #{displayIndex + 1}
                </h4>
                {canDelete && (
                    <Button
                        type="button"
                        variant="danger"
                        size="sm"
                        onClick={() => removeItem('contactos', globalIndex)}
                    >
                        <TrashIcon />
                        Eliminar
                    </Button>
                )}
            </div>

            {/* {!item.es_familiar && (
                <div className="mb-4 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    <Select
                        label="Copiar datos desde un Familiar registrado (Opcional)"
                        id={`contacto-${globalIndex}-copiar-familiar`}
                        name={`contacto_${globalIndex}_copiar_familiar`}
                        placeholder={
                            familiaresOptions.length > 0
                                ? "-- Seleccione un familiar para autorrellenar --"
                                : "No hay familiares con datos suficientes aún"
                        }
                        options={familiaresOptions}
                        disabled={familiaresOptions.length === 0}
                        onChange={handleSelectFamiliarToCopy}
                        className="text-xs"
                    />
                    <p className="text-[11px] text-gray-500 mt-1">
                        Si selecciona un familiar, sus datos se copiarán automáticamente a los campos inferiores. Puede editarlos libremente.
                    </p>
                </div>
            )} */}

            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
                <Input
                    label="Nombre"
                    id={`contacto-${globalIndex}-nombre`}
                    name={`${baseName}.nombre`}
                    placeholder="Ingrese el nombre"
                    value={item.nombre ?? ''}
                    maxLength={100}
                    onChange={(e) => handleChange('nombre', e.target.value)}
                    error={getFieldError(errors, `${baseName}.nombre`)}
                    required
                />
                <Input
                    label="Apellido Paterno"
                    id={`contacto-${globalIndex}-paterno`}
                    name={`${baseName}.paterno`}
                    placeholder="Ingrese el apellido paterno"
                    value={item.paterno ?? ''}
                    maxLength={100}
                    onChange={(e) => handleChange('paterno', e.target.value)}
                    error={getFieldError(errors, `${baseName}.paterno`)}
                    required
                />
                <Input
                    label="Apellido Materno"
                    id={`contacto-${globalIndex}-materno`}
                    name={`${baseName}.materno`}
                    placeholder="Ingrese el apellido materno"
                    value={item.materno ?? ''}
                    maxLength={100}
                    onChange={(e) => handleChange('materno', e.target.value)}
                    error={getFieldError(errors, `${baseName}.materno`)}
                    required
                />
                <Input
                    label={item.es_familiar ? "Parentesco" : "Relación / Vínculo"}
                    id={`contacto-${globalIndex}-parentesco`}
                    name={`${baseName}.parentesco_relacion`}
                    placeholder={item.es_familiar ? "Ej: Hermano, Tío, Primo" : "Ej: Amigo, Tío, Compañero"}
                    value={item.parentesco_relacion ?? ''}
                    maxLength={50}
                    onChange={(e) => handleChange('parentesco_relacion', e.target.value)}
                    error={getFieldError(errors, `${baseName}.parentesco_relacion`)}
                    required
                />
                <Input
                    label="Edad (Opcional)"
                    type="number"
                    id={`contacto-${globalIndex}-edad`}
                    name={`${baseName}.edad`}
                    placeholder="Ej: 30"
                    value={item.edad ?? ''}
                    min="0"
                    step="1"
                    onChange={(e) => handleChange('edad', e.target.value)}
                    error={getFieldError(errors, `${baseName}.edad`)}
                />
                <Input
                    label="Teléfono / Celular"
                    type="tel"
                    id={`contacto-${globalIndex}-telefono`}
                    name={`${baseName}.telefono_celular`}
                    placeholder="Ej: 71234567"
                    value={item.telefono_celular ?? ''}
                    maxLength={20}
                    onChange={(e) => handleChange('telefono_celular', e.target.value)}
                    error={getFieldError(errors, `${baseName}.telefono_celular`)}
                    required
                />
            </div>
        </div>
    );
});

export default function Paso4Contactos({
    contactos = [],
    handleNestedChange,
    addItem,
    removeItem,
    errors = {}
}) {
    const { familiares, referencias, familiaresRawList } = useMemo(() => {
        const fam = [];
        const ref = [];
        const famRaw = [];

        contactos.forEach((item, globalIndex) => {
            const entry = { item, globalIndex };
            if (item.es_familiar) {
                fam.push(entry);
                famRaw.push(item);
            } else {
                ref.push(entry);
            }
        });

        return { familiares: fam, referencias: ref, familiaresRawList: famRaw };
    }, [contactos]);

    const familiaresOptions = useMemo(() => {
        return familiaresRawList
            .filter((f) => (f.nombre || '').trim() !== '' || (f.paterno || '').trim() !== '')
            .map((f) => {
                const id = f.temp_id || f.id || f.id_familiar;
                const nombreCompleto = [f.nombre, f.paterno, f.materno].filter(Boolean).join(' ');
                const parentesco = f.parentesco_relacion ? ` (${f.parentesco_relacion})` : '';
                return {
                    value: String(id),
                    label: `${nombreCompleto}${parentesco}`.trim()
                };
            });
    }, [familiaresRawList]);

    const handleAddContacto = useCallback((esFamiliar) => {
        addItem('contactos', {
            temp_id: generateId(),
            nombre: '',
            paterno: '',
            materno: '',
            parentesco_relacion: '',
            edad: '',
            telefono_celular: '',
            es_familiar: Boolean(esFamiliar)
        });
    }, [addItem]);

    return (
        <div className="space-y-6">
            <Card title="Información Familiar (Mínimo 1 requerida)">
                {familiares.length === 0 ? (
                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg mb-4">
                        <p className="text-sm text-gray-500">No ha agregado datos familiares.</p>
                    </div>
                ) : (
                    familiares.map(({ item, globalIndex }, idx) => (
                        <ContactoItem
                            key={item.temp_id || item.id || item.id_familiar || `fam-${globalIndex}`}
                            item={item}
                            globalIndex={globalIndex}
                            displayIndex={idx}
                            titlePrefix="Familiar"
                            canDelete={familiares.length > 1}
                            handleNestedChange={handleNestedChange}
                            removeItem={removeItem}
                            errors={errors}
                        />
                    ))
                )}

                <div className="mt-4">
                    <Button
                        type="button"
                        variant="agregar"
                        size="sm"
                        onClick={() => handleAddContacto(true)}
                    >
                        <PlusIcon />Agregar Familiar
                    </Button>
                </div>
            </Card>

            <Card title="Referencias Personales (Mínimo 1 requerida)">
                <p className="text-xs text-gray-400 mb-4">
                    Registre personas que no sean familiares directos: amigos, vecinos o compañeros que puedan dar referencias suyas.
                </p>

                {referencias.length === 0 ? (
                    <div className="text-center py-6 border-2 border-dashed border-gray-200 rounded-lg mb-4">
                        <p className="text-sm text-gray-500">No ha agregado referencias personales.</p>
                    </div>
                ) : (
                    referencias.map(({ item, globalIndex }, idx) => (
                        <ContactoItem
                            key={item.temp_id || item.id || item.id_familiar || `ref-${globalIndex}`}
                            item={item}
                            globalIndex={globalIndex}
                            displayIndex={idx}
                            titlePrefix="Referencia Personal"
                            canDelete={referencias.length > 1}
                            handleNestedChange={handleNestedChange}
                            removeItem={removeItem}
                            familiaresList={familiaresRawList}
                            familiaresOptions={familiaresOptions}
                            errors={errors}
                        />
                    ))
                )}

                <div className="mt-4">
                    <Button
                        type="button"
                        variant="agregar"
                        size="sm"
                        onClick={() => handleAddContacto(false)}
                    >
                        <PlusIcon />Agregar Referencia Personal
                    </Button>
                </div>
            </Card>
        </div>
    );
}