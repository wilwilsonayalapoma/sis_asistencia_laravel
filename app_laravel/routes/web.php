<?php

use App\Http\Controllers\AuthController;
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

Route::get('/', [MarcadoController::class, 'publico'])->name('public.asistencia');
Route::post('/registro-asistencia', [MarcadoController::class, 'registrarPublico'])->name('public.asistencia.registrar');

Route::middleware('guest')->group(function () {
	Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
	Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::middleware('auth')->group(function () {
	Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

Route::middleware(['auth', 'admin'])->group(function () {
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
	Route::get('/reportes/pdf', [ReporteController::class, 'pdf'])->name('reportes.pdf');
	Route::get('/reportes/imprimir', [ReporteController::class, 'imprimir'])->name('reportes.imprimir');

	Route::prefix('/configuraciones')->name('configuraciones.')->group(function () {
		Route::get('/', [ConfiguracionController::class, 'index'])->name('index');

		Route::get('/general', [ConfiguracionController::class, 'general'])->name('general');
		Route::put('/general', [ConfiguracionController::class, 'updateGeneral'])->name('general.update');

		Route::get('/tipos-personal', [ConfiguracionController::class, 'tiposPersonal'])->name('tipos-personal.index');
		Route::post('/tipos-personal', [ConfiguracionController::class, 'storeTipoPersonal'])->name('tipos-personal.store');
		Route::patch('/tipos-personal/{tipoPersonal}/estado', [ConfiguracionController::class, 'cambiarEstadoTipoPersonal'])->name('tipos-personal.estado');

		Route::get('/oficinas', [ConfiguracionController::class, 'oficinas'])->name('oficinas.index');
		Route::post('/oficinas', [ConfiguracionController::class, 'storeOficina'])->name('oficinas.store');
		Route::patch('/oficinas/{oficina}/estado', [ConfiguracionController::class, 'cambiarEstadoOficina'])->name('oficinas.estado');

		Route::get('/turnos', [ConfiguracionController::class, 'turnos'])->name('turnos.index');
		Route::post('/turnos', [ConfiguracionController::class, 'storeTurno'])->name('turnos.store');
		Route::patch('/turnos/{turno}/estado', [ConfiguracionController::class, 'cambiarEstadoTurno'])->name('turnos.estado');
		Route::get('/turnos/{turno}/editar', [ConfiguracionController::class, 'editTurno'])->name('turnos.edit');
		Route::put('/turnos/{turno}', [ConfiguracionController::class, 'updateTurno'])->name('turnos.update');
	});
});
