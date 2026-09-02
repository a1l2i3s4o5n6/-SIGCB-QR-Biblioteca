@php
    $k = $resumen['kpis'] ?? [];
    $qrA = $k['qrActivos'] ?? 0;
    $qrI = $k['qrInactivos'] ?? 0;
    $qrT = $qrA + $qrI;
    $qrN = $k['qrCreadosPeriodo'] ?? 0;
    $pct = $qrT > 0 ? round(($qrA / $qrT) * 100) : 0;
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-qrcode text-primary-400 mr-2"></i> Códigos QR activos
        </h3>
        <a href="{{ route('qr-codigos.index') }}" class="text-xs text-primary-400 hover:text-primary-500 font-medium">
            Generar e imprimir QR <i class="fas fa-external-link-alt ml-1"></i>
        </a>
    </div>
    <div class="p-5">
        @if ($qrT > 0)
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex-1 min-w-[180px]">
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="text-gray-600"><i class="fas fa-circle text-green-600 text-[9px] mr-1"></i>Activos: <strong>{{ number_format($qrA) }}</strong></span>
                        <span class="text-gray-400">{{ $pct }}%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-green-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex justify-between text-sm mt-1.5">
                        <span class="text-gray-500"><i class="fas fa-circle text-gray-300 text-[9px] mr-1"></i>Inactivos: <strong>{{ number_format($qrI) }}</strong></span>
                        <span class="text-xs text-gray-400">{{ $qrN }} creados en el período</span>
                    </div>
                </div>
                <div class="text-center px-4">
                    <p class="text-3xl font-bold text-gray-800">{{ number_format($qrA) }}</p>
                    <p class="text-xs text-gray-500">QR habilitados para</p>
                    <p class="text-xs text-gray-500">validación y consulta</p>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <i class="fas fa-qrcode text-gray-200 text-4xl mb-3"></i>
                <p class="text-sm text-gray-500">Aún no se han generado códigos QR.</p>
                <a href="{{ route('qr-codigos.index') }}" class="mt-2 text-xs text-primary-400 hover:text-primary-500 font-medium">
                    Generar el primero <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endif
    </div>
</div>