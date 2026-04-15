@extends('layouts.app')

@section('title', 'Configuraciones - Oficinas')

@section('content')
<h1 class="h3 mb-3">Configuraciones</h1>
@include('configuraciones._menu')

<div class="card p-3 mb-4">
    <h2 class="h5 mb-3">Crear Oficina</h2>
    <form method="post" action="{{ route('configuraciones.oficinas.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Nombre de oficina</label>
            <input class="form-control" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ejemplo: Planificacion" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Descripcion (opcional)</label>
            <input class="form-control" type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Detalle breve de la oficina">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Agregar oficina</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Oficinas</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripcion</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($oficinas as $oficina)
                <tr>
                    <td>{{ $oficina->id }}</td>
                    <td>{{ $oficina->nombre }}</td>
                    <td>{{ $oficina->descripcion ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $oficina->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $oficina->estado ? 'Activa' : 'Inactiva' }}
                        </span>
                    </td>
                    <td>
                        <form method="post" action="{{ route('configuraciones.oficinas.estado', $oficina) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-dark" type="submit">Cambiar estado</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No hay oficinas registradas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
