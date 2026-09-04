<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatosController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\MultaController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SancionController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

// ── Página de inicio → redirige al login ────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Autenticación ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Rutas protegidas (requieren JWT en sesión) ───────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Catálogo de libros ────────────────────────────────────────────────
    Route::get('/catalogo',             [CatalogoController::class, 'index'])->name('catalogo.index');
    Route::get('/catalogo/crear',       [CatalogoController::class, 'create'])->name('catalogo.create');
    Route::post('/catalogo',            [CatalogoController::class, 'store'])->name('catalogo.store');
    Route::get('/catalogo/{id}',        [CatalogoController::class, 'show'])->name('catalogo.show');
    Route::get('/catalogo/{id}/editar', [CatalogoController::class, 'edit'])->name('catalogo.edit');
    Route::put('/catalogo/{id}',        [CatalogoController::class, 'update'])->name('catalogo.update');
    Route::delete('/catalogo/{id}',     [CatalogoController::class, 'destroy'])->name('catalogo.destroy');

    // ── Usuarios ────────────────────────────────────────────────────────
    Route::get('/usuarios',                [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear',          [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios',               [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}',           [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{id}/editar',    [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}',           [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}',        [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // ── Préstamos ───────────────────────────────────────────────────────
    Route::get('/prestamos',                [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/crear',          [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos',               [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::get('/prestamos/renovaciones-pendientes',           [PrestamoController::class, 'renovacionesPendientes'])->name('prestamos.renovaciones-pendientes');
    Route::get('/prestamos/{id}',           [PrestamoController::class, 'show'])->name('prestamos.show');
    Route::put('/prestamos/{id}/devolver',  [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::put('/prestamos/{id}/renovar',   [PrestamoController::class, 'renovar'])->name('prestamos.renovar');
    Route::put('/prestamos/{id}/aprobar-renovacion',           [PrestamoController::class, 'aprobarRenovacion'])->name('prestamos.aprobar-renovacion');
    Route::put('/prestamos/{id}/rechazar-renovacion',          [PrestamoController::class, 'rechazarRenovacion'])->name('prestamos.rechazar-renovacion');

    // ── Devoluciones ────────────────────────────────────────────────────
    Route::get('/devoluciones',            [PrestamoController::class, 'devoluciones'])->name('devoluciones.index');

    // ── Reservas ────────────────────────────────────────────────────────
    Route::get('/reservas',                [ReservaController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/crear',          [ReservaController::class, 'create'])->name('reservas.create');
    Route::post('/reservas',               [ReservaController::class, 'store'])->name('reservas.store');
    Route::get('/reservas/{id}',           [ReservaController::class, 'show'])->name('reservas.show');
    Route::delete('/reservas/{id}',        [ReservaController::class, 'cancelar'])->name('reservas.cancelar');

    // ── Multas ──────────────────────────────────────────────────────────
    Route::get('/multas',                  [MultaController::class, 'index'])->name('multas.index');
    Route::post('/multas/{id}/pagar',      [MultaController::class, 'pagar'])->name('multas.pagar');

    // ── Reportes ────────────────────────────────────────────────────────
    Route::get('/reportes',                [ReporteController::class, 'index'])->name('reportes.index');

    // ── Configuración ───────────────────────────────────────────────────
    Route::get('/configuracion',           [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion',          [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // ── Códigos QR ─────────────────────────────────────────────────────
    Route::get('/qr-codigos',                  [QrController::class, 'index'])->name('qr-codigos.index');
    Route::get('/qr-codigos/escanear',         [QrController::class, 'validar'])->name('qr-codigos.validar');
    Route::post('/qr-codigos',                 [QrController::class, 'store'])->name('qr-codigos.store');
    Route::post('/qr-codigos/{id}/toggle',     [QrController::class, 'toggle'])->name('qr-codigos.toggle');
    Route::post('/qr-codigos/{id}/regenerar',  [QrController::class, 'regenerar'])->name('qr-codigos.regenerar');

    // ── Notificaciones ────────────────────────────────────────────────
    Route::get('/notificaciones',                 [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::get('/notificaciones/no-leidas',       [NotificacionController::class, 'noLeidasJson'])->name('notificaciones.no-leidas-json');
    Route::post('/notificaciones',                [NotificacionController::class, 'store'])->name('notificaciones.store');
    Route::post('/notificaciones/{id}/leida',     [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/leer-todas',     [NotificacionController::class, 'leerTodas'])->name('notificaciones.leer-todas');

    // ── Sanciones ─────────────────────────────────────────────────────
    Route::get('/sanciones',                  [SancionController::class, 'index'])->name('sanciones.index');
    Route::post('/sanciones',                 [SancionController::class, 'store'])->name('sanciones.store');
    Route::post('/sanciones/{id}/levantar',   [SancionController::class, 'levantar'])->name('sanciones.levantar');

    // ── Mi Perfil ─────────────────────────────────────────────────────
    Route::get('/perfil',                     [PerfilController::class, 'index'])->name('perfil.index');
    Route::put('/perfil',                     [PerfilController::class, 'update'])->name('perfil.update');

    // ── Auto-servicio del estudiante ─────────────────────────────────
    Route::get('/estudiante/mis-prestamos',              [EstudianteController::class, 'misPrestamos'])->name('estudiante.mis-prestamos');
    Route::put('/estudiante/prestamos/{id}/solicitar-renovacion', [EstudianteController::class, 'solicitarRenovacion'])->name('estudiante.solicitar-renovacion');
    Route::get('/estudiante/mis-reservas',               [EstudianteController::class, 'misReservas'])->name('estudiante.mis-reservas');
    Route::post('/estudiante/reservar-libro',            [EstudianteController::class, 'reservarLibro'])->name('estudiante.reservar-libro');
    Route::delete('/estudiante/reservas/{id}',           [EstudianteController::class, 'cancelarReserva'])->name('estudiante.cancelar-reserva');

    // ── Auditoría ───────────────────────────────────────────────────────
    Route::get('/auditoria',               [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/reporte',       [AuditoriaController::class, 'pdf'])->name('auditoria.reporte');

    // ── Búsqueda en vivo (fragmentos de tabla para Alpine) ──────────────
    Route::get('/datos/catalogo',        [DatosController::class, 'catalogo'])->name('datos.catalogo');
    Route::get('/datos/usuarios',        [DatosController::class, 'usuarios'])->name('datos.usuarios');
    Route::get('/datos/prestamos',       [DatosController::class, 'prestamos'])->name('datos.prestamos');
    Route::get('/datos/reservas',        [DatosController::class, 'reservas'])->name('datos.reservas');
    Route::get('/datos/ejemplares',      [DatosController::class, 'ejemplares'])->name('datos.ejemplares');
    Route::get('/datos/ejemplar-codigo', [DatosController::class, 'ejemplarPorCodigo'])->name('datos.ejemplar.codigo');
});
