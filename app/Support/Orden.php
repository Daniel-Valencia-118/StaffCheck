<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class Orden
{
    public static function aplicar(Builder $query, ?string $campo, ?string $direccion, array $columnas, string $porDefecto, string $direccionPorDefecto = 'desc'): array
    {
        $campo = in_array($campo, $columnas, true) ? $campo : null;
        $direccion = $direccion === 'asc' ? 'asc' : 'desc';

        if ($campo) {
            $query->orderBy($campo, $direccion);
        }

        $query->orderBy($porDefecto, $direccionPorDefecto)->orderBy($query->getModel()->getQualifiedKeyName(), 'desc');

        return $campo ? ['orden' => $campo, 'dir' => $direccion] : [];
    }
}
