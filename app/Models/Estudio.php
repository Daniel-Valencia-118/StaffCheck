<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estudio extends Model
{
    use HasFactory;

    protected $table = 'estudios';

    protected $primaryKey = 'id_estudio';

    public $incrementing = true;

    protected $fillable = [
        'tipo', 'titulo_obtenido', 'institucion', 'anio', 'id_persona',
    ];

    protected $casts = [
        'anio' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Estudio $estudio) {
            $camposString = ['tipo', 'titulo_obtenido', 'institucion'];

            foreach ($camposString as $campo) {
                if (! empty($estudio->$campo)) {
                    $estudio->$campo = mb_strtolower(trim($estudio->$campo), 'UTF-8');
                }
            }
        });
    }

    // Relaciones
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
