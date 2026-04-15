@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nombre</label>
        <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $empleado->nombre ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Apellido paterno</label>
        <input type="text" class="form-control" name="paterno" value="{{ old('paterno', $empleado->paterno ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Apellido materno</label>
        <input type="text" class="form-control" name="materno" value="{{ old('materno', $empleado->materno ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Cedula de identidad</label>
        <input type="text" class="form-control" name="ci" value="{{ old('ci', $empleado->ci ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Correo</label>
        <input type="email" class="form-control" name="correo" value="{{ old('correo', $empleado->correo ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Celular</label>
        <input type="text" class="form-control" name="celular" value="{{ old('celular', $empleado->celular ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select class="form-select" name="estado" required>
            <option value="1" {{ old('estado', $empleado->estado ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $empleado->estado ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>

    @if(!empty($mostrarAsignacion))
        <div class="col-12 mt-2">
            <hr>
            <h5 class="mb-2">Asignacion vigente</h5>
            <p class="text-muted mb-0">Define oficina, tipo y turno para habilitar el marcado del empleado.</p>
        </div>

        <div class="col-md-4">
            <label class="form-label">Oficina</label>
            <select class="form-select" name="oficina_id" required>
                <option value="">Seleccione una oficina</option>
                @foreach($oficinas as $oficina)
                    <option value="{{ $oficina->id }}" {{ old('oficina_id', $asignacionVigente->oficina_id ?? '') == $oficina->id ? 'selected' : '' }}>
                        {{ $oficina->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Tipo de personal</label>
            <select class="form-select" name="tipo_personal_id" required>
                <option value="">Seleccione un tipo</option>
                @foreach($tiposPersonal as $tipo)
                    <option value="{{ $tipo->id }}" {{ old('tipo_personal_id', $asignacionVigente->tipo_personal_id ?? '') == $tipo->id ? 'selected' : '' }}>
                        {{ $tipo->tipo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Turno</label>
            <select class="form-select" name="turno_id" required>
                <option value="">Seleccione un turno</option>
                @foreach($turnos as $turno)
                    <option value="{{ $turno->id }}" {{ old('turno_id', $asignacionVigente->turno_id ?? '') == $turno->id ? 'selected' : '' }}>
                        {{ $turno->nombre }} ({{ substr($turno->hora_entrada, 0, 5) }} - {{ $turno->hora_salida ? substr($turno->hora_salida, 0, 5) : 'Sin salida' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label class="form-label">Fecha de inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" value="{{ old('fecha_inicio', optional($asignacionVigente->fecha_inicio ?? null)->toDateString() ?? now()->toDateString()) }}" required>
        </div>
    @endif
</div>
<div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-outline-secondary" href="{{ route('empleados.index') }}">Cancelar</a>
</div>
