<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Oficina;
use App\Models\Personal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today()->toDateString();

        $stats = [
            'empleados_activos' => Personal::where('estado', 1)->count(),
            'oficinas_activas' => Oficina::where('estado', 1)->count(),
            'marcados_hoy' => Asistencia::whereDate('fecha', $hoy)->count(),
            'tardanzas_hoy' => Asistencia::whereDate('fecha', $hoy)->where('estado', 'tardanza')->count(),
        ];

        $ultimos = Asistencia::with(['personal', 'asignacion.oficina'])
            ->whereDate('fecha', $hoy)
            ->orderByDesc('actualizado_el')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('stats', 'ultimos'));
    }
}
