<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte de Asistencia</title>
    <style>
        @page {
            size: A4;
            margin: 18mm 12mm 16mm 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 0;
            background-image: url('{{ asset('images/hoja_membretado.png') }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            position: relative;
            padding: 8px;
        }

        .marca-agua {
            position: fixed;
            top: 34%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 55%;
            opacity: 0.15;
            z-index: 0;
            pointer-events: none;
        }

        .contenido {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.90);
            padding: 8px;
            border: 1px solid #d1d5db;
        }

        h1 {
            margin: 0 0 6px;
            text-align: center;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .actions {
            display: flex;
            gap: 8px;
            margin: 10px 0;
        }

        .btn {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            background: rgba(255, 255, 255, 0.96);
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px;
            text-align: left;
            font-size: 11px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 8px;
        }

        .card {
            border: 1px solid #d1d5db;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px;
        }

        .card small {
            color: #6b7280;
            display: block;
        }

        .section {
            margin-top: 10px;
            font-weight: bold;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                background-attachment: fixed;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <img class="marca-agua" src="{{ asset('images/marca_de_agua.png') }}" alt="Marca de agua">

    <div class="contenido">
        <h1>Reporte de Asistencia</h1>

        <div class="actions">
            <button class="btn" onclick="window.print()">Imprimir / Guardar PDF</button>
            <a class="btn" href="{{ route('reportes.index', request()->query()) }}">Volver</a>
        </div>

        <div class="meta">
            <div><strong>Periodo:</strong> {{ $filtros['fecha_inicio'] }} al {{ $filtros['fecha_fin'] }}</div>
            <div><strong>Tipo de reporte:</strong> {{ str_replace('_', ' ', $filtros['tipo_reporte']) }}</div>
            <div><strong>Generado:</strong> {{ $generadoEn->format('Y-m-d H:i:s') }}</div>
        </div>

        <div class="cards">
            <div class="card"><small>Total registros</small><strong>{{ $totales->total_registros ?? 0 }}</strong></div>
            <div class="card"><small>Total presentes</small><strong>{{ $totales->total_presentes ?? 0 }}</strong></div>
            <div class="card"><small>Total tardanzas</small><strong>{{ $totales->total_tardanzas ?? 0 }}</strong></div>
            <div class="card"><small>Total ausentes</small><strong>{{ $totales->total_ausentes ?? 0 }}</strong></div>
        </div>

        @if($filtros['tipo_reporte'] === 'tardanza_general')
            <div class="section">Ranking de retrasos</div>
            <table>
                <thead>
                <tr>
                    <th>CI</th>
                    <th>Empleado</th>
                    <th>Total retrasos</th>
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
                    <tr><td colspan="3">Sin datos.</td></tr>
                @endforelse
                </tbody>
            </table>
        @endif

        <div class="section">Detalle del reporte</div>
        <table>
            <thead>
            <tr>
                <th>Fecha</th>
                <th>CI</th>
                <th>Empleado</th>
                <th>Oficina</th>
                <th>Tipo</th>
                <th>Turno</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Estado</th>
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
                <tr><td colspan="9">Sin registros para el rango seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
