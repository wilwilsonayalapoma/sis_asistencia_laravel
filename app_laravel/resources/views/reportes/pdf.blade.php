<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia</title>
    <style>
        @page {
            margin: 34mm 12mm 16mm 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .background-layer {
            position: fixed;
            top: -34mm;
            left: -12mm;
            width: 210mm;
            height: 297mm;
            z-index: -1000;
        }

        .background-layer img {
            width: 100%;
            height: 100%;
        }

        .watermark-layer {
            position: fixed;
            top: 95mm;
            left: 40mm;
            width: 130mm;
            z-index: -900;
            opacity: 0.16;
        }

        .watermark-layer img {
            width: 100%;
            height: auto;
        }

        .header {
            margin-bottom: 10px;
        }

        .title {
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .subtitle {
            text-align: center;
            margin: 4px 0 8px;
            font-size: 10px;
        }

        .meta {
            margin-bottom: 12px;
            border: 1px solid #d1d5db;
            padding: 8px;
            background: rgba(255, 255, 255, 0.88);
        }

        .meta-row {
            margin-bottom: 4px;
        }

        .meta-row:last-child {
            margin-bottom: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.9);
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: left;
            font-size: 10px;
        }

        .totales {
            margin-bottom: 10px;
        }

        .section-title {
            margin: 12px 0 6px;
            font-size: 12px;
            font-weight: 700;
        }

        .muted {
            color: #6b7280;
        }

        .capitalize {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
@if($fondoBase64)
    <div class="background-layer">
        <img src="{{ $fondoBase64 }}" alt="Fondo membretado">
    </div>
@endif

@if($marcaAguaBase64)
    <div class="watermark-layer">
        <img src="{{ $marcaAguaBase64 }}" alt="Marca de agua">
    </div>
@endif

<div class="header">
    <p class="title">Reporte de Asistencia</p>
    <p class="subtitle">Sistema de control de asistencia</p>
</div>

<div class="meta">
    <div class="meta-row"><strong>Periodo:</strong> {{ $filtros['fecha_inicio'] }} al {{ $filtros['fecha_fin'] }}</div>
    <div class="meta-row"><strong>Tipo de reporte:</strong> <span class="capitalize">{{ str_replace('_', ' ', $filtros['tipo_reporte']) }}</span></div>
    <div class="meta-row"><strong>Filtros:</strong>
        Oficina={{ $oficinas->firstWhere('id', $filtros['oficina_id'])->nombre ?? 'Todas' }},
        Tipo={{ $tiposPersonal->firstWhere('id', $filtros['tipo_personal_id'])->tipo ?? 'Todos' }},
        Empleado={{ $empleados->firstWhere('id', $filtros['empleado_id'])->nombre_completo ?? 'Todos' }},
        Estado={{ $filtros['estado'] ?? 'Todos' }}
    </div>
    <div class="meta-row"><strong>Generado:</strong> {{ $generadoEn->format('Y-m-d H:i:s') }}</div>
</div>

<table class="totales">
    <thead>
        <tr>
            <th>Total registros</th>
            <th>Total presentes</th>
            <th>Total tardanzas</th>
            <th>Total ausentes</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $totales->total_registros ?? 0 }}</td>
            <td>{{ $totales->total_presentes ?? 0 }}</td>
            <td>{{ $totales->total_tardanzas ?? 0 }}</td>
            <td>{{ $totales->total_ausentes ?? 0 }}</td>
        </tr>
    </tbody>
</table>

@if($filtros['tipo_reporte'] === 'tardanza_general')
    <div class="section-title">Ranking de retrasos</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">CI</th>
                <th style="width: 65%;">Empleado</th>
                <th style="width: 20%;">Total retrasos</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rankingTardanzas as $item)
            <tr>
                <td>{{ $item->ci }}</td>
                <td>{{ $item->nombre_completo }}</td>
                <td>{{ $item->total_tardanzas }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="muted">Sin datos.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endif

<div class="section-title">Detalle del reporte</div>
<table>
    <thead>
        <tr>
            <th style="width: 9%;">Fecha</th>
            <th style="width: 10%;">CI</th>
            <th style="width: 23%;">Empleado</th>
            <th style="width: 13%;">Oficina</th>
            <th style="width: 12%;">Tipo</th>
            <th style="width: 10%;">Turno</th>
            <th style="width: 8%;">Entrada</th>
            <th style="width: 8%;">Salida</th>
            <th style="width: 7%;">Estado</th>
        </tr>
    </thead>
    <tbody>
    @forelse($detalle as $d)
        <tr>
            <td>{{ \Carbon\Carbon::parse($d->fecha)->format('Y-m-d') }}</td>
            <td>{{ $d->ci }}</td>
            <td>{{ $d->nombre_completo }}</td>
            <td>{{ $d->oficina }}</td>
            <td>{{ $d->tipo_personal }}</td>
            <td>{{ $d->turno }}</td>
            <td>{{ $d->entrada ? \Carbon\Carbon::parse($d->entrada)->format('H:i:s') : '-' }}</td>
            <td>{{ $d->salida ? \Carbon\Carbon::parse($d->salida)->format('H:i:s') : '-' }}</td>
            <td>{{ $d->estado }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="muted">Sin registros para el rango seleccionado.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
