@php
    $actividad = $resumen['actividadReciente'] ?? [];

    $mapaIconos = [
        'PRESTAMO'        => ['fa-exchange-alt', 'bg-primary-50 text-primary-400'],
        'LIBRO'           => ['fa-book', 'bg-blue-50 text-blue-500'],
        'RESERVA'         => ['fa-calendar-check', 'bg-gold-50 text-gold-400'],
        'SANCION'         => ['fa-ban', 'bg-red-50 text-red-500'],
        'MULTA'           => ['fa-money-bill-wave', 'bg-amber-50 text-amber-500'],
        'CÓDIGO QR'       => ['fa-qrcode', 'bg-indigo-50 text-indigo-500'],
        'NOTIFICACION'    => ['fa-bell', 'bg-purple-50 text-purple-500'],
        'USUARIO'         => ['fa-user-plus', 'bg-teal-50 text-teal-500'],
    ];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-stream text-primary-400 mr-2"></i> Actividad reciente del sistema
        </h3>
        <span class="text-xs text-gray-400">{{ count($actividad) }} eventos</span>
    </div>

    @if (count($actividad) > 0)
        <ul class="divide-y divide-gray-100 px-5">
            @foreach ($actividad as $evento)
                @php
                    [$icon, $color] = $mapaIconos[$evento['entidad']] ?? ['fa-circle', 'bg-gray-100 text-gray-400'];
                @endphp
                <li class="py-3 flex items-center gap-3">
                    <span class="w-9 h-9 rounded-full {{ $color }} flex items-center justify-center shrink-0">
                        <i class="fas {{ $icon }} text-sm"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-700 truncate">
                            <span class="text-primary-400 font-bold uppercase text-xs">{{ $evento['entidad'] }}</span>
                            · {{ $evento['detalle'] ?? $evento['accion'] ?? 'Actividad registrada' }}
                        </p>
                        <p class="text-[11px] text-gray-400 mt-0.5">
                            {{ $evento['usuarioNombre'] ?? 'Sistema' }} · {{ \Carbon\Carbon::parse($evento['createdAt'])->diffForHumans() }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="px-5 py-3 border-t border-gray-100">
            <p class="text-[11px] leading-relaxed text-gray-400">
                Este panel reúne la actividad real registrada por el sistema. Los eventos de inicio de sesión (LOGIN) no se muestran para conservar relevancia.
            </p>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <i class="fas fa-stream text-gray-200 text-4xl mb-3"></i>
            <p class="text-sm text-gray-500">Aún no hay actividad registrada en el sistema.</p>
        </div>
    @endif
</div>