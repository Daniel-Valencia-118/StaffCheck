<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class Busqueda
{
    private const ESCAPE = '!';

    public static function filtrar(Builder $consulta, array $columnas, ?string $texto): Builder
    {
        $texto = trim((string) $texto);
        if ($texto === '') {
            return $consulta;
        }

        $patron = '%'.strtr($texto, [self::ESCAPE => self::ESCAPE.self::ESCAPE, '%' => self::ESCAPE.'%', '_' => self::ESCAPE.'_']).'%';

        return $consulta->where(function (Builder $grupo) use ($columnas, $patron) {
            foreach ($columnas as $columna) {
                $grupo->orWhereRaw($grupo->getQuery()->getGrammar()->wrap($columna)." LIKE ? ESCAPE '".self::ESCAPE."'", [$patron]);
            }
        });
    }
}
