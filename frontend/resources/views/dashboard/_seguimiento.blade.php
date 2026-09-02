@php
    $k = $resumen['kpis'] ?? [];
    $estadoPrestamos = [
        ['ACTIVO', 'fa-exchange-alt', 'bg-green-50 text-green-600', $k['prestamosActivos'] ?? 0, route('prestamos.index')],
        ['VENCIDO', 'fa-hourglass-end', 'bg-red-50 text-red-500', $k['prestamosVencidos'] ?? 0, route('prestamos.index')],
        ['RESERVADO', 'fa-bookmark', 'bg-blue-50 text-blue-500', $k['prestamosReservados'] ?? 0, route('prestamos.index')],
        ['DEVUELTO', 'fa-undo', 'bg-gray-100 text-gray-500', $k['prestamosDevueltos'] ?? 0, route('prestamos.index')],
    ];
    $proximoVencimiento = (($k['prestamosDevueltosPeriodo'] ?? 0) > 0) ? $k['prestamosDevueltosPeriodo'] . ' devolucione(s) registradas en el período' : null;
    $reservas = [
        ['PENDIENTE', 'fa-hourglass-half', 'bg-amber-50 text-amber-600', $k['reservasPendientes'] ?? 0],
        ['CONFIRMADA', 'fa-check-circle', 'bg-emerald-50 text-emerald-600', $k['reservasConfirmadas'] ?? 0],
        ['COMPLETADA', 'fa-check-double', 'bg-green-100 text-green-700', $k['reservasCompletadas'] ?? 0],
        ['CANCELADA', 'fa-times', 'bg-gray-100 text-gray-400', $k['reservasCanceladas'] ?? 0],
    ];
    $usuariosPorRol = $k['usuariosPorRol'] ?? [];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    {{-- Seguimiento de préstamos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-exchange-alt text-primary-400 mr-2"></i> Préstamos</h3>
            <a href="{{ route('prestamos.index') }}" class="text-xs text-primary-400 hover:text-primary-500">Ver todo</a>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 gap-3">
                @foreach ($estadoPrestamos as [$label, $icon, $color, $cant, $url])
                    <a href="{{ $url }}" class="rounded-lg border border-gray-100 p-3 hover:border-primary-200 transition">
                        <span class="inline-flex items-center gap-2 text-xs text-gray-500">
                            <i class="fas {{ $icon }} {{ str_contains($color, 'text-red') ? 'text-red-500' : 'text-gray-400' }}"></i>{{ $label }}
                        </span>
                        <p class="text-xl font-bold text-gray-800 mt-0.5">{{ number_format($cant) }}</p>
                    </a>
                @endforeach
            </div>
            @if ($proximoVencimiento)
                <p class="text-[11px] text-gray-400 mt-3">{{ $proximoVencimiento }}</p>
            @endif
            @if (($k['prestamosVencidos'] ?? 0) > 0)
                <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    Hay {{ $k['prestamosVencidos'] }} entrega(s) con retraso para gestionar.
                </div>
            @endif
        </div>
    </div>

    {{-- Pipeline de reservas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-calendar-check text-gold-400 mr-2"></i> Reservas</h3>
            <a href="{{ route('prestamos.index') }}" class="text-xs text-primary-400 hover:text-primary-500">Crear</a>
        </div>
        <div class="p-5 space-y-2.5">
            @foreach ($reservas as [$label, $icon, $color, $cant])
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-2 text-sm text-gray-600">
                        <span class="w-8 h-8 rounded-lg {{ $color }} flex items-center justify-center"><i class="fas {{ $icon }} text-xs"></i></span>
                        {{ $label }}
                    </span>
                    <span class="text-sm font-bold text-gray-800">{{ number_format($cant) }}</span>
                </div>
            @endforeach
            <p class="text-[11px] text-gray-400 pt-1">Todas las reservas se convierten en préstamos al confirmarse.</p>
        </div>
    </div>

    {{-- Usuarios registrados --}}
    @php $esAdmin = ($resumen['rol'] ?? '') === 'ADMIN'; @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden md:col-span-2 lg:col-span-1">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800"><i class="fas fa-users text-primary-400 mr-2"></i> Usuarios</h3>
            <a href="{{ $esAdmin ? route('usuarios.index') : route('catalogo.index') }}" class="text-xs text-primary-400 hover:text-primary-500">Ver detalle</a>
        </div>
        <div class="p-5">
            <p class="text-3xl font-bold text-gray-800">{{ number_format($k['usuariosRegistrados'] ?? 0) }}</p>
            <p class="text-xs text-gray-400 mb-4">{{ $k['usuariosNuevosPeriodo'] ?? 0 }} nuevos en el período · {{ $k['usuariosActivos'] ?? 0 }} activos</p>
            <ul class="space-y-2">
                @foreach (($usuariosPorRol ?: []) as $u)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 capitalize">{{ strtolower(str_replace('_', ' ', $u['rol'])) }}</span>
                        <span class="flex items-center gap-2">
                            <span class="font-medium text-gray-700">{{ $u['cantidad'] }}</span>
                            <span class="w-16 rounded-full bg-gray-100 h-1.5 overflow-hidden">
                                <span class="block h-full rounded-full bg-primary-400" style="width: {{ min(100, $u['porcentaje']) }}%"></span>
                            </span>
                        </span>
                    </li>
                @endforeach
            </ul>
            @if (($k['usuariosConSancionActiva'] ?? 0) > 0)
                <div class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 flex items-center gap-2">
                    <i class="fas fa-ban"></i>
                    {{ $k['usuariosConSancionActiva'] }} usuario(s) con sanción activa.
                </div>
            @endif
        </div>
    </div>
</div>