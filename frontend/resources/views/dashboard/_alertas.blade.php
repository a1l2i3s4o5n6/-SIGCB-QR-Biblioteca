@php
    $alertas = $resumen['alertas'] ?? [];
    $k = $resumen['kpis'] ?? [];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-flag text-red-500 mr-2"></i> Alertas del sistema
        </h3>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ count($alertas) > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
            {{ count($alertas) > 0 ? count($alertas) . ' por atender' : 'Sin pendientes' }}
        </span>
    </div>

    @if (count($alertas) > 0)
        <ul class="divide-y divide-gray-100 px-5">
            @foreach ($alertas as $alerta)
                <li class="py-3 flex items-start gap-3">
                    @if ($alerta['prioridad'] === 'ALTA')
                        <span class="mt-0.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-600">
                            <i class="fas fa-circle text-[6px]"></i> ALTA
                        </span>
                    @elseif ($alerta['prioridad'] === 'MEDIA')
                        <span class="mt-0.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-600">
                            <i class="fas fa-circle text-[6px]"></i> MEDIA
                        </span>
                    @else
                        <span class="mt-0.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-gray-100 text-gray-500">
                            <i class="fas fa-circle text-[6px]"></i> BAJA
                        </span>
                    @endif
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
                    @if (!empty($alerta['fecha']))
                        <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ \Carbon\Carbon::parse($alerta['fecha'])->diffForHumans() }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/60">
            <p class="text-[11px] text-gray-400">Además: {{ $k['prestamosVencidos'] ?? 0 }} préstamo(s) vencido(s), {{ $k['prestamosProximos24h'] ?? 0 }} por vencer en 24h, ejemplares dañados {{ $k['ejemplaresDanados'] ?? 0 }}.</p>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mb-3">
                <i class="fas fa-check text-green-500 text-lg"></i>
            </div>
            <p class="text-sm font-medium text-gray-600">Sistema al día</p>
            <p class="text-xs text-gray-400 mt-1">No hay alertas que requieran atención en este momento.</p>
        </div>
    @endif
</div>