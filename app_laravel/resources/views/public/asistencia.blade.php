<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registro de Asistencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --menu-color: #0d6efd;
            --bg-soft: #f2f4f8;
        }
        body {
            background: var(--bg-soft);
            min-height: 100vh;
        }
        .topbar {
            background: var(--menu-color);
            color: #fff;
        }
        .clock-wrap {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .08);
            max-width: 760px;
        }
        .clock-main {
            font-size: clamp(4rem, 12vw, 8rem);
            font-weight: 700;
            line-height: 1;
            letter-spacing: .05rem;
        }
        .clock-sec {
            font-size: clamp(1.2rem, 3vw, 2rem);
            font-weight: 700;
            vertical-align: super;
            margin-left: .35rem;
        }
        .logo {
            height: 44px;
            width: auto;
            object-fit: contain;
        }
        .logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.18);
            font-weight: 700;
        }
    </style>
</head>
<body>
<header class="topbar py-2">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo">
            @else
                <div class="logo-fallback">SA</div>
            @endif
            <strong>Sistema de Asistencia</strong>
        </div>
        <a href="{{ route('admin.login') }}" class="btn btn-light btn-sm" title="Ingreso administrador">
            <span class="me-1">👤</span> Admin
        </a>
    </div>
</header>

<main class="container py-5">
    <div class="clock-wrap mx-auto p-4 p-md-5 text-center">
        <div class="clock-main" id="clockMain">00:00<span class="clock-sec" id="clockSec">00</span></div>
        <div class="fs-5 text-muted mt-2" id="dateText"></div>

        <div class="mt-4">
            <label for="ci" class="form-label fs-5 fw-semibold">Numero de CI</label>
            <input id="ci" type="text" class="form-control form-control-lg text-center" placeholder="Ingresa tu numero de CI" autocomplete="off">
        </div>

        <button id="btnRegistrar" class="btn btn-primary btn-lg w-100 mt-3 py-3">Registrar asistencia</button>
    </div>
</main>

<div class="modal fade" id="resultadoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultado de registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="resultadoBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const clockMain = document.getElementById('clockMain');
    const clockSec = document.getElementById('clockSec');
    const dateText = document.getElementById('dateText');
    const ciInput = document.getElementById('ci');
    const btnRegistrar = document.getElementById('btnRegistrar');
    const resultadoBody = document.getElementById('resultadoBody');
    const resultadoModal = new bootstrap.Modal(document.getElementById('resultadoModal'));

    function actualizarReloj() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');

        clockMain.innerHTML = `${h}:${m}<span class="clock-sec" id="clockSec">${s}</span>`;
        dateText.textContent = now.toLocaleDateString('es-BO', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    setInterval(actualizarReloj, 1000);
    actualizarReloj();

    async function registrar() {
        const ci = ciInput.value.trim();
        if (!ci) {
            ciInput.focus();
            return;
        }

        btnRegistrar.disabled = true;

        try {
            const response = await fetch('{{ route('public.asistencia.registrar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ci })
            });

            const data = await response.json();

            if (!response.ok) {
                resultadoBody.innerHTML = `<div class="alert alert-danger mb-0">${data.mensaje ?? 'No se pudo registrar la asistencia.'}</div>`;
                resultadoModal.show();
                return;
            }

            let tardanza = '';
            if (data.es_tardanza) {
                tardanza = '<p class="mb-0 text-danger fw-semibold">Registrado como tardanza.</p>';
            }

            const mensajeRegistro = data.mensaje ?? 'Registro procesado correctamente.';

            resultadoBody.innerHTML = `
                <p class="mb-2"><strong>${data.nombre_completo}</strong></p>
                <p class="mb-1">${mensajeRegistro}</p>
                <p class="mb-2">Hora del marcado: <strong>${data.hora_marcado}</strong></p>
                ${tardanza}
            `;

            resultadoModal.show();
            ciInput.value = '';
        } catch (error) {
            resultadoBody.innerHTML = '<div class="alert alert-danger mb-0">Error de conexión al registrar la asistencia.</div>';
            resultadoModal.show();
        } finally {
            btnRegistrar.disabled = false;
        }
    }

    btnRegistrar.addEventListener('click', registrar);
    ciInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            registrar();
        }
    });
</script>
</body>
</html>
