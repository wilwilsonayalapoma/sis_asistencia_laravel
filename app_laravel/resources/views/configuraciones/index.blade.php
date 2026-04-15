@extends('layouts.app')

@section('title', 'Configuraciones')

@section('content')
<h1 class="h3 mb-3">Configuraciones</h1>

<div class="card p-3 mb-4">
    <form method="post" action="{{ route('configuraciones.update') }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">Nombre de la institucion</label>
            <input class="form-control" type="text" name="nombre_institucion" value="{{ old('nombre_institucion', $data['nombre_institucion']) }}" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Hora limite de tardanza</label>
            <input class="form-control" type="text" name="hora_tardanza" value="{{ old('hora_tardanza', $data['hora_tardanza']) }}" placeholder="08:30:00" required>
            <small class="text-muted">Formato HH:MM:SS</small>
        </div>

        <div class="col-12">
            <button class="btn btn-primary" type="submit">Guardar configuracion</button>
        </div>
    </form>
</div>

<div class="card p-3 mb-4">
    <h2 class="h5 mb-3">Crear Tipo de Personal</h2>
    <form method="post" action="{{ route('configuraciones.tipos-personal.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-8">
            <label class="form-label">Nombre del tipo</label>
            <input class="form-control" type="text" name="tipo" value="{{ old('tipo') }}" placeholder="Ejemplo: Supervisión" required>
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

<div class="card p-3 mt-4 mb-4">
    <h2 class="h5 mb-3">Crear Oficina</h2>
    <form method="post" action="{{ route('configuraciones.oficinas.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-5">
            <label class="form-label">Nombre de oficina</label>
            <input class="form-control" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ejemplo: Planificación" required>
        </div>
        <div class="col-md-5">
            <label class="form-label">Descripción (opcional)</label>
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
                    <th>Descripción</th>
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

<div class="card p-3 mt-4 mb-4">
    <h2 class="h5 mb-3">Crear Turno</h2>
    <form method="post" action="{{ route('configuraciones.turnos.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label">Nombre del turno</label>
            <input class="form-control" type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ejemplo: Mañana" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora de entrada</label>
            <input class="form-control" type="time" name="hora_entrada" value="{{ old('hora_entrada') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora de tardanza</label>
            <input class="form-control" type="time" name="hora_tardanza" value="{{ old('hora_tardanza') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Hora de salida (opcional)</label>
            <input class="form-control" type="time" name="hora_salida" value="{{ old('hora_salida') }}">
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-primary" type="submit">Agregar</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header bg-white"><strong>Turnos</strong></div>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Entrada</th>
                    <th>Tardanza</th>
                    <th>Salida</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($turnos as $turno)
                <tr>
                    <td>{{ $turno->id }}</td>
                    <td>{{ $turno->nombre }}</td>
                    <td>{{ $turno->hora_entrada }}</td>
                    <td>{{ $turno->hora_tardanza }}</td>
                    <td>{{ $turno->hora_salida ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $turno->estado ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $turno->estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td>
                        <form method="post" action="{{ route('configuraciones.turnos.estado', $turno) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-dark" type="submit">Cambiar estado</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No hay turnos registrados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
