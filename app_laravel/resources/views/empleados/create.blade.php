@extends('layouts.app')

@section('title', 'Nuevo Empleado')

@section('content')
<h1 class="h3 mb-3">Nuevo empleado</h1>
<div class="card p-3">
    <form method="post" action="{{ route('empleados.store') }}">
        @include('empleados._form', ['mostrarAsignacion' => true])
    </form>
</div>
@endsection
