@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>
    <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Empleados activos</div>
            <div class="h4 mb-0">{{ $stats['empleados_activos'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Oficinas activas</div>
            <div class="h4 mb-0">{{ $stats['oficinas_activas'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Marcados hoy</div>
            <div class="h4 mb-0">{{ $stats['marcados_hoy'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3">
            <div class="text-muted small">Tardanzas hoy</div>
            <div class="h4 mb-0">{{ $stats['tardanzas_hoy'] }}</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Ultimos movimientos de hoy</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Empleado</th>
                    <th>Oficina</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimos as $row)
                    <tr>
                        <td>{{ $row->personal->ci ?? '-' }}</td>
                        <td>{{ $row->personal->nombre_completo ?? '-' }}</td>
                        <td>{{ $row->asignacion->oficina->nombre ?? '-' }}</td>
                        <td>{{ optional($row->entrada)->format('H:i:s') }}</td>
                        <td>{{ optional($row->salida)->format('H:i:s') }}</td>
                        <td><span class="badge text-bg-secondary">{{ $row->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Sin registros hoy.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
