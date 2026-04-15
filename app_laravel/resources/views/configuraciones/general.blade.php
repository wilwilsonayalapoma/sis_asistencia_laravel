@extends('layouts.app')

@section('title', 'Configuraciones - General')

@section('content')
<h1 class="h3 mb-3">Configuraciones</h1>
@include('configuraciones._menu')

<div class="card p-3 mb-4">
    <h2 class="h5 mb-3">Configuracion General</h2>
    <form method="post" action="{{ route('configuraciones.general.update') }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
            <label class="form-label">Nombre de la institucion</label>
            <input class="form-control" type="text" name="nombre_institucion" value="{{ old('nombre_institucion', $data['nombre_institucion']) }}" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Hora limite de tardanza global</label>
            <input class="form-control" type="text" name="hora_tardanza" value="{{ old('hora_tardanza', $data['hora_tardanza']) }}" placeholder="08:30:00" required>
            <small class="text-muted">Formato HH:MM:SS</small>
        </div>

        <div class="col-12">
            <button class="btn btn-primary" type="submit">Guardar configuracion</button>
        </div>
    </form>
</div>
@endsection
