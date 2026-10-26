<?php

use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ModoDespliegue;
use App\Http\Middleware\UsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectUsersTo(function (Request $request) {
            return $request->user()?->getDashboardRoute() ?? route('registro.personal');
        });

        $middleware->web(append: [
            HandleInertiaRequests::class,
            CabecerasSeguridad::class,
        ]);

        $middleware->statefulApi();

        $middleware->alias([
            'usuario.activo' => UsuarioActivo::class,
            'despliegue' => ModoDespliegue::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->create();
