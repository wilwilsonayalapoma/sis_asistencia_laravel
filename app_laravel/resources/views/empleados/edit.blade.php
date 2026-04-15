@extends('layouts.app')

@section('title', 'Editar Empleado')

@section('content')
<h1 class="h3 mb-3">Editar empleado</h1>
<div class="card p-3">
    <form method="post" action="{{ route('empleados.update', $empleado) }}">
        @method('PUT')
        @include('empleados._form')
    </form>
</div>
@endsection
