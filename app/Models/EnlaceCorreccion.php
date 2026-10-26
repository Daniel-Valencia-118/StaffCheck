<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EnlaceCorreccion extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'enlaces_correccion';

    protected $fillable = ['id_persona', 'token_hash', 'expira_at', 'usado_at', 'id_usuario'];

    protected $casts = [
        'expira_at' => 'datetime',
        'usado_at' => 'datetime',
    ];

    public static function generar(Persona $persona, ?User $usuario, int $dias): string
    {
        $token = Str::random(48);

        static::query()->where('id_persona', $persona->id)->whereNull('usado_at')->delete();

        static::create([
            'id_persona' => $persona->id,
            'token_hash' => static::hash($token),
            'expira_at' => now()->addDays($dias),
            'id_usuario' => $usuario?->id,
        ]);

        return $token;
    }

    public static function vigente(string $token): ?self
    {
        return static::query()
            ->where('token_hash', static::hash($token))
            ->whereNull('usado_at')
            ->where('expira_at', '>', now())
            ->whereHas('persona')
            ->first();
    }

    public static function activoDe(Persona $persona): ?self
    {
        return static::query()
            ->where('id_persona', $persona->id)
            ->whereNull('usado_at')
            ->where('expira_at', '>', now())
            ->latest('id')
            ->first();
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }
}
