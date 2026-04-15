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
