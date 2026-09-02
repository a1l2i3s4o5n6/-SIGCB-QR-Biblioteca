@php
    $rol = $resumen['rol'] ?? session('rol');
    $esAdmin = $rol === 'ADMIN';
    $accesos = [];

    if ($rol === 'BIBLIOTECARIO' || $esAdmin) {
        $accesos[] = ['Registrar préstamo', 'fa-arrow-right', 'bg-primary-50 text-primary-400', route('prestamos.index')];
        $accesos[] = ['Registrar devolución', 'fa-undo-alt', 'bg-blue-50 text-blue-500', route('prestamos.index')];
        $accesos[] = ['Registrar libro', 'fa-book', 'bg-indigo-50 text-indigo-500', route('catalogo.create')];
        $accesos[] = ['Crear reserva vinculada', 'fa-calendar-check', 'bg-gold-50 text-gold-400', route('prestamos.create')];
        $accesos[] = ['Generar código QR', 'fa-qrcode', 'bg-purple-50 text-purple-500', route('qr-codigos.index')];
        $accesos[] = ['Registrar sanción', 'fa-ban', 'bg-red-50 text-red-500', route('sanciones.index')];
    }
    if ($esAdmin) {
        $accesos[] = ['Registrar usuario', 'fa-user-plus', 'bg-teal-50 text-teal-500', route('usuarios.index')];
    }
    if ($rol === 'BIBLIOTECARIO' || $esAdmin) {
        $accesos[] = ['Generar reporte', 'fa-file-alt', 'bg-amber-50 text-amber-500', route('reportes.index')];
    }

    if (count($accesos) === 0) { $accesos = [[ 'Explorar catálogo', 'fa-search', 'bg-primary-50 text-primary-400', route('catalogo.index') ]]; }
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 flex items-center">
            <i class="fas fa-arrow-right text-primary-400 mr-2"></i> Accesos rápidos
        </h3>
    </div>
    <ul class="px-3 py-2 space-y-1">
        @foreach ($accesos as [$label, $icon, $color, $url])
            <li>
                <a href="{{ $url }}" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 transition">
                    <span class="w-8 h-8 rounded-lg {{ $color }} flex items-center justify-center">
                        <i class="fas {{ $icon }} text-xs"></i>
                    </span>
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                    <i class="fas fa-chevron-right text-gray-300 text-xs ml-auto"></i>
                </a>
            </li>
        @endforeach
    </ul>
</div>