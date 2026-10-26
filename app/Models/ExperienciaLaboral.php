<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienciaLaboral extends Model
{
    use HasFactory;

    protected $table = 'experiencias_laborales';

    protected $primaryKey = 'id_exp_lab';

    public $incrementing = true;

    protected $fillable = [
        'institucion', 'cargo', 'fecha_inicio', 'fecha_fin', 'id_persona',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    protected static function booted(): void
    {
        static::saving(function (ExperienciaLaboral $experiencia) {
            $camposString = ['institucion', 'cargo'];

            foreach ($camposString as $campo) {
                if (! empty($experiencia->$campo)) {
                    $experiencia->$campo = mb_strtolower(trim($experiencia->$campo), 'UTF-8');
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
