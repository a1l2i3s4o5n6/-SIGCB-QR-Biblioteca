@php
    $k = $resumen['kpis'] ?? [];
    $alertas = $resumen['alertas'] ?? [];
    $noLeidas = $resumen['notificacionesNoLeidas'] ?? 0;

    $stats = [
        ['Mis préstamos activos', 'fa-exchange-alt', 'bg-primary-50 text-primary-400', $k['prestamosActivos'] ?? 0, route('prestamos.index')],
        ['Préstamos vencidos', 'fa-hourglass-end', 'bg-red-50 text-red-500', $k['prestamosVencidos'] ?? 0, route('prestamos.index')],
        ['Vencen en 24 horas', 'fa-clock', 'bg-amber-50 text-amber-500', $k['prestamosProximos24h'] ?? 0, route('prestamos.index')],
        ['Mis reservas', 'fa-calendar-check', 'bg-gold-50 text-gold-400', $k['reservasPendientes'] ?? 0, route('reservas.index')],
        ['Sanciones activas', 'fa-ban', 'bg-red-50 text-red-500', $k['sancionesActivas'] ?? 0, route('sanciones.index')],
        ['Multas pendientes', 'fa-money-bill-wave', 'bg-blue-50 text-blue-500', $k['multasPendientes'] ?? 0, route('multas.index')],
    ];
@endphp

{{-- Bienvenida --}}
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h2 class="text-lg font-bold text-gray-800">Hola, te damos la bienvenida a tu espacio personal</h2>
        <p class="text-sm text-gray-500 mt-0.5">Mantente al día con tus préstamos, reservas y notificaciones.</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $noLeidas > 0 ? 'bg-gold-50 text-gold-400' : 'bg-gray-50 text-gray-400' }}">
            <i class="fas fa-bell"></i>
            @if ($noLeidas > 0)
                {{ $noLeidas }} notificación(es) sin leer
            @else
                Sin notificaciones nuevas
            @endif
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-600">
            <i class="fas fa-book"></i>{{ number_format($k['librosDisponibles'] ?? 0) }} libros disponibles
        </span>
    </div>
</div>

{{-- Indicadores personales --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4 sm:gap-5 mb-6">
    @foreach ($stats as [$label, $icon, $color, $valor, $url])
        <a href="{{ $url }}" class="stat-card block">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($valor) }}</p>
                </div>
                <div class="stat-icon {{ $color }}">
                    <i class="fas {{ $icon }}"></i>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Consultar detalle <i class="fas fa-arrow-right ml-1"></i></p>
        </a>
    @endforeach
</div>

{{-- Catálogo --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-book-open text-primary-400 mr-2"></i> Encontrar un libro
        </h3>
        <a href="{{ route('catalogo.index') }}" class="text-xs text-primary-400 hover:text-primary-500 font-medium">
            Explorar catálogo <i class="fas fa-external-link-alt ml-1"></i>
        </a>
    </div>
    <div class="p-5 flex items-center gap-4">
        <span class="w-12 h-12 rounded-xl bg-primary-50 text-primary-400 flex items-center justify-center">
            <i class="fas fa-search text-lg"></i>
        </span>
        <div>
            <p class="text-sm text-gray-600">Consulta los <strong>{{ number_format($k['librosRegistrados'] ?? 0) }}</strong> títulos del catálogo, reserva o solicita en préstamo</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $k['librosDisponibles'] ?? 0 }} títulos con ejemplares disponibles ahora mismo.</p>
        </div>
    </div>
</div>

{{-- Alertas personales --}}
@if (count($alertas) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-flag text-red-500 mr-2"></i> Tienes alertas que atender
            </h3>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600">{{ count($alertas) }} </span>
        </div>
        <ul class="divide-y divide-gray-100 px-5">
            @foreach ($alertas as $alerta)
                <li class="py-3 flex items-start gap-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold
                        {{ $alerta['prioridad'] === 'CRITICA' ? 'bg-red-50 text-red-600' : ($alerta['prioridad'] === 'ALTA' ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-500') }}">
                        <i class="fas fa-circle text-[6px]"></i>
                        {{ $alerta['prioridad'] }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700">{{ $alerta['descripcion'] }}</p>
                        @if (!empty($alerta['detalle']))
                            <p class="text-xs text-gray-500 mt-0.5">{{ $alerta['detalle'] }}</p>
                        @endif
                        @if (!empty($alerta['url']))
                            <a href="{{ $alerta['url'] }}" class="text-xs text-primary-400 hover:text-primary-500 font-medium inline-flex items-center mt-1">
                                {{ $alerta['accion'] ?? 'Ver detalle' }} <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2">
        @include('dashboard._actividad')
    </div>
    <div class="space-y-6">
        @include('dashboard._estado')
        @include('dashboard._accesos')
    </div>
</div>