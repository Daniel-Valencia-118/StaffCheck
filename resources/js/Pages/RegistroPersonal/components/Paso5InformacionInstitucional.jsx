import React, { memo } from 'react';
import Input from '../../../Components/Forms/Input';
import Select from '../../../Components/Forms/Select';
import Card from '../../../Components/Forms/Card';
import Verificacion from '../../../Components/Forms/Verificacion';
import { hoyLocal } from '../../../utils/dateUtils';

const SEDES_OPTIONS = [
    { value: 'Oficina Central La Paz', label: 'Sede Central La Paz' },
    { value: 'Oficina Santa Cruz', label: 'Sede Santa Cruz' },
];

const Paso5InformacionInstitucional = memo(({ form = {}, handleChange, errors = {}, conTurnstile = true, reinicioVerificacion = 0 }) => (
    <Card title="Información Institucional">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-1">
            <Input
                id="inst-fecha_ingreso_fundacion"
                label="Fecha Ingreso Fundación"
                name="fecha_ingreso_fundacion"
                type="date"
                value={form.fecha_ingreso_fundacion}
                onChange={handleChange}
                error={errors.fecha_ingreso_fundacion}
                max={hoyLocal()}
                required
            />
            <Input
                id="inst-cargo_actual"
                label="Cargo Actual"
                name="cargo_actual"
                placeholder="Ingrese su cargo actual en la fundación"
                value={form.cargo_actual}
                onChange={handleChange}
                error={errors.cargo_actual}
                maxLength={100}
                required
            />
            <Select
                label="Oficina Actual"
                name="oficina_actual"
                value={form.oficina_actual}
                onChange={handleChange}
                options={SEDES_OPTIONS}
                error={errors.oficina_actual}
                required
            />
        </div>

        {conTurnstile && (
            <Verificacion
                reinicio={reinicioVerificacion}
                onToken={(token) => handleChange('cf_turnstile_response', token)}
                error={errors.cf_turnstile_response}
            />
        )}
    </Card>
));

Paso5InformacionInstitucional.displayName = 'Paso5InformacionInstitucional';
export default Paso5InformacionInstitucional;
