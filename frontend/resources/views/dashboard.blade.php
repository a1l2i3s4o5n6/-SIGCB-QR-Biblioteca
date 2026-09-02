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
        @include('dashboard._personal')
    @endif
</x-app-layout>