import React, { forwardRef } from 'react';
import Campo, { clasesCampo } from './Campo';

const Textarea = forwardRef(({
    label,
    name,
    id,
    value,
    error,
    ayuda,
    required = false,
    rows = 3,
    className = '',
    ...props
}, ref) => {
    const textareaId = id || name;

    return (
        <Campo id={textareaId} label={label} required={required} error={error} ayuda={ayuda}>
            <textarea
                ref={ref}
                id={textareaId}
                name={name}
                value={value ?? ''}
                required={required}
                rows={rows}
                aria-invalid={error ? true : undefined}
                aria-describedby={error ? `${textareaId}-error` : undefined}
                className={clasesCampo(error, className)}
                {...props}
            />
        </Campo>
    );
});

Textarea.displayName = 'Textarea';
export default Textarea;
