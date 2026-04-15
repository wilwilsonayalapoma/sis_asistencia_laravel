<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuraciones.general*') ? 'active' : '' }}" href="{{ route('configuraciones.general') }}">General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuraciones.tipos-personal*') ? 'active' : '' }}" href="{{ route('configuraciones.tipos-personal.index') }}">Tipos de Personal</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuraciones.oficinas*') ? 'active' : '' }}" href="{{ route('configuraciones.oficinas.index') }}">Oficinas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('configuraciones.turnos*') ? 'active' : '' }}" href="{{ route('configuraciones.turnos.index') }}">Turnos</a>
    </li>
</ul>
