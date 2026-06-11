<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ConsolidadoController;

Route::get('/consolidado', [ConsolidadoController::class, 'index'])->name('consolidado.index');
    Route::get('/consolidado/{cliente}', [ConsolidadoController::class, 'show'])->name('consolidado.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/crear', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::get('/prestamos/{prestamo}', [PrestamoController::class, 'show'])->name('prestamos.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/panel', [PanelController::class, 'index'])->name('panel')->middleware('auth');

Route::post('/prestamos/{prestamo}/movimiento', [PrestamoController::class, 'guardarMovimiento'])
    ->name('prestamos.movimiento');

Route::post('/cuotas/{cuota}/pagar', [PrestamoController::class, 'pagarCuota'])
    ->name('cuotas.pagar');

Route::middleware(['auth'])->group(function () {

    Route::get('/consolidado', function () {
        return view('consolidado.index');
    })->name('consolidado');

    Route::get('/empresas', function () {
        return view('empresas.index');
    })->name('empresas.index');

    Route::get('/usuarios', function () {
        return view('usuarios.index');
    })->name('usuarios.index');

    Route::get('/formas-pago', function () {
        return view('formas_pago.index');
    })->name('formas_pago.index');

    Route::get('/auditorias', function () {
        return view('auditorias.index');
    })->name('auditorias.index');

    Route::get('/backups', function () {
        return '<h1>Backups</h1>';
    })->name('backups.index');
});


require __DIR__.'/auth.php';
