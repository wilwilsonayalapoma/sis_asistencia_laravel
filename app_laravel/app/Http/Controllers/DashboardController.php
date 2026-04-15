<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Oficina;
use App\Models\Personal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today()->toDateString();

        $faltantes = DB::table('personal as p')
            ->join('asignacion_oficina as ao', function ($join) use ($hoy) {
                $join->on('ao.personal_id', '=', 'p.id')
                    ->where('ao.estado', '=', 1)
                    ->whereDate('ao.fecha_inicio', '<=', $hoy)
                    ->where(function ($q) use ($hoy) {
                        $q->whereNull('ao.fecha_fin')
                            ->orWhereDate('ao.fecha_fin', '>=', $hoy);
                    });
            })
            ->join('oficina as o', 'o.id', '=', 'ao.oficina_id')
            ->leftJoin('asistencia as a', function ($join) use ($hoy) {
                $join->on('a.personal_id', '=', 'p.id')
                    ->whereDate('a.fecha', '=', $hoy);
            })
            ->where('p.estado', 1)
            ->whereNull('a.id')
            ->select(
                'p.id',
                'p.ci',
                'p.nombre',
                'p.paterno',
                'p.materno',
                'o.nombre as oficina'
            )
            ->orderBy('p.paterno', 'asc')
            ->orderBy('p.materno', 'asc')
            ->orderBy('p.nombre', 'asc')
            ->get();

        $stats = [
            'empleados_activos' => Personal::where('estado', 1)->count(),
            'oficinas_activas' => Oficina::where('estado', 1)->count(),
            'marcados_hoy' => Asistencia::whereDate('fecha', $hoy)->count(),
            'tardanzas_hoy' => Asistencia::whereDate('fecha', $hoy)->where('estado', 'tardanza')->count(),
            'faltantes_hoy' => $faltantes->count(),
        ];

        $ultimos = Asistencia::with(['personal', 'asignacion.oficina'])
            ->whereDate('fecha', $hoy)
            ->orderByDesc('actualizado_el')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('stats', 'ultimos', 'faltantes'));
    }
}
