<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use App\Models\User;
use Database\Seeders\RolesPermisosSeeder;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\PermissionRegistrar;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'institucion' => fn () => Configuracion::valor('institucion_nombre'),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'enlace' => $request->session()->get('enlace'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'paterno' => $user->paterno,
                    'materno' => $user->materno,
                    'rol' => $user->rol,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permisos' => $this->permisos($user),
                ] : null,
            ],
        ]);
    }

    private function permisos(User $user): array
    {
        if ($user->hasRole('admin')) {
            return app(PermissionRegistrar::class)->getPermissions()->pluck('name')->all();
        }

        return $user->getAllPermissions()->pluck('name')->diff(RolesPermisosSeeder::PERMISOS_SOLO_ADMIN)->values()->all();
    }
}
