<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\ConsolidadoController;
use App\Http\Controllers\UsuarioController;

// Página de bienvenida pública (sin iniciar sesión)
Route::get('/', function () {
    return view('welcome');
});

// =========================================================================
// ACCESOS COMUNES PARA TODOS LOS USUARIOS LOGUEADOS (SUPERADMIN, ADMIN y CLIENTE)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    
    // Panel de Inicio / Dashboard común
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    // Gestión del Perfil propio
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================================================================
// MÓDULO CONSOLIDADO DE CLIENTES Y CONSULTA DE PRÉSTAMOS (Lectura para Clientes)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    
    // El listado general de consolidados SOLO lo ven SUPERADMIN y ADMIN
    Route::get('/consolidado', [ConsolidadoController::class, 'index'])
        ->middleware('role:SUPERADMIN,ADMIN')
        ->name('consolidado.index');
        
    // Al detalle del consolidado entran todos (El Cliente solo accede a su propio ID)
    Route::get('/consolidado/{cliente}', [ConsolidadoController::class, 'show'])
        ->middleware('role:SUPERADMIN,ADMIN,CLIENTE')
        ->name('consolidado.show');

    // PERMISO DE LECTURA DE PRÉSTAMO: Ahora el CLIENTE también puede entrar a ver la ficha de su préstamo
    Route::get('/prestamos/{prestamo}', [PrestamoController::class, 'show'])
        ->middleware('role:SUPERADMIN,ADMIN,CLIENTE')
        ->name('prestamos.show');
});

// =========================================================================
// MÓDULOS DE CREACIÓN Y MODIFICACIÓN (Solo SUPERADMIN y ADMIN)
// =========================================================================
Route::middleware(['auth', 'role:SUPERADMIN,ADMIN'])->group(function () {
    
    // Gestión de Clientes
    Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
    Route::get('/clientes/crear', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
    Route::get('/clientes/{cliente}/editar', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');

    // Acciones operativas de Préstamos, Movimientos y Cuotas (Crear, pagar, mover)
    Route::get('/prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
    Route::get('/prestamos/crear', [PrestamoController::class, 'create'])->name('prestamos.create');
    Route::post('/prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
    Route::post('/prestamos/{prestamo}/movimiento', [PrestamoController::class, 'guardarMovimiento'])->name('prestamos.movimiento');
    Route::post('/cuotas/{cuota}/pagar', [PrestamoController::class, 'pagarCuota'])->name('cuotas.pagar');

    // Formas de Pago
    Route::get('/formas-pago', function () {
        return view('formas_pago.index');
    })->name('formas_pago.index');
});

// =========================================================================
// MÓDULOS EXCLUSIVOS PARA EL SÚPER ADMINISTRADOR (Acceso Total Global)
// =========================================================================
Route::middleware(['auth', 'role:SUPERADMIN'])->group(function () {
    
    // Gestión de Usuarios del Sistema
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');

    // Configuraciones avanzadas del sistema
    Route::get('/empresas', function () { return view('empresas.index'); })->name('empresas.index');
    Route::get('/auditorias', function () { return view('auditorias.index'); })->name('auditorias.index');
    Route::get('/backups', function () { return '<h1>Backups</h1>'; })->name('backups.index');
});

require __DIR__.'/auth.php';