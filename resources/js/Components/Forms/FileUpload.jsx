import React, { useEffect, useRef, useState } from 'react';
import Campo from './Campo';

export default function FileUpload({
    label,
    name,
    value,
    onChange,
    error,
    ayuda,
    accept = 'image/*',
    required = false,
    className = '',
    ...props
}) {
    const fileInputRef = useRef(null);
    const [vistaLocal, setVistaLocal] = useState(null);

    useEffect(() => {
        if (!(value instanceof File)) {
            setVistaLocal(null);
            return undefined;
        }

        const url = URL.createObjectURL(value);
        setVistaLocal(url);

        return () => URL.revokeObjectURL(url);
    }, [value]);

    const preview = typeof value === 'string' ? value : vistaLocal;

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file && onChange) {
            onChange({ target: { name, value: file } });
        }
    };

    return (
        <Campo id={name} label={label} required={required} error={error} ayuda={ayuda} className={`mb-4 ${className}`}>
            <div className="flex items-center gap-4">
                <div className="w-24 h-24 rounded-full overflow-hidden border border-gray-200 shadow-sm bg-gray-50 flex items-center justify-center shrink-0">
                    {preview
                        ? <img src={preview} alt="Vista previa de la foto" className="w-full h-full object-cover" />
                        : <span className="text-xs text-gray-400">Sin foto</span>}
                </div>
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="px-4 py-2 bg-marca-50 text-marca-700 text-sm font-medium rounded-lg hover:bg-marca-100 transition-colors cursor-pointer"
                >
                    {preview ? 'Cambiar foto' : 'Subir foto'}
                </button>
                <input
                    ref={fileInputRef}
                    id={name}
                    type="file"
                    name={name}
                    accept={accept}
                    onChange={handleFileChange}
                    className="hidden"
                    {...props}
                />
            </div>
        </Campo>
    );
}
