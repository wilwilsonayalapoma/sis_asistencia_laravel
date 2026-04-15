<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Asistencia')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .navbar-brand { font-weight: 700; }
        .card { border: 0; box-shadow: 0 0.35rem 0.8rem rgba(0,0,0,.05); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Asistencia</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('marcado.index') }}">Panel de Marcado</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('empleados.index') }}">Empleados</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('reportes.index') }}">Reportes</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('configuraciones.index') }}">Configuraciones</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2 text-white">
                <span class="small">{{ auth()->user()->name ?? 'Administrador' }}</span>
                <form method="post" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light">Salir</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-warning mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
