<?php

namespace App\Models;

use App\Support\FotosPersonal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Persona extends Model
{
    use HasFactory, SoftDeletes;

    public const ESTADOS = ['pendiente', 'aprobado', 'observado', 'rechazado'];

    public const ESTADOS_CON_MOTIVO = ['observado', 'rechazado'];

    public const COLUMNAS_BUSQUEDA = ['nombres', 'paterno', 'materno', 'ci', 'email'];

    public const OFICINA_LA_PAZ = 'oficina central la paz';

    public const OFICINA_SANTA_CRUZ = 'oficina santa cruz';

    public const COLUMNAS_ORDEN = ['paterno', 'materno', 'nombres', 'ci', 'email', 'celular', 'oficina_actual', 'cargo_actual', 'estado'];

    public const CACHE_RESUMEN = 'personal.resumen';

    public const SEGUNDOS_RESUMEN = 600;

    protected $table = 'personas';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'paterno', 'materno', 'nombres', 'ci', 'ci_expedicion', 'sexo',
        'fecha_nacimiento', 'lugar_nacimiento_provincia', 'lugar_nacimiento_ciudad',
        'estado_civil', 'numero_hijos', 'email', 'telefono', 'celular',
        'direccion_actual', 'fecha_ingreso_fundacion', 'cargo_actual',
        'oficina_actual', 'longitude', 'latitude', 'url_croquis', 'url_foto', 'estado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso_fundacion' => 'date',
        'numero_hijos' => 'integer',
        'longitude' => 'float',
        'latitude' => 'float',
    ];

    protected static function booted(): void
    {
        static::saving(function (Persona $persona) {
            $camposString = [
                'paterno', 'materno', 'nombres', 'lugar_nacimiento_provincia',
                'lugar_nacimiento_ciudad', 'estado_civil', 'direccion_actual',
                'cargo_actual', 'oficina_actual', 'estado', 'sexo', 'email',
            ];

            foreach ($camposString as $campo) {
                if (! empty($persona->$campo)) {
                    $persona->$campo = mb_strtolower(trim($persona->$campo), 'UTF-8');
                }
            }

            if (! empty($persona->ci_expedicion)) {
                $persona->ci_expedicion = mb_strtoupper(trim($persona->ci_expedicion), 'UTF-8');
            }
        });

        $olvidarResumen = fn () => Cache::forget(self::CACHE_RESUMEN);
        static::saved($olvidarResumen);
        static::deleted($olvidarResumen);
        static::restored($olvidarResumen);
    }

    protected function urlFoto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value && $this->id ? FotosPersonal::url($this->id, $value) : null,
        );
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->paterno} {$this->materno}");
    }

    public function experienciasLaborales(): HasMany
    {
        return $this->hasMany(ExperienciaLaboral::class, 'id_persona', 'id');
    }

    public function referenciasLaborales(): HasMany
    {
        return $this->hasMany(ReferenciaLaboral::class, 'id_persona', 'id');
    }

    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class, 'id_persona', 'id');
    }

    public function estudios(): HasMany
    {
        return $this->hasMany(Estudio::class, 'id_persona', 'id');
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class, 'id_persona', 'id');
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(RevisionFicha::class, 'id_persona', 'id');
    }

    public function cambiarEstado(string $nuevoEstado, ?string $motivo, ?User $usuario): RevisionFicha
    {
        if (! in_array($nuevoEstado, self::ESTADOS, true)) {
            throw new InvalidArgumentException('Estado no válido');
        }

        return DB::transaction(function () use ($nuevoEstado, $motivo, $usuario) {
            $anterior = $this->estado;
            $this->estado = $nuevoEstado;
            $this->save();

            return $this->revisiones()->create([
                'id_usuario' => $usuario?->id,
                'estado_anterior' => $anterior,
                'estado_nuevo' => $nuevoEstado,
                'motivo' => $motivo,
            ]);
        });
    }
}
