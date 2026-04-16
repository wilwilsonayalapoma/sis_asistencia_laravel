<?php

namespace App\Http\Controllers;

use App\Models\Oficina;
use App\Models\Personal;
use App\Models\TipoPersonal;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $this->resolverFiltros($request);
        $catalogos = $this->obtenerCatalogos();
        $datos = $this->obtenerDatosReporte($filtros, false);

        return view('reportes.index', array_merge($catalogos, $datos, [
            'filtros' => $filtros,
        ]));
    }

    public function pdf(Request $request)
    {
        if (!extension_loaded('gd')) {
            return redirect()->route('reportes.index', $request->query())
                ->with('error', 'No se puede generar el PDF porque la extension GD de PHP no esta habilitada.');
        }

        $filtros = $this->resolverFiltros($request);
        $catalogos = $this->obtenerCatalogos();
        $datos = $this->obtenerDatosReporte($filtros, true);

        $pdf = Pdf::loadView('reportes.pdf', array_merge($catalogos, $datos, [
            'filtros' => $filtros,
            'generadoEn' => now(),
            'fondoBase64' => $this->imagenABase64(public_path('images/hoja_membretado.png')),
            'marcaAguaBase64' => $this->imagenABase64(public_path('images/marca_de_agua.png')),
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('reporte_asistencia_' . now()->format('Ymd_His') . '.pdf');
    }

    public function imprimir(Request $request)
    {
        $filtros = $this->resolverFiltros($request);
        $catalogos = $this->obtenerCatalogos();
        $datos = $this->obtenerDatosReporte($filtros, true);

        return view('reportes.imprimir', array_merge($catalogos, $datos, [
            'filtros' => $filtros,
            'generadoEn' => now(),
        ]));
    }

    private function obtenerCatalogos(): array
    {
        return [
            'oficinas' => Oficina::where('estado', 1)->orderBy('nombre')->get(),
            'tiposPersonal' => TipoPersonal::where('estado', 1)->orderBy('tipo')->get(),
            'empleados' => Personal::where('estado', 1)
                ->orderBy('paterno')
                ->orderBy('materno')
                ->orderBy('nombre')
                ->get(),
        ];
    }

    private function resolverFiltros(Request $request): array
    {
        $hoy = Carbon::today();
        $semanaActual = sprintf('%s-W%02d', $hoy->format('o'), $hoy->isoWeek());
        $mesActual = $hoy->format('Y-m');

        $validated = $request->validate([
            'periodo' => ['nullable', 'in:rango,semana,mes'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'semana' => ['nullable', 'regex:/^\\d{4}-W\\d{2}$/'],
            'mes' => ['nullable', 'regex:/^\\d{4}-\\d{2}$/'],
            'oficina_id' => ['nullable', 'integer'],
            'tipo_personal_id' => ['nullable', 'integer'],
            'empleado_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:presente,tardanza,ausente'],
            'tipo_reporte' => ['nullable', 'in:asistencia_general,tardanza_general,tardanza_individual,faltantes_general'],
        ]);

        $periodo = $validated['periodo'] ?? 'mes';
        $semana = $validated['semana'] ?? $semanaActual;
        $mes = $validated['mes'] ?? $mesActual;

        if ($periodo === 'semana') {
            preg_match('/^(\\d{4})-W(\\d{2})$/', $semana, $partesSemana);
            $anio = (int) ($partesSemana[1] ?? $hoy->format('o'));
            $numeroSemana = (int) ($partesSemana[2] ?? $hoy->isoWeek());

            $fechaInicio = Carbon::now()->setISODate($anio, $numeroSemana)->startOfWeek(Carbon::MONDAY)->toDateString();
            $fechaFin = Carbon::now()->setISODate($anio, $numeroSemana)->endOfWeek(Carbon::SUNDAY)->toDateString();
        } elseif ($periodo === 'mes') {
            $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
            $finMes = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
            $fechaInicio = $inicioMes->toDateString();
            $fechaFin = $finMes->toDateString();
        } else {
            $fechaInicio = $validated['fecha_inicio'] ?? $hoy->copy()->startOfMonth()->toDateString();
            $fechaFin = $validated['fecha_fin'] ?? $hoy->toDateString();
        }

        if ($fechaInicio > $fechaFin) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        return [
            'periodo' => $periodo,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin,
            'semana' => $semana,
            'mes' => $mes,
            'oficina_id' => $validated['oficina_id'] ?? null,
            'tipo_personal_id' => $validated['tipo_personal_id'] ?? null,
            'empleado_id' => $validated['empleado_id'] ?? null,
            'estado' => $validated['estado'] ?? null,
            'tipo_reporte' => $validated['tipo_reporte'] ?? 'asistencia_general',
        ];
    }

    private function obtenerDatosReporte(array $filtros, bool $esPdf): array
    {
        if ($filtros['tipo_reporte'] === 'faltantes_general') {
            return $this->obtenerDatosFaltantes($filtros);
        }

        $consultaBase = $this->armarConsultaBase($filtros);

        $totales = (clone $consultaBase)
            ->selectRaw('COUNT(*) as total_registros')
            ->selectRaw("SUM(a.estado = 'presente') as total_presentes")
            ->selectRaw("SUM(a.estado = 'tardanza') as total_tardanzas")
            ->selectRaw("SUM(a.estado = 'ausente') as total_ausentes")
            ->first();

        $resumen = (clone $consultaBase)
            ->selectRaw('p.id as personal_id')
            ->selectRaw('p.ci')
            ->selectRaw("CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) as nombre_completo")
            ->selectRaw('o.nombre as oficina')
            ->selectRaw('tp.tipo as tipo_personal')
            ->selectRaw('COUNT(*) as dias_registrados')
            ->selectRaw("SUM(a.estado = 'presente') as presentes")
            ->selectRaw("SUM(a.estado = 'tardanza') as tardanzas")
            ->selectRaw("SUM(a.estado = 'ausente') as ausentes")
            ->groupBy('p.id', 'p.ci', 'p.nombre', 'p.paterno', 'p.materno', 'o.nombre', 'tp.tipo')
            ->orderBy('o.nombre')
            ->orderBy('p.paterno')
            ->orderBy('p.materno')
            ->get();

        $detalleQuery = (clone $consultaBase)
            ->selectRaw('a.fecha')
            ->selectRaw('a.entrada')
            ->selectRaw('a.salida')
            ->selectRaw('a.estado')
            ->selectRaw('p.ci')
            ->selectRaw("CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) as nombre_completo")
            ->selectRaw('o.nombre as oficina')
            ->selectRaw('tp.tipo as tipo_personal')
            ->selectRaw('t.nombre as turno')
            ->orderByDesc('a.fecha')
            ->orderBy('p.paterno')
            ->orderBy('p.materno');

        if (!$esPdf) {
            $detalleQuery->limit(300);
        }

        $detalle = $detalleQuery->get();

        $rankingTardanzas = collect();
        if ($filtros['tipo_reporte'] === 'tardanza_general') {
            $rankingTardanzas = (clone $consultaBase)
                ->selectRaw('p.id as personal_id')
                ->selectRaw('p.ci')
                ->selectRaw("CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) as nombre_completo")
                ->selectRaw('COUNT(*) as total_tardanzas')
                ->groupBy('p.id', 'p.ci', 'p.nombre', 'p.paterno', 'p.materno')
                ->orderByDesc('total_tardanzas')
                ->orderBy('p.paterno')
                ->get();
        }

        $sinEmpleadoEnIndividual = $filtros['tipo_reporte'] === 'tardanza_individual' && empty($filtros['empleado_id']);

        return [
            'totales' => $totales,
            'resumen' => $resumen,
            'detalle' => $detalle,
            'rankingTardanzas' => $rankingTardanzas,
            'sinEmpleadoEnIndividual' => $sinEmpleadoEnIndividual,
        ];
    }

    private function obtenerDatosFaltantes(array $filtros): array
    {
        $faltantes = DB::table('personal as p')
            ->join('asignacion_oficina as ao', function ($join) use ($filtros) {
                $join->on('ao.personal_id', '=', 'p.id')
                    ->where('ao.estado', '=', 1)
                    ->whereDate('ao.fecha_inicio', '<=', $filtros['fecha_fin'])
                    ->where(function ($q) use ($filtros) {
                        $q->whereNull('ao.fecha_fin')
                            ->orWhereDate('ao.fecha_fin', '>=', $filtros['fecha_inicio']);
                    });
            })
            ->join('oficina as o', 'o.id', '=', 'ao.oficina_id')
            ->join('tipo_personal as tp', 'tp.id', '=', 'ao.tipo_personal_id')
            ->join('turno as t', 't.id', '=', 'ao.turno_id')
            ->leftJoin('asistencia as a', function ($join) use ($filtros) {
                $join->on('a.personal_id', '=', 'p.id')
                    ->whereBetween('a.fecha', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
                    ->where(function ($q) {
                        $q->whereNotNull('a.entrada')
                            ->orWhereNotNull('a.salida');
                    });
            })
            ->where('p.estado', 1)
            ->when($filtros['oficina_id'], fn ($q) => $q->where('o.id', $filtros['oficina_id']))
            ->when($filtros['tipo_personal_id'], fn ($q) => $q->where('tp.id', $filtros['tipo_personal_id']))
            ->when($filtros['empleado_id'], fn ($q) => $q->where('p.id', $filtros['empleado_id']))
            ->whereNull('a.id')
            ->selectRaw('p.id as personal_id')
            ->selectRaw('p.ci')
            ->selectRaw("CONCAT(p.paterno, ' ', COALESCE(p.materno,''), ' ', p.nombre) as nombre_completo")
            ->selectRaw('o.nombre as oficina')
            ->selectRaw('tp.tipo as tipo_personal')
            ->selectRaw('t.nombre as turno')
            ->groupBy('p.id', 'p.ci', 'p.nombre', 'p.paterno', 'p.materno', 'o.nombre', 'tp.tipo', 't.nombre')
            ->orderBy('o.nombre')
            ->orderBy('p.paterno')
            ->orderBy('p.materno')
            ->get();

        $diasRango = Carbon::parse($filtros['fecha_inicio'])->diffInDays(Carbon::parse($filtros['fecha_fin'])) + 1;

        $resumen = $faltantes->map(function ($f) use ($diasRango) {
            return (object) [
                'personal_id' => $f->personal_id,
                'ci' => $f->ci,
                'nombre_completo' => $f->nombre_completo,
                'oficina' => $f->oficina,
                'tipo_personal' => $f->tipo_personal,
                'dias_registrados' => 0,
                'presentes' => 0,
                'tardanzas' => 0,
                'ausentes' => $diasRango,
            ];
        });

        $detalle = $faltantes->map(function ($f) {
            return (object) [
                'fecha' => null,
                'entrada' => null,
                'salida' => null,
                'estado' => 'ausente',
                'ci' => $f->ci,
                'nombre_completo' => $f->nombre_completo,
                'oficina' => $f->oficina,
                'tipo_personal' => $f->tipo_personal,
                'turno' => $f->turno,
            ];
        });

        $totales = (object) [
            'total_registros' => $faltantes->count(),
            'total_presentes' => 0,
            'total_tardanzas' => 0,
            'total_ausentes' => $faltantes->count(),
        ];

        return [
            'totales' => $totales,
            'resumen' => $resumen,
            'detalle' => $detalle,
            'rankingTardanzas' => collect(),
            'sinEmpleadoEnIndividual' => false,
        ];
    }

    private function armarConsultaBase(array $filtros)
    {
        $consulta = DB::table('asistencia as a')
            ->join('personal as p', 'p.id', '=', 'a.personal_id')
            ->join('asignacion_oficina as ao', 'ao.id', '=', 'a.asignacion_oficina_id')
            ->join('oficina as o', 'o.id', '=', 'ao.oficina_id')
            ->join('tipo_personal as tp', 'tp.id', '=', 'ao.tipo_personal_id')
            ->join('turno as t', 't.id', '=', 'ao.turno_id')
            ->whereBetween('a.fecha', [$filtros['fecha_inicio'], $filtros['fecha_fin']])
            ->when($filtros['oficina_id'], fn ($q) => $q->where('o.id', $filtros['oficina_id']))
            ->when($filtros['tipo_personal_id'], fn ($q) => $q->where('tp.id', $filtros['tipo_personal_id']))
            ->when($filtros['empleado_id'], fn ($q) => $q->where('p.id', $filtros['empleado_id']));

        if (in_array($filtros['tipo_reporte'], ['tardanza_general', 'tardanza_individual'], true)) {
            $consulta->where('a.estado', 'tardanza');
        } elseif (!empty($filtros['estado'])) {
            $consulta->where('a.estado', $filtros['estado']);
        }

        if ($filtros['tipo_reporte'] === 'tardanza_individual' && empty($filtros['empleado_id'])) {
            $consulta->whereRaw('1 = 0');
        }

        return $consulta;
    }

    private function imagenABase64(string $ruta): ?string
    {
        if (!file_exists($ruta)) {
            return null;
        }

        $contenido = file_get_contents($ruta);
        if ($contenido === false) {
            return null;
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $mime = $extension === 'png' ? 'image/png' : 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($contenido);
    }
}
