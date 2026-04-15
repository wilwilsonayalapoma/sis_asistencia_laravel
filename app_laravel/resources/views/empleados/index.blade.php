@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Empleados</h1>
    <a class="btn btn-primary" href="{{ route('empleados.create') }}">Nuevo empleado</a>
</div>

<div class="card p-3 mb-3">
    <form class="row g-2" method="get" action="{{ route('empleados.index') }}">
        <div class="col-md-7">
            <input class="form-control" type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar por CI, nombre o apellido">
        </div>
        <div class="col-md-3">
            <select class="form-select" name="orden">
                <option value="az" {{ ($ordenListado ?? 'az') === 'az' ? 'selected' : '' }}>Orden A-Z</option>
                <option value="za" {{ ($ordenListado ?? 'az') === 'za' ? 'selected' : '' }}>Orden Z-A</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>CI</th>
                    <th>Nombre completo</th>
                    <th>Correo</th>
                    <th>Celular</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($empleados as $e)
                    <tr>
                        <td>{{ $e->ci }}</td>
                        <td>{{ $e->nombre_completo }}</td>
                        <td>{{ $e->correo ?: '-' }}</td>
                        <td>{{ $e->celular ?: '-' }}</td>
                        <td>
                            <span class="badge {{ $e->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $e->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('empleados.edit', $e) }}">Editar</a>
                            <form action="{{ route('empleados.estado', $e) }}" method="post">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-dark" type="submit">Cambiar estado</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No hay empleados registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $empleados->links('pagination::bootstrap-5') }}</div>
@endsection
