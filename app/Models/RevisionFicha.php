<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionFicha extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'revisiones_fichas';

    protected $fillable = ['id_persona', 'id_usuario', 'estado_anterior', 'estado_nuevo', 'motivo'];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id')->withTrashed();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
