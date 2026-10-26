<?php

use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\RolController;
use App\Http\Controllers\Admin\SistemaController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistroCuentaController;
use App\Http\Controllers\CorreccionFichaController;
use App\Http\Controllers\Gestion\CuentaController;
use App\Http\Controllers\Gestion\FichaPDFController;
use App\Http\Controllers\Gestion\FotoPersonalController;
use App\Http\Controllers\Gestion\MiFichaController;
use App\Http\Controllers\Gestion\PersonalController;
use App\Http\Controllers\RegistroPersonalController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('registro.personal');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:login');
    Route::get('/registro-cuenta', [RegistroCuentaController::class, 'create'])->name('registro.cuenta');
    Route::post('/registro-cuenta', [RegistroCuentaController::class, 'store'])->name('registro.cuenta.store')->middleware('throttle:registro');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/registro-personal', [RegistroPersonalController::class, 'create'])->name('registro.personal');
Route::post('/registro-personal', [RegistroPersonalController::class, 'store'])->name('registro.personal.store')->middleware('throttle:registro');
Route::where(['token' => '[A-Za-z0-9]{48}'])->group(function () {
    Route::get('/correccion/{token}', [CorreccionFichaController::class, 'edit'])->name('correccion.edit')->middleware('throttle:enlace');
    Route::get('/correccion/{token}/foto', [CorreccionFichaController::class, 'foto'])->name('correccion.foto')->middleware('throttle:enlace');
    Route::post('/correccion/{token}', [CorreccionFichaController::class, 'update'])->name('correccion.update')->middleware('throttle:registro');
});

Route::get('/registro-exitoso', function () {
    return Inertia::render('RegistroExitoso');
})->name('registro.exitoso');

Route::middleware(['auth', 'usuario.activo', 'can:panel.acceder'])->prefix('panel')->group(function () {
    Route::get('/', fn () => redirect(auth()->user()->getDashboardRoute()))->name('panel');

    Route::get('/cuenta', [CuentaController::class, 'index'])->name('cuenta');
    Route::patch('/cuenta', [CuentaController::class, 'update'])->name('cuenta.update');

    Route::get('/personal/{id}/foto', [FotoPersonalController::class, 'mostrar'])->name('personal.foto')->whereNumber('id');

    Route::middleware('can:mi_ficha.editar')->group(function () {
        Route::get('/mi-ficha', [MiFichaController::class, 'edit'])->name('mi_ficha.edit');
        Route::put('/mi-ficha', [MiFichaController::class, 'update'])->name('mi_ficha.update');
    });

    Route::middleware('can:personal.ver')->group(function () {
        Route::get('/personal', [PersonalController::class, 'index'])->name('personal.index');
        Route::get('/personal/exportar', [PersonalController::class, 'export'])->name('personal.export')->middleware('can:personal.exportar');
        Route::get('/personal/{id}', [PersonalController::class, 'show'])->name('personal.show')->whereNumber('id')->middleware('can:personal.ver_detalle');
        Route::get('/personal/{id}/ficha-pdf', [FichaPDFController::class, 'generate'])->name('personal.ficha.pdf')->whereNumber('id')->middleware('can:personal.pdf');
        Route::put('/personal/{id}/estado', [PersonalController::class, 'updateStatus'])->name('personal.estado')->whereNumber('id')->middleware('can:personal.cambiar_estado');
        Route::middleware('can:personal.editar')->group(function () {
            Route::get('/personal/{id}/editar', [PersonalController::class, 'edit'])->name('personal.edit')->whereNumber('id');
            Route::put('/personal/{id}', [PersonalController::class, 'update'])->name('personal.update')->whereNumber('id');
            Route::post('/personal/{id}/enlace-correccion', [PersonalController::class, 'generarEnlace'])->name('personal.enlace')->whereNumber('id');
            Route::delete('/personal/{id}/enlace-correccion', [PersonalController::class, 'revocarEnlace'])->name('personal.enlace.revocar')->whereNumber('id');
        });
        Route::middleware('can:personal.eliminar')->group(function () {
            Route::delete('/personal/{id}', [PersonalController::class, 'destroy'])->name('personal.destroy')->whereNumber('id');
            Route::put('/personal/{id}/restaurar', [PersonalController::class, 'restore'])->name('personal.restore')->whereNumber('id');
        });
    });

    Route::middleware('can:usuarios.ver')->group(function () {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::middleware('can:usuarios.gestionar')->group(function () {
            Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit')->whereNumber('id');
            Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update')->whereNumber('id');
            Route::put('/usuarios/{id}/estado', [UsuarioController::class, 'updateStatus'])->name('usuarios.estado')->whereNumber('id');
            Route::put('/usuarios/{id}/contrasena', [UsuarioController::class, 'updatePassword'])->name('usuarios.contrasena')->whereNumber('id');
            Route::put('/usuarios/{id}/aprobar', [UsuarioController::class, 'aprobar'])->name('usuarios.aprobar')->whereNumber('id');
            Route::delete('/usuarios/{id}', [UsuarioController::class, 'rechazar'])->name('usuarios.rechazar')->whereNumber('id');
            Route::put('/usuarios/{id}/ficha', [UsuarioController::class, 'vincularFicha'])->name('usuarios.ficha')->whereNumber('id');
        });
    });

    Route::middleware('can:roles.gestionar')->group(function () {
        Route::get('/roles', [RolController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RolController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}', [RolController::class, 'update'])->name('roles.update')->whereNumber('id');
        Route::delete('/roles/{id}', [RolController::class, 'destroy'])->name('roles.destroy')->whereNumber('id');
    });

    Route::middleware('can:configuracion.gestionar')->group(function () {
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');
    });

    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index')->middleware('can:auditoria.ver');

    Route::middleware('can:sistema.gestionar')->prefix('sistema')->group(function () {
        Route::get('/', [SistemaController::class, 'index'])->name('sistema.index');
        Route::middleware('despliegue')->group(function () {
            Route::post('/limpiar-cache', [SistemaController::class, 'limpiarCache'])->name('sistema.cache');
            Route::post('/sincronizar-permisos', [SistemaController::class, 'sincronizarPermisos'])->name('sistema.permisos');
            Route::post('/enlace-archivos', [SistemaController::class, 'crearEnlaceArchivos'])->name('sistema.storage');
            Route::post('/proteger-fotos', [SistemaController::class, 'protegerFotos'])->name('sistema.fotos');
        });
        Route::post('/depurar-auditoria', [SistemaController::class, 'limpiarAuditoria'])->name('sistema.auditoria');
    });
});

Route::get('/admin/{ruta?}', fn (?string $ruta = null) => redirect('/panel/'.$ruta, 301))->where('ruta', '.*');
Route::get('/rrhh/{ruta?}', fn (?string $ruta = null) => redirect('/panel/'.$ruta, 301))->where('ruta', '.*');
