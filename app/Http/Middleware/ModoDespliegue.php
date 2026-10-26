<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModoDespliegue
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('app.deploy'), 403, 'Esta acción solo está disponible en modo despliegue (APP_DEPLOY=true).');

        return $next($request);
    }
}
