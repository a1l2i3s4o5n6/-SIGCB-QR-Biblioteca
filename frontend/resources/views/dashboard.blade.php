<x-app-layout>
    @php
        $rol = $resumen['rol'] ?? session('rol', 'ESTUDIANTE');
        $staff = in_array($rol, ['ADMIN', 'BIBLIOTECARIO'], true);
        $kpis = $resumen['kpis'] ?? [];
        $alertas = $resumen['alertas'] ?? [];
        $actividad = $resumen['actividadReciente'] ?? [];
        $estado = $resumen['estadoSistema'] ?? [];
        $desde = $resumen['desde'] ?? null;
        $hasta = $resumen['hasta'] ?? null;
        $hoy = now()->format('Y-m-d');
        if ($desde === null || $hasta === null) { $preset = '30'; }
        elseif ($desde === $hoy && $hasta === $hoy) { $preset = 'hoy'; }
        elseif ($desde === now()->subDays(6)->format('Y-m-d') && $hasta === $hoy) { $preset = '7'; }
        elseif ($desde === now()->subDays(29)->format('Y-m-d') && $hasta === $hoy) { $preset = '30'; }
        elseif ($desde === now()->startOfMonth()->format('Y-m-d') && $hasta === $hoy) { $preset = 'mes'; }
        elseif ($desde === now()->startOfYear()->format('Y-m-d') && $hasta === $hoy) { $preset = 'anno'; }
        else { $preset = 'personalizado'; }
    @endphp

    <x-slot name="header">
        @include('dashboard._encabezado')
    </x-slot>

    @if ($staff)
        @include('dashboard._kpis')
        @include('dashboard._graficos')
        @include('dashboard._sanciones_multas')
        @include('dashboard._qr')
        @include('dashboard._alertas')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                @include('dashboard._actividad')
            </div>
            <div class="space-y-6">
                @include('dashboard._estado')
                @include('dashboard._accesos')
            </div>
        </div>
        @include('dashboard._seguimiento')
    @else
        {{-- Panel QR para estudiante --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary-100 text-primary-600">
                    <i class="fas fa-qrcode text-lg"></i>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-gray-800">¿Buscas un libro?</h2>
                    <p class="text-xs text-gray-500">Escanea el código QR del libro para verlo y reservarlo.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                <div class="bg-primary-50 border border-primary-100 rounded-lg px-4 py-3 text-center">
                    <span class="block text-lg font-bold text-primary-600">1</span>
                    <p class="text-xs text-gray-600 mt-0.5">Escanea el QR del libro</p>
                </div>
                <div class="bg-primary-50 border border-primary-100 rounded-lg px-4 py-3 text-center">
                    <span class="block text-lg font-bold text-primary-600">2</span>
                    <p class="text-xs text-gray-600 mt-0.5">Pulsa "Reservar este libro"</p>
                </div>
                <div class="bg-primary-50 border border-primary-100 rounded-lg px-4 py-3 text-center">
                    <span class="block text-lg font-bold text-primary-600">3</span>
                    <p class="text-xs text-gray-600 mt-0.5">Recógelo en biblioteca</p>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-4">
                Tu reserva queda <strong>pendiente</strong> hasta que el personal la atienda y registre el préstamo en el mostrador.
                Recibirás una notificación de confirmación.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('estudiante.escaneo-qr') }}"
                    class="btn-primary-custom inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-qrcode mr-2"></i> Escanear código QR
                </a>
                <a href="{{ route('reservas.index') }}"
                    class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition">
                    <i class="fas fa-list mr-2 text-gray-400"></i> Mis solicitudes
                </a>
            </div>
        </div>

        @include('dashboard._personal')

        @if (!empty($misReservas['content']))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-check text-gold-400 mr-2"></i> Tus solicitudes recientes
                    </h3>
                    <a href="{{ route('reservas.index') }}"
                        class="text-xs text-primary-400 hover:text-primary-500 font-medium">
                        Ver todas <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach (array_slice($misReservas['content'], 0, 5) as $r)
                        <li class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-700 truncate">{{ $r['libroTitulo'] }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Solicitada {{ \Carbon\Carbon::parse($r['fechaReserva'])->diffForHumans() }}
                                </p>
                            </div>
                            <span class="shrink-0 px-2.5 py-1 rounded-full text-[11px] font-bold
                                {{ $r['estado'] === 'PENDIENTE' ? 'bg-amber-50 text-amber-600' : ($r['estado'] === 'COMPLETADA' ? 'bg-green-50 text-green-600' : ($r['estado'] === 'CANCELADA' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500')) }}">
                                {{ $r['estado'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</x-app-layout>