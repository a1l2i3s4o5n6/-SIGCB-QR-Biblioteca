@php
    $estado = $resumen['estadoSistema'] ?? [];
    $items = [
        ['baseDeDatosOperativa', 'Base de datos', 'fa-database', 'PostgreSQL'],
        ['apiOperativa', 'API del sistema', 'fa-server', 'REST · JWT'],
        ['qrOperativo', 'Módulo QR', 'fa-qrcode', 'Validación'],
        ['respaldoDisponible', 'Respaldo de datos', 'fa-archive', $estado['ultimoRespaldo'] ?? 'No disponible'],
    ];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-heartbeat text-primary-400 mr-2"></i> Estado del sistema
        </h3>
    </div>
    <ul class="divide-y divide-gray-100 px-5 py-1">
        @foreach ($items as [$campo, $label, $icon, $detalle])
            @php
                $ok = (bool)($estado[$campo] ?? false);
                $esRespaldo = $campo === 'respaldoDisponible';
            @endphp
            <li class="py-3 flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-gray-50 flex items-center justify-center shrink-0">
                    <i class="fas {{ $icon }} text-gray-400"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-700">{{ $label }}</p>
                    <p class="text-[11px] text-gray-400">{{ $detalle }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-medium
                    {{ $ok ? 'text-green-600' : 'text-red-500' }}">
                    <span class="w-2 h-2 rounded-full {{ $ok ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    {{ $ok ? 'Operativo' : ($esRespaldo ? 'No disponible' : 'Caído') }}
                </span>
            </li>
        @endforeach
    </ul>
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/60">
        <p class="text-[11px] text-gray-400">
            Última actualización: {{ \Carbon\Carbon::parse($resumen['generadoEl'] ?? now())->diffForHumans() }} ·
            <span class="text-gray-500">rango {{ $resumen['desde'] ?? '—' }} → {{ $resumen['hasta'] ?? '—' }}</span>
        </p>
    </div>
</div>