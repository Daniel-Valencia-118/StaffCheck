import React, { useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import Button from '../../../Components/Forms/Button';
import Card from '../../../Components/Forms/Card';
import Aviso from '../../../Components/Shared/Aviso';

export default function EnlaceCorreccionCard({ persona, enlaceVence, cuentaVinculada }) {
    const { flash } = usePage().props;
    const [copiado, setCopiado] = useState(false);
    const [procesando, setProcesando] = useState(false);

    const opciones = {
        preserveScroll: true,
        onStart: () => setProcesando(true),
        onFinish: () => setProcesando(false),
    };

    const generar = () => {
        const aviso = enlaceVence
            ? 'Ya hay un enlace activo. Si genera uno nuevo, el anterior dejará de funcionar. ¿Continuar?'
            : '¿Generar un enlace para que la persona corrija su ficha?';

        if (confirm(aviso)) {
            setCopiado(false);
            router.post(route('personal.enlace', persona.id), {}, opciones);
        }
    };

    const revocar = () => {
        if (confirm('¿Revocar el enlace activo? La persona ya no podrá usarlo.')) {
            router.delete(route('personal.enlace.revocar', persona.id), opciones);
        }
    };

    const copiar = async () => {
        try {
            await navigator.clipboard.writeText(flash.enlace);
            setCopiado(true);
        } catch {
            setCopiado(false);
        }
    };

    return (
        <Card title="Corrección por la persona" className="">
            <p className="text-sm text-gray-600 mb-3">
                {cuentaVinculada
                    ? <>Tiene una cuenta vinculada (<span className="font-medium">{cuentaVinculada}</span>) y puede corregir su ficha desde «Mi ficha».</>
                    : 'No tiene cuenta vinculada. Puede enviarle un enlace de un solo uso para que corrija su ficha sin registrarse.'}
            </p>

            {flash?.enlace && (
                <Aviso tono="info">
                    <p className="mb-2 font-medium">Copie el enlace ahora: por seguridad no se volverá a mostrar.</p>
                    <div className="flex flex-col sm:flex-row gap-2">
                        <input
                            readOnly
                            value={flash.enlace}
                            aria-label="Enlace de corrección"
                            className="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-white text-xs text-gray-800"
                            onFocus={(e) => e.target.select()}
                        />
                        <Button variant={copiado ? 'success' : 'primary'} onClick={copiar}>{copiado ? 'Copiado' : 'Copiar'}</Button>
                    </div>
                </Aviso>
            )}

            <p className="text-sm text-gray-500 mb-3">
                {enlaceVence ? <>Enlace activo hasta <span className="font-medium text-gray-700">{enlaceVence}</span>.</> : 'Sin enlace activo.'}
            </p>
            <div className="flex flex-wrap items-center gap-2">
                <Button variant={enlaceVence ? 'secondary' : 'primary'} onClick={generar} disabled={procesando}>
                    {enlaceVence ? 'Generar nuevo enlace' : 'Generar enlace'}
                </Button>
                {enlaceVence && (
                    <Button variant="dangerOutline" onClick={revocar} disabled={procesando}>Revocar</Button>
                )}
            </div>
        </Card>
    );
}
