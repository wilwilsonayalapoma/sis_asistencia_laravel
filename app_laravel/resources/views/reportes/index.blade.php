@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
<h1 class="h3 mb-3">Reportes</h1>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card p-3 mb-4">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Tipo de reporte</label>
            <select class="form-select" name="tipo_reporte">
                <option value="asistencia_general" {{ $filtros['tipo_reporte'] === 'asistencia_general' ? 'selected' : '' }}>Asistencia general</option>
                <option value="tardanza_general" {{ $filtros['tipo_reporte'] === 'tardanza_general' ? 'selected' : '' }}>Retrasos generales</option>
                <option value="tardanza_individual" {{ $filtros['tipo_reporte'] === 'tardanza_individual' ? 'selected' : '' }}>Retrasos por empleado</option>
                <option value="faltantes_general" {{ $filtros['tipo_reporte'] === 'faltantes_general' ? 'selected' : '' }}>Empleados faltantes</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Periodo</label>
            <select class="form-select" id="periodo" name="periodo">
                <option value="mes" {{ $filtros['periodo'] === 'mes' ? 'selected' : '' }}>Mes</option>
                <option value="semana" {{ $filtros['periodo'] === 'semana' ? 'selected' : '' }}>Semana</option>
                <option value="rango" {{ $filtros['periodo'] === 'rango' ? 'selected' : '' }}>Fecha a fecha</option>
            </select>
        </div>
        <div class="col-md-3" id="filtroMesWrap">
            <label class="form-label">Mes</label>
            <input class="form-control" type="month" name="mes" value="{{ $filtros['mes'] }}">
        </div>
        <div class="col-md-3" id="filtroSemanaWrap">
            <label class="form-label">Semana</label>
            <input class="form-control" type="week" name="semana" value="{{ $filtros['semana'] }}">
        </div>
        <div class="col-md-3" id="filtroInicioWrap">
            <label class="form-label">Fecha inicio</label>
            <input class="form-control" type="date" name="fecha_inicio" value="{{ $filtros['fecha_inicio'] }}">
        </div>
        <div class="col-md-3" id="filtroFinWrap">
            <label class="form-label">Fecha fin</label>
            <input class="form-control" type="date" name="fecha_fin" value="{{ $filtros['fecha_fin'] }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Oficina</label>
            <select class="form-select" name="oficina_id">
                <option value="">Todas</option>
                @foreach($oficinas as $oficina)
                    <option value="{{ $oficina->id }}" {{ (string)$filtros['oficina_id'] === (string)$oficina->id ? 'selected' : '' }}>
                        {{ $oficina->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipo de personal</label>
            <select class="form-select" name="tipo_personal_id">
                <option value="">Todos</option>
                @foreach($tiposPersonal as $tipo)
                    <option value="{{ $tipo->id }}" {{ (string)$filtros['tipo_personal_id'] === (string)$tipo->id ? 'selected' : '' }}>
                        {{ $tipo->tipo }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Empleado</label>
            <select class="form-select" name="empleado_id">
                <option value="">Todos</option>
                @foreach($empleados as $empleado)
                    <option value="{{ $empleado->id }}" {{ (string)$filtros['empleado_id'] === (string)$empleado->id ? 'selected' : '' }}>
                        {{ $empleado->ci }} - {{ $empleado->nombre_completo }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Estado</label>
            <select class="form-select" name="estado">
                <option value="">Todos</option>
                <option value="presente" {{ $filtros['estado'] === 'presente' ? 'selected' : '' }}>Presente</option>
                <option value="tardanza" {{ $filtros['estado'] === 'tardanza' ? 'selected' : '' }}>Tardanza</option>
                <option value="ausente" {{ $filtros['estado'] === 'ausente' ? 'selected' : '' }}>Ausente</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </div>
        <div class="col-md-2 d-grid">
            <a href="{{ route('reportes.pdf', request()->query()) }}" class="btn btn-danger">Descargar PDF</a>
        </div>
        <div class="col-md-2 d-grid">
            <a href="{{ route('reportes.imprimir', request()->query()) }}" class="btn btn-outline-secondary" target="_blank">Imprimir/Guardar PDF</a>
        </div>
    </form>
</div>

@if($sinEmpleadoEnIndividual)
    <div class="alert alert-warning">
        Para el reporte de retrasos por empleado, selecciona un empleado especifico.
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Registros</div>
                <div class="h4 mb-0">{{ $totales->total_registros ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Presentes</div>
                <div class="h4 mb-0 text-success">{{ $totales->total_presentes ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Tardanzas</div>
                <div class="h4 mb-0 text-warning">{{ $totales->total_tardanzas ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-muted small">Ausentes</div>
                <div class="h4 mb-0 text-danger">{{ $totales->total_ausentes ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

@if($filtros['tipo_reporte'] === 'tardanza_general')
<div class="card mb-4">
    <div class="card-header bg-white"><strong>Ranking de retrasos</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
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
                <tr><td colspan="3" class="text-center text-muted py-3">Sin datos para el rango seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header bg-white"><strong>Resumen por empleado</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Empleado</th>
                    <th>Oficina</th>
                    <th>Tipo</th>
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
                    <td>{{ $r->tipo_personal }}</td>
                    <td>{{ $r->dias_registrados }}</td>
                    <td>{{ $r->presentes }}</td>
                    <td>{{ $r->tardanzas }}</td>
                    <td>{{ $r->ausentes }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-3">Sin datos para el rango seleccionado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Detalle (ultimos 300 para vista en pantalla)</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
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
                    <td>{{ $d->fecha ? \Carbon\Carbon::parse($d->fecha)->format('Y-m-d') : '-' }}</td>
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
                <tr><td colspan="9" class="text-center text-muted py-3">Sin registros.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function () {
        const periodo = document.getElementById('periodo');
        const mesWrap = document.getElementById('filtroMesWrap');
        const semanaWrap = document.getElementById('filtroSemanaWrap');
        const inicioWrap = document.getElementById('filtroInicioWrap');
        const finWrap = document.getElementById('filtroFinWrap');

        function actualizarVisibilidadPeriodos() {
            const valor = periodo.value;
            mesWrap.style.display = valor === 'mes' ? '' : 'none';
            semanaWrap.style.display = valor === 'semana' ? '' : 'none';
            inicioWrap.style.display = valor === 'rango' ? '' : 'none';
            finWrap.style.display = valor === 'rango' ? '' : 'none';
        }

        periodo.addEventListener('change', actualizarVisibilidadPeriodos);
        actualizarVisibilidadPeriodos();
    })();
</script>
@endsection
