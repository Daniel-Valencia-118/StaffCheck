<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuracion extends Model
{
    public const CACHE = 'configuraciones';

    public const DEFINICIONES = [
        'institucion_nombre' => [
            'tipo' => 'texto',
            'defecto' => 'Hogar de Niños Alalay',
            'reglas' => ['required', 'string', 'max:150'],
        ],
        'registro_abierto' => [
            'tipo' => 'booleano',
            'defecto' => true,
            'reglas' => ['required', 'boolean'],
        ],
        'registro_mensaje_cerrado' => [
            'tipo' => 'texto',
            'defecto' => 'El registro de personal está cerrado temporalmente. Consulte con Recursos Humanos.',
            'reglas' => ['nullable', 'string', 'max:300'],
        ],
        'registro_cuentas_abierto' => [
            'tipo' => 'booleano',
            'defecto' => false,
            'reglas' => ['required', 'boolean'],
        ],
        'enlace_correccion_dias' => [
            'tipo' => 'numero',
            'defecto' => 7,
            'reglas' => ['required', 'integer', 'min:1', 'max:30'],
        ],
        'fichas_estado_inicial' => [
            'tipo' => 'opcion',
            'opciones' => ['aprobado', 'pendiente'],
            'defecto' => 'aprobado',
            'reglas' => ['required', 'in:aprobado,pendiente'],
        ],
        'registro_max_por_hora' => [
            'tipo' => 'numero',
            'defecto' => 30,
            'reglas' => ['required', 'integer', 'min:5', 'max:500'],
        ],
        'csp_aplicar' => [
            'tipo' => 'booleano',
            'defecto' => false,
            'reglas' => ['required', 'boolean'],
        ],
        'foto_max_kb' => [
            'tipo' => 'numero',
            'defecto' => 2048,
            'reglas' => ['required', 'integer', 'min:256', 'max:5120'],
        ],
    ];

    protected $table = 'configuraciones';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor'];

    public static function valor(string $clave): mixed
    {
        return static::todas()[$clave] ?? null;
    }

    public static function todas(): array
    {
        $guardadas = Cache::rememberForever(self::CACHE, fn () => static::query()->pluck('valor', 'clave')->all());

        $valores = [];
        foreach (self::DEFINICIONES as $clave => $definicion) {
            $valores[$clave] = array_key_exists($clave, $guardadas)
                ? static::convertir($guardadas[$clave], $definicion['tipo'])
                : $definicion['defecto'];
        }

        return $valores;
    }

    public static function guardar(array $nuevos): array
    {
        $actuales = static::todas();
        $cambios = [];

        foreach (array_intersect_key($nuevos, self::DEFINICIONES) as $clave => $valor) {
            $valor = static::convertir($valor, self::DEFINICIONES[$clave]['tipo']);

            if ($valor !== $actuales[$clave]) {
                static::updateOrCreate(['clave' => $clave], ['valor' => is_bool($valor) ? (int) $valor : $valor]);
                $cambios[$clave] = ['antes' => $actuales[$clave], 'despues' => $valor];
            }
        }

        Cache::forget(self::CACHE);

        return $cambios;
    }

    private static function convertir(mixed $valor, string $tipo): mixed
    {
        return match ($tipo) {
            'booleano' => filter_var($valor, FILTER_VALIDATE_BOOLEAN),
            'numero' => (int) $valor,
            default => $valor === null ? null : (string) $valor,
        };
    }
}
