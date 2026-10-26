<?php

namespace App\Models;

use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles {
        hasPermissionTo as protected hasPermissionToSpatie;
    }

    protected $fillable = [
        'nombre',
        'paterno',
        'materno',
        'email',
        'password',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'rol' => 'string',
        'estado' => 'boolean',
        'pendiente' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            $camposString = ['nombre', 'paterno', 'materno', 'email', 'rol'];

            foreach ($camposString as $campo) {
                if (! empty($user->$campo)) {
                    $user->$campo = mb_strtolower(trim($user->$campo), 'UTF-8');
                }
            }

            $user->materno ??= '';
        });
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->paterno} {$this->materno}");
    }

    public function isActivo(): bool
    {
        return $this->estado === true;
    }

    public function asignarRol(string $rol): void
    {
        $rol = mb_strtolower(trim($rol), 'UTF-8');

        $this->syncRoles([Role::findOrCreate($rol, 'web')]);
        $this->rol = $rol;
        $this->save();
    }

    public function esAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        $nombre = $permission instanceof Permission ? $permission->name : $permission;

        if (in_array($nombre, RolesPermisosSeeder::PERMISOS_SOLO_ADMIN, true) && ! $this->esAdmin()) {
            return false;
        }

        return $this->hasPermissionToSpatie($permission, $guardName);
    }

    public function puedeOtorgar(iterable $permisos): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        return collect($permisos)->diff($this->getAllPermissions()->pluck('name'))->isEmpty();
    }

    public function puedeAsignarRol(Role $rol): bool
    {
        if ($this->esAdmin()) {
            return true;
        }

        return $rol->name !== 'admin' && $this->puedeOtorgar($rol->permissions->pluck('name'));
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id');
    }

    public function getDashboardRoute(): string
    {
        return match (true) {
            $this->can('personal.ver') => route('personal.index'),
            $this->can('mi_ficha.editar') => route('mi_ficha.edit'),
            $this->can('panel.acceder') => route('cuenta'),
            default => route('registro.personal'),
        };
    }
}
