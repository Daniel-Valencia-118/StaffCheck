import React, { forwardRef } from 'react';
import Campo, { clasesCampo } from './Campo';

const Select = forwardRef(({
    label,
    name,
    id,
    value,
    options = [],
    error,
    ayuda,
    required = false,
    placeholder = 'Seleccione...',
    className = '',
    ...props
}, ref) => {
    const selectId = id || name;
    const valor = value ?? '';
    const desconocido = valor !== '' && !options.some((option) => String(option.value) === String(valor));

    return (
        <Campo id={selectId} label={label} required={required} error={error} ayuda={ayuda}>
            <select
                ref={ref}
                id={selectId}
                name={name}
                value={valor}
                required={required}
                aria-invalid={error ? true : undefined}
                aria-describedby={error ? `${selectId}-error` : undefined}
                className={clasesCampo(error, className)}
                {...props}
            >
                <option value="" disabled={required}>{placeholder}</option>
                {desconocido && <option value={valor}>{valor}</option>}
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </Campo>
    );
});

Select.displayName = 'Select';
export default Select;
