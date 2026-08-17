<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\DashboardController;
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
});
