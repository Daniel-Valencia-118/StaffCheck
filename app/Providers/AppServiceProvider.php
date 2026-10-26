<?php

namespace App\Providers;

use App\Models\Configuracion;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);

        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email'), 'UTF-8');
            $respuesta = function () {
                throw ValidationException::withMessages([
                    'email' => 'Demasiados intentos de inicio de sesión. Espere un minuto e intente nuevamente.',
                ]);
            };

            return [
                Limit::perMinute(5)->by('login:'.$email.'|'.$request->ip())->response($respuesta),
                Limit::perMinute(20)->by('login-ip:'.$request->ip())->response($respuesta),
            ];
        });

        RateLimiter::for('registro', function (Request $request) {
            $respuesta = fn (string $mensaje) => function () use ($mensaje) {
                throw ValidationException::withMessages(['general' => $mensaje]);
            };

            return [
                Limit::perMinute(20)->by('registro:'.$request->ip())
                    ->response($respuesta('Demasiados envíos desde esta red. Espere un minuto e intente nuevamente.')),
                Limit::perHour((int) Configuracion::valor('registro_max_por_hora'))->by('registro-hora:'.$request->ip())
                    ->response($respuesta('Se alcanzó el límite de envíos por hora desde esta red. Intente más tarde o comuníquese con Recursos Humanos.')),
            ];
        });

        RateLimiter::for('enlace', fn (Request $request) => Limit::perMinute(30)->by('enlace:'.$request->ip()));
    }
}
