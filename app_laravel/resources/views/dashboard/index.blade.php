@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>
    <div class="d-flex align-items-center gap-2">
        @if($stats['faltantes_hoy'] > 0)
            <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="modal" data-bs-target="#faltantesModal">
                Ver faltantes
            </button>
        @endif
        <span class="text-muted">{{ now()->format('d/m/Y H:i') }}</span>
    </div>
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
    <div class="col-md-3">
        <div class="card p-3 border border-warning-subtle">
            <div class="text-muted small">Faltantes hoy</div>
            <div class="h4 mb-0 text-warning">{{ $stats['faltantes_hoy'] }}</div>
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

<div class="modal fade" id="faltantesModal" tabindex="-1" aria-labelledby="faltantesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="faltantesModalLabel">Empleados faltantes de hoy ({{ $stats['faltantes_hoy'] }})</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>CI</th>
                                <th>Empleado</th>
                                <th>Oficina</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($faltantes as $f)
                                <tr>
                                    <td>{{ $f->ci }}</td>
                                    <td>{{ trim($f->paterno . ' ' . ($f->materno ?? '') . ' ' . $f->nombre) }}</td>
                                    <td>{{ $f->oficina }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Sin faltantes por ahora.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($stats['faltantes_hoy'] > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var faltantesModalEl = document.getElementById('faltantesModal');
    if (!faltantesModalEl) return;
    var faltantesModal = new bootstrap.Modal(faltantesModalEl);
    faltantesModal.show();
});
</script>
@endif
@endsection
