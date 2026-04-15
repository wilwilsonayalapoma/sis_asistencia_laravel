@extends('layouts.app')

@section('title', 'Panel de Marcado')

@section('content')
<h1 class="h3 mb-3">Panel de Marcado</h1>

<div class="card p-3 mb-4">
    <form action="{{ route('marcado.procesar') }}" method="post" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Cedula de identidad (CI)</label>
            <input type="text" name="ci" class="form-control form-control-lg" required autofocus>
        </div>
        <div class="col-md-3">
            <label class="form-label">Accion</label>
            <select name="accion" class="form-select form-select-lg" required>
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-lg w-100" type="submit">Registrar</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong>Registros de hoy</strong>
            <form method="get" action="{{ route('marcado.index') }}" class="d-flex align-items-center gap-2">
                <input type="hidden" name="apellido" value="{{ $apellidoBusqueda ?? '' }}">
                <label class="form-label mb-0" for="orden">Orden</label>
                <select id="orden" name="orden" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="desc" {{ ($ordenListado ?? 'desc') === 'desc' ? 'selected' : '' }}>Mas reciente primero</option>
                    <option value="asc" {{ ($ordenListado ?? 'desc') === 'asc' ? 'selected' : '' }}>Mas antiguo primero</option>
                </select>
            </form>
        </div>

        <form method="get" action="{{ route('marcado.index') }}" class="row g-2 align-items-center">
            <input type="hidden" name="orden" value="{{ $ordenListado ?? 'desc' }}">
            <div class="col-md-10">
                <input
                    type="text"
                    name="apellido"
                    class="form-control form-control-sm"
                    placeholder="Buscar trabajador por apellido (paterno o materno)"
                    value="{{ $apellidoBusqueda ?? '' }}"
                >
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-primary btn-sm" type="submit">Buscar</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
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
            @forelse($registros as $row)
                <tr>
                    <td>{{ $row->personal->ci ?? '-' }}</td>
                    <td>{{ $row->personal->nombre_completo ?? '-' }}</td>
                    <td>{{ $row->asignacion->oficina->nombre ?? '-' }}</td>
                    <td>{{ optional($row->entrada)->format('H:i:s') }}</td>
                    <td>{{ optional($row->salida)->format('H:i:s') }}</td>
                    <td><span class="badge text-bg-secondary">{{ $row->estado }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Sin marcados hoy.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
