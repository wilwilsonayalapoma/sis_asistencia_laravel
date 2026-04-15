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
    <div class="card-header bg-white"><strong>Registros de hoy</strong></div>
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
