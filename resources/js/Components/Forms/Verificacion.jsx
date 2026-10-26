import React from 'react';
import { Turnstile } from '@marsidev/react-turnstile';

const SITE_KEY = import.meta.env.VITE_CLOUDFLARE_TURNSTILE_SITE_KEY || '';

export default function Verificacion({ onToken, error, reinicio = 0 }) {
    return (
        <div className="flex flex-col items-center justify-center my-4 min-h-17.5">
            {SITE_KEY ? (
                <Turnstile
                    key={reinicio}
                    siteKey={SITE_KEY}
                    onSuccess={(token) => onToken(token)}
                    onExpire={() => onToken('')}
                    onError={() => onToken('')}
                    options={{ size: 'normal', theme: 'light' }}
                />
            ) : (
                <p className="text-red-600 text-xs text-center p-2 border border-red-200 rounded-lg bg-red-50">
                    Falta configurar la verificación de seguridad (VITE_CLOUDFLARE_TURNSTILE_SITE_KEY).
                </p>
            )}
            {error && <p className="text-xs text-red-600 mt-2 font-medium text-center">{error}</p>}
        </div>
    );
}
