@extends('layouts.app')

@section('title', 'Configuraciones - Tipos de Personal')

@section('content')
<h1 class="h3 mb-3">Configuraciones</h1>
@include('configuraciones._menu')

<div class="card p-3 mb-4">
    <h2 class="h5 mb-3">Crear Tipo de Personal</h2>
    <form method="post" action="{{ route('configuraciones.tipos-personal.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-8">
            <label class="form-label">Nombre del tipo</label>
            <input class="form-control" type="text" name="tipo" value="{{ old('tipo') }}" placeholder="Ejemplo: Supervision" required>
        </div>
        <div class="col-md-4 d-grid">
            <button class="btn btn-primary" type="submit">Agregar tipo</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Tipos de Personal</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($tiposPersonal as $tp)
                <tr>
                    <td>{{ $tp->id }}</td>
                    <td>{{ $tp->tipo }}</td>
                    <td>
                        <span class="badge {{ $tp->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $tp->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <form method="post" action="{{ route('configuraciones.tipos-personal.estado', $tp) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-dark" type="submit">Cambiar estado</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No hay tipos de personal registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
