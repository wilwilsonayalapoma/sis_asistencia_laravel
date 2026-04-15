@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<h1 class="h3 mb-3">Reportes</h1>

<div class="card p-3 mb-4">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Fecha inicio</label>
            <input class="form-control" type="date" name="fecha_inicio" value="{{ $fechaInicio }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fecha fin</label>
            <input class="form-control" type="date" name="fecha_fin" value="{{ $fechaFin }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Oficina</label>
            <select class="form-select" name="oficina_id">
                <option value="">Todas</option>
                @foreach($oficinas as $oficina)
                    <option value="{{ $oficina->id }}" {{ (string)$oficinaId === (string)$oficina->id ? 'selected' : '' }}>
                        {{ $oficina->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </div>
    </form>
</div>

<div class="card mb-4">
    <div class="card-header bg-white"><strong>Resumen por empleado</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Empleado</th>
                    <th>Oficina</th>
                    <th>Dias</th>
                    <th>Presentes</th>
                    <th>Tardanzas</th>
                    <th>Ausentes</th>
                </tr>
            </thead>
            <tbody>
            @forelse($resumen as $r)
                <tr>
                    <td>{{ $r->ci }}</td>
                    <td>{{ $r->nombre_completo }}</td>
                    <td>{{ $r->oficina }}</td>
                    <td>{{ $r->dias_registrados }}</td>
                    <td>{{ $r->presentes }}</td>
                    <td>{{ $r->tardanzas }}</td>
                    <td>{{ $r->ausentes }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Sin datos para el rango seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Detalle (ultimos 100)</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>CI</th>
                    <th>Empleado</th>
                    <th>Oficina</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            @forelse($detalle as $d)
                <tr>
                    <td>{{ optional($d->fecha)->format('Y-m-d') }}</td>
                    <td>{{ $d->personal->ci ?? '-' }}</td>
                    <td>{{ $d->personal->nombre_completo ?? '-' }}</td>
                    <td>{{ $d->asignacion->oficina->nombre ?? '-' }}</td>
                    <td>{{ optional($d->entrada)->format('H:i:s') }}</td>
                    <td>{{ optional($d->salida)->format('H:i:s') }}</td>
                    <td>{{ $d->estado }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
