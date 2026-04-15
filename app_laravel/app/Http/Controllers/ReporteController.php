<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Oficina;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $oficinas = Oficina::where('estado', 1)->orderBy('nombre')->get();

        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->get('fecha_fin', Carbon::now()->toDateString());
        $oficinaId = $request->get('oficina_id');

        $resumen = DB::table('asistencia as a')
            ->join('personal as p', 'p.id', '=', 'a.personal_id')
            ->join('asignacion_oficina as ao', 'ao.id', '=', 'a.asignacion_oficina_id')
            ->join('oficina as o', 'o.id', '=', 'ao.oficina_id')
            ->select(
                'p.id',
                'p.ci',
                DB::raw("CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) as nombre_completo"),
                'o.nombre as oficina',
                DB::raw('COUNT(*) as dias_registrados'),
                DB::raw("SUM(a.estado = 'presente') as presentes"),
                DB::raw("SUM(a.estado = 'tardanza') as tardanzas"),
                DB::raw("SUM(a.estado = 'ausente') as ausentes")
            )
            ->whereBetween('a.fecha', [$fechaInicio, $fechaFin])
            ->when($oficinaId, fn ($q) => $q->where('o.id', $oficinaId))
            ->groupBy('p.id', 'p.ci', 'p.nombre', 'p.paterno', 'p.materno', 'o.nombre')
            ->orderBy('o.nombre')
            ->orderBy('p.paterno')
            ->get();

        $detalle = Asistencia::with(['personal', 'asignacion.oficina'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->when($oficinaId, function ($query) use ($oficinaId) {
                $query->whereHas('asignacion', function ($q) use ($oficinaId) {
                    $q->where('oficina_id', $oficinaId);
                });
            })
            ->orderByDesc('fecha')
            ->limit(100)
            ->get();

        return view('reportes.index', compact('oficinas', 'resumen', 'detalle', 'fechaInicio', 'fechaFin', 'oficinaId'));
    }
}
