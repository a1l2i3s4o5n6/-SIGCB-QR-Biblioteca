<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detalle de Préstamo</h1>
                <p class="text-sm text-gray-500 mt-0.5">Préstamo #{{ $prestamo['id'] ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('prestamos.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) && ($prestamo['estado'] ?? '') === 'ACTIVO')
                    <form method="POST" action="{{ route('prestamos.renovar', $prestamo['id']) }}"
                        onsubmit="return confirm('¿Renovar este préstamo por 7 días más?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 shadow transition">
                            <i class="fas fa-sync-alt mr-2"></i> Renovar
                        </button>
                    </form>
                    <form method="POST" action="{{ route('prestamos.devolver', $prestamo['id']) }}"
                        onsubmit="return confirm('¿Registrar devolución de este préstamo?')">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                            class="btn-primary-custom px-4 py-2 rounded-lg text-sm font-semibold text-white shadow">
                            <i class="fas fa-undo mr-2"></i> Devolver
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 flex items-center justify-between px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info principal -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary-400 mr-2"></i>
                    Información del préstamo
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Usuario</p>
                    <p class="text-sm text-gray-700">{{ $prestamo['usuarioNombre'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Libro</p>
                    <p class="text-sm text-gray-700">{{ $prestamo['libroTitulo'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Código de ejemplar</p>
                    <p class="text-sm text-gray-700">
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-[11px] font-mono rounded">{{ $prestamo['codigoEjemplar'] ?? '—' }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Estado</p>
                    @php
                        $color = match($prestamo['estado'] ?? '') {
                            'ACTIVO' => 'bg-green-50 text-green-700',
                            'DEVUELTO' => 'bg-blue-50 text-blue-700',
                            'VENCIDO' => 'bg-red-50 text-red-700',
                            'RENOVADO' => 'bg-gold-50 text-gold-400',
                            default => 'bg-yellow-50 text-yellow-700',
                        };
                    @endphp
                    <span class="px-2 py-1 {{ $color }} text-[11px] font-medium rounded-full">{{ $prestamo['estado'] ?? '—' }}</span>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Fecha de inicio</p>
                    <p class="text-sm text-gray-700">{{ $prestamo['fechaPrestamo'] ? \Carbon\Carbon::parse($prestamo['fechaPrestamo'])->format('d/m/Y H:i') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Fecha de vencimiento</p>
                    <p class="text-sm text-gray-700">{{ $prestamo['fechaVencimiento'] ? \Carbon\Carbon::parse($prestamo['fechaVencimiento'])->format('d/m/Y H:i') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Fecha de devolución</p>
                    <p class="text-sm text-gray-700">{{ $prestamo['fechaDevolucion'] ? \Carbon\Carbon::parse($prestamo['fechaDevolucion'])->format('d/m/Y H:i') : 'Pendiente' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Días restantes</p>
                    @php
                        $vencido = ($prestamo['estado'] ?? '') === 'VENCIDO';
                        $activo = ($prestamo['estado'] ?? '') === 'ACTIVO' && !empty($prestamo['fechaVencimiento']);
                        $dias = $activo ? \Carbon\Carbon::parse($prestamo['fechaVencimiento'])->diffInDays(\Carbon\Carbon::now(), false) : 0;
                    @endphp
                    <p class="text-sm font-semibold {{ $vencido ? 'text-red-500' : ($activo && $dias <= 1 ? 'text-gold-400' : 'text-green-600') }}">
                        {{ $vencido ? 'Vencido' : ($activo ? (int) $dias . ' día(s)' : '—') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-fit">
            <h3 class="font-semibold text-gray-800 flex items-center mb-4">
                <i class="fas fa-sticky-note text-gold-400 mr-2"></i>
                Observaciones
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $prestamo['observaciones'] ?? 'Sin observaciones.' }}</p>
        </div>
    </div>
</x-app-layout>