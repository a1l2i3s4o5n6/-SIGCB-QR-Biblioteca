@php
    $k = $resumen['kpis'] ?? [];
    $sA = $k['sancionesActivas'] ?? 0;
    $sV = $k['sancionesVencidas'] ?? 0;
    $sP = $k['sancionesProximas'] ?? 0;
    $sR = $k['sancionesResueltas'] ?? 0;
    $sN = $k['sancionesNuevasPeriodo'] ?? 0;
    $mP = $k['multasPendientes'] ?? 0;
    $mT = $k['totalMultasPendientes'] ?? 0;
    $mG = $k['multasGeneradasPeriodo'] ?? 0;
    $mPa = $k['multasPagadasPeriodo'] ?? 0;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-ban text-red-500 mr-2"></i> Sanciones y multas
        </h3>
        <a href="{{ route('sanciones.index') }}" class="text-xs text-primary-400 hover:text-primary-500 font-medium">
            Ir a Sanciones <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>

    <div class="p-5">
        {{-- Indicadores --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-5">
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                <p class="text-xs text-amber-700 font-medium">Activas</p>
                <p class="text-2xl font-bold text-amber-700">{{ number_format($sA) }}</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                <p class="text-xs text-red-700 font-medium">Vencidas</p>
                <p class="text-2xl font-bold text-red-700">{{ number_format($sV) }}</p>
            </div>
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                <p class="text-xs text-yellow-700 font-medium">Próximas a vencer</p>
                <p class="text-2xl font-bold text-yellow-700">{{ number_format($sP) }}</p>
            </div>
            <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                <p class="text-xs text-green-700 font-medium">Resueltas</p>
                <p class="text-2xl font-bold text-green-700">{{ number_format($sR) }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs text-gray-600 font-medium">Multas pendientes</p>
                <p class="text-2xl font-bold text-gray-800">${{ number_format($mT, 2) }}</p>
                <p class="text-[11px] text-gray-500">{{ $mP }} multa(s) por cobrar</p>
            </div>
        </div>

        {{-- Alertas prioritarias con texto e icono --}}
        <div class="space-y-2.5">
            @if ($sV > 0)
                <div class="flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-red-700">{{ $sV }} sanción(es) están vencidas y requieren revisión.</p>
                        <a href="{{ route('sanciones.index') }}" class="text-xs text-red-600 underline">Ver sanciones</a>
                    </div>
                </div>
            @endif
            @if ($sP > 0)
                <div class="flex items-start gap-3 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3">
                    <i class="fas fa-clock text-yellow-600 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-yellow-700">{{ $sP }} sanción(es) próximas a vencer.</p>
                        <a href="{{ route('sanciones.index') }}" class="text-xs text-yellow-700 underline">Ver sanciones</a>
                    </div>
                </div>
            @endif
            @if ($sA > 0 && $sV === 0 && $sP === 0)
                <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
                    <i class="fas fa-ban text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-amber-700">{{ $sA }} usuario(s) tienen sanción(es) pendiente(s) de resolución.</p>
                        <a href="{{ route('sanciones.index') }}" class="text-xs text-amber-700 underline">Ver sanciones</a>
                    </div>
                </div>
            @endif
            @if ($mP > 0)
                <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
                    <i class="fas fa-money-bill-wave text-blue-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-blue-700">{{ $mP }} multa(s) pendiente(s) de pago por ${{ number_format($mT, 2) }}.</p>
                        <a href="{{ route('multas.index') }}" class="text-xs text-blue-700 underline">Ver multas</a>
                    </div>
                </div>
            @endif
            @if ($sR > 0)
                <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                    <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-green-700">{{ $sR }} sanción(es) resueltas.</p>
                        <a href="{{ route('sanciones.index') }}" class="text-xs text-green-700 underline">Ver sanciones</a>
                    </div>
                </div>
            @endif
            @if ($sA === 0 && $sV === 0 && $sP === 0 && $mP === 0)
                <div class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <p class="text-sm font-medium text-green-700">✓ Todo está en orden. No hay sanciones ni multas pendientes.</p>
                </div>
            @endif
        </div>

        <p class="text-[11px] text-gray-400 mt-4">
            {{ $sN }} sanción(es) creada(s) en el período · {{ $mG }} multa(s) generada(s) · {{ $mPa }} pagada(s).
        </p>
    </div>
</div>