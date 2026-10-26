import React, { forwardRef } from 'react';
import Campo, { clasesCampo } from './Campo';

const Input = forwardRef(({
    label,
    name,
    id,
    type = 'text',
    value,
    error,
    ayuda,
    required = false,
    className = '',
    ...props
}, ref) => {
    const inputId = id || name;

    return (
        <Campo id={inputId} label={label} required={required} error={error} ayuda={ayuda}>
            <input
                ref={ref}
                id={inputId}
                name={name}
                type={type}
                value={type === 'file' ? undefined : (value ?? '')}
                required={required}
                aria-invalid={error ? true : undefined}
                aria-describedby={error ? `${inputId}-error` : undefined}
                className={clasesCampo(error, className)}
                {...props}
            />
        </Campo>
    );
});

Input.displayName = 'Input';
export default Input;
