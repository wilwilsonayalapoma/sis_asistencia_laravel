<?php

use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\MarcadoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/marcado', [MarcadoController::class, 'index'])->name('marcado.index');
Route::post('/marcado', [MarcadoController::class, 'procesar'])->name('marcado.procesar');

Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
Route::get('/empleados/crear', [EmpleadoController::class, 'create'])->name('empleados.create');
Route::post('/empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
Route::get('/empleados/{empleado}/editar', [EmpleadoController::class, 'edit'])->name('empleados.edit');
Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
Route::patch('/empleados/{empleado}/estado', [EmpleadoController::class, 'cambiarEstado'])->name('empleados.estado');

Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');

Route::get('/configuraciones', [ConfiguracionController::class, 'index'])->name('configuraciones.index');
Route::put('/configuraciones', [ConfiguracionController::class, 'update'])->name('configuraciones.update');
Route::post('/configuraciones/tipos-personal', [ConfiguracionController::class, 'storeTipoPersonal'])->name('configuraciones.tipos-personal.store');
Route::patch('/configuraciones/tipos-personal/{tipoPersonal}/estado', [ConfiguracionController::class, 'cambiarEstadoTipoPersonal'])->name('configuraciones.tipos-personal.estado');
Route::post('/configuraciones/oficinas', [ConfiguracionController::class, 'storeOficina'])->name('configuraciones.oficinas.store');
Route::patch('/configuraciones/oficinas/{oficina}/estado', [ConfiguracionController::class, 'cambiarEstadoOficina'])->name('configuraciones.oficinas.estado');
Route::post('/configuraciones/turnos', [ConfiguracionController::class, 'storeTurno'])->name('configuraciones.turnos.store');
Route::patch('/configuraciones/turnos/{turno}/estado', [ConfiguracionController::class, 'cambiarEstadoTurno'])->name('configuraciones.turnos.estado');
Route::get('/configuraciones/turnos/{turno}/editar', [ConfiguracionController::class, 'editTurno'])->name('configuraciones.turnos.edit');
Route::put('/configuraciones/turnos/{turno}', [ConfiguracionController::class, 'updateTurno'])->name('configuraciones.turnos.update');
