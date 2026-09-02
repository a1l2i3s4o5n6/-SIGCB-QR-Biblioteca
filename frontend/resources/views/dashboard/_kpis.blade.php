@php
    $k = $resumen['kpis'] ?? [];
    $esAdmin = ($resumen['rol'] ?? '') === 'ADMIN';
    $sancActivas = $k['sancionesActivas'] ?? 0;
    $sancVencidas = $k['sancionesVencidas'] ?? 0;

    $cards = [
        [
            'label' => 'Libros registrados',
            'value' => number_format($k['librosRegistrados'] ?? 0),
            'icon'  => 'fa-book',
            'color' => 'bg-primary-50 text-primary-400',
            'url'   => route('catalogo.index'),
            'sub'   => ($k['librosNuevosPeriodo'] ?? 0) > 0 ? (($k['librosNuevosPeriodo']) . ' nuevos en el período') : 'Catálogo activo',
        ],
        [
            'label' => 'Ejemplares disponibles',
            'value' => number_format($k['ejemplaresDisponibles'] ?? 0),
            'icon'  => 'fa-layer-group',
            'color' => 'bg-blue-50 text-blue-500',
            'url'   => route('catalogo.index'),
            'sub'   => ($k['ejemplaresPrestados'] ?? 0) . ' prestados' .
                        (($k['ejemplaresDanados'] ?? 0) > 0 ? ' · ' . $k['ejemplaresDanados'] . ' dañados' : ''),
        ],
        [
            'label' => 'Préstamos activos',
            'value' => number_format($k['prestamosActivos'] ?? 0),
            'icon'  => 'fa-exchange-alt',
            'color' => 'bg-green-50 text-green-600',
            'url'   => route('prestamos.index'),
            'sub'   => ($k['prestamosVencidos'] ?? 0) > 0
                            ? (($k['prestamosVencidos']) . ' vencido(s) · ' . ($k['prestamosProximos24h'] ?? 0) . ' vencen en 24h')
                            : (($k['prestamosProximos24h'] ?? 0) . ' vencen en 24h'),
            'warn'  => ($k['prestamosVencidos'] ?? 0) > 0,
        ],
        [
            'label' => 'Usuarios registrados',
            'value' => number_format($k['usuariosRegistrados'] ?? 0),
            'icon'  => 'fa-users',
            'color' => 'bg-gold-50 text-gold-400',
            'url'   => $esAdmin ? route('usuarios.index') : route('prestamos.index'),
            'sub'   => ($k['usuariosNuevosPeriodo'] ?? 0) . ' nuevos en el período · ' . ($k['usuariosActivos'] ?? 0) . ' activos',
            'disabled' => !$esAdmin,
        ],
        [
            'label' => 'Códigos QR activos',
            'value' => number_format($k['qrActivos'] ?? 0),
            'icon'  => 'fa-qrcode',
            'color' => 'bg-indigo-50 text-indigo-500',
            'url'   => route('qr-codigos.index'),
            'sub'   => ($k['qrInactivos'] ?? 0) . ' inactivo(s) · ' . ($k['qrCreadosPeriodo'] ?? 0) . ' creados en el período',
        ],
        [
            'label' => 'Sanciones activas',
            'value' => number_format($sancActivas),
            'icon'  => 'fa-ban',
            'color' => $sancVencidas > 0 ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-500',
            'url'   => route('sanciones.index'),
            'sub'   => $sancVencidas > 0
                            ? ('⚠ ' . $sancVencidas . ' requieren atención inmediata')
                            : (($k['sancionesProximas'] ?? 0) > 0 ? '⚠ ' . $k['sancionesProximas'] . ' próximas a vencer' : 'Sin pendientes urgentes'),
            'warn'  => $sancVencidas > 0,
        ],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-4 sm:gap-5 mb-6">
    @foreach ($cards as $card)
        @if (!empty($card['disabled']))
            <div class="stat-card cursor-default">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $card['value'] }}</p>
                    </div>
                    <div class="stat-icon {{ $card['color'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">{{ $card['sub'] }}</p>
            </div>
        @else
            <a href="{{ $card['url'] }}" class="stat-card block {{ !empty($card['warn']) ? 'ring-1 ring-amber-200' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                        <p class="text-2xl font-bold {{ !empty($card['warn']) ? 'text-red-600' : 'text-gray-800' }} mt-1">{{ $card['value'] }}</p>
                    </div>
                    <div class="stat-icon {{ $card['color'] }}">
                        <i class="fas {{ $card['icon'] }}"></i>
                    </div>
                </div>
                <p class="text-xs {{ !empty($card['warn']) ? 'text-red-500 font-medium' : 'text-gray-400' }} mt-2">{{ $card['sub'] }}</p>
            </a>
        @endif
    @endforeach
</div>