import React, { useRef } from 'react';
import Button from '../Forms/Button';

export default function Wizard({
    stepTitles = [],
    paso,
    onCambiarPaso,
    pasosConError = [],
    children,
    onComplete,
    isSubmitting = false,
    puedeFinalizar = true,
    textoFinal = 'Finalizar',
}) {
    const inicio = useRef(null);
    const ultimo = stepTitles.length - 1;

    const irA = (destino) => {
        onCambiarPaso(Math.min(Math.max(destino, 0), ultimo));
        inicio.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    return (
        <div ref={inicio} className="scroll-mt-4">
            <p className="sm:hidden text-sm font-medium text-marca-800 mb-2">
                Paso {paso + 1} de {stepTitles.length}: {stepTitles[paso]}
            </p>
            <ol className="flex items-start gap-2 mb-6">
                {stepTitles.map((title, index) => {
                    const conError = pasosConError.includes(index);
                    const actual = index === paso;
                    const hecho = index < paso;
                    const barra = conError ? 'bg-red-500' : actual || hecho ? 'bg-marca-700' : 'bg-gray-200';
                    const texto = conError ? 'text-red-600' : actual ? 'text-marca-700' : hecho ? 'text-gray-700' : 'text-gray-400';

                    return (
                        <li key={title} className="flex-1 min-w-0">
                            <button
                                type="button"
                                onClick={() => irA(index)}
                                disabled={isSubmitting}
                                aria-current={actual ? 'step' : undefined}
                                className="w-full text-center cursor-pointer disabled:cursor-not-allowed group"
                            >
                                <span className={`block h-2 rounded-full transition-colors ${barra}`} />
                                <span className={`hidden sm:block text-xs mt-1.5 font-medium truncate group-hover:underline ${texto}`}>
                                    {title}{conError && ' •'}
                                </span>
                            </button>
                        </li>
                    );
                })}
            </ol>

            <div className="my-4">
                {typeof children === 'function' ? children(paso) : children}
            </div>

            <div className="flex justify-between items-center mt-6 pt-4 border-t border-gray-100">
                <Button variant="secondary" onClick={() => irA(paso - 1)} disabled={paso === 0 || isSubmitting}>
                    Anterior
                </Button>

                {paso === ultimo ? (
                    <Button onClick={onComplete} loading={isSubmitting} disabled={!puedeFinalizar}>
                        {isSubmitting ? 'Guardando...' : textoFinal}
                    </Button>
                ) : (
                    <Button onClick={() => irA(paso + 1)} disabled={isSubmitting}>
                        Siguiente
                    </Button>
                )}
            </div>
        </div>
    );
}
