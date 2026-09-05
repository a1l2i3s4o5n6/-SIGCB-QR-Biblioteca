<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detalle de Reserva</h1>
                <p class="text-sm text-gray-500 mt-0.5">Reserva #{{ $reserva['id'] ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('reservas.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                @if ((($reserva['estado'] ?? '') === 'PENDIENTE') && in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO', 'ESTUDIANTE']))
                    <form method="POST" action="{{ route('reservas.cancelar', $reserva['id']) }}"
                        onsubmit="return confirm('¿Cancelar esta reserva?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-red-500 hover:bg-red-600 shadow transition">
                            <i class="fas fa-times mr-2"></i> Cancelar Reserva
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-calendar-check text-primary-400 mr-2"></i>
                Información de la reserva
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Usuario</p>
                <p class="text-sm text-gray-700">{{ $reserva['usuarioNombre'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Libro</p>
                <p class="text-sm text-gray-700">{{ $reserva['libroTitulo'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Estado</p>
                @php
                    $color = match($reserva['estado'] ?? '') {
                        'PENDIENTE' => 'bg-yellow-50 text-yellow-700',
                        'COMPLETADA' => 'bg-green-50 text-green-700',
                        'CANCELADA' => 'bg-red-50 text-red-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="px-2 py-1 {{ $color }} text-[11px] font-medium rounded-full">{{ $reserva['estado'] ?? '—' }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Fecha de reserva</p>
                <p class="text-sm text-gray-700">{{ $reserva['fechaReserva'] ? \Carbon\Carbon::parse($reserva['fechaReserva'])->format('d/m/Y H:i') : '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Vencimiento</p>
                <p class="text-sm text-gray-700">{{ $reserva['fechaVencimiento'] ? \Carbon\Carbon::parse($reserva['fechaVencimiento'])->format('d/m/Y H:i') : '—' }}</p>
            </div>
        </div>
    </div>
</x-app-layout>