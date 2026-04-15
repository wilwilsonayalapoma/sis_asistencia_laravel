<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #e0f2fe);
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: 0;
            box-shadow: 0 16px 32px rgba(0, 0, 0, .12);
        }
    </style>
</head>
<body>
<div class="card login-card">
    <div class="card-body p-4">
        <h1 class="h4 mb-3">Acceso Administrador</h1>
        <p class="text-muted">Ingresa con tu usuario administrador.</p>

        @if($errors->any())
            <div class="alert alert-danger py-2">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            <a href="{{ route('public.asistencia') }}" class="btn btn-outline-secondary w-100 mt-2">Volver al marcado</a>
        </form>
    </div>
</div>
</body>
</html>
