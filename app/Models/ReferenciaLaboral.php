<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenciaLaboral extends Model
{
    use HasFactory;

    protected $table = 'referencias_laborales';

    protected $primaryKey = 'id_ref_lab';

    public $incrementing = true;

    protected $fillable = [
        'nombre_referente',
        'institucion',
        'telefono_celular',
        'id_persona',
    ];

    protected static function booted(): void
    {
        static::saving(function (ReferenciaLaboral $referencia) {
            $camposString = ['nombre_referente', 'institucion'];

            foreach ($camposString as $campo) {
                if (! empty($referencia->$campo)) {
                    $referencia->$campo = mb_strtolower(trim($referencia->$campo), 'UTF-8');
                }
            }

            if (! empty($referencia->telefono_celular)) {
                $referencia->telefono_celular = trim($referencia->telefono_celular);
            }
        });
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
