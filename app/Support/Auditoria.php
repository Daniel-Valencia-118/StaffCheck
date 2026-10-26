<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Throwable;

class Auditoria
{
    public const MODULOS = [
        'auth' => 'Sesiones',
        'personal' => 'Personal',
        'usuarios' => 'Usuarios',
        'roles' => 'Roles y permisos',
        'configuracion' => 'Configuración',
        'sistema' => 'Sistema',
    ];

    public static function registrar(string $modulo, string $evento, string $descripcion, ?Model $sujeto = null, array $datos = []): void
    {
        try {
            $actividad = activity($modulo)
                ->event($evento)
                ->withProperties(array_merge($datos, ['ip' => request()->ip()]));

            if ($sujeto) {
                $actividad->performedOn($sujeto);
            }

            if ($usuario = auth()->user()) {
                $actividad->causedBy($usuario);
            }

            $actividad->log($descripcion);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
