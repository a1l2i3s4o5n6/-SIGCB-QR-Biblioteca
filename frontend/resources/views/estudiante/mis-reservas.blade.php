<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Mis Reservas</h1>
                <p class="text-sm text-gray-500 mt-0.5">Consulta tus reservas y reserva un libro del catálogo</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700 uppercase tracking-wide">
                {{ session('rol', 'LECTOR') }}
            </span>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
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

    @php
        $libroPreseleccionado = (int) request('libro', 0);
        $tituloSeleccionado = trim((string) request('titulo', ''));
    @endphp

    @if ($libroPreseleccionado > 0)
        <div class="mb-6 px-4 py-3 bg-primary-50 border border-primary-200 text-primary-700 text-sm rounded-lg flex items-center gap-2">
            <i class="fas fa-hand-holding-heart"></i>
            <span>Libro seleccionado desde el QR:
                <strong>{{ $tituloSeleccionado ?: '#' . $libroPreseleccionado }}</strong>. Pulsa <strong>Reservar</strong> para guardar tu reserva.
            </span>
        </div>
    @endif

    <!-- Reservar un libro -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">
            <i class="fas fa-book-medical text-primary-400 mr-2"></i> Reservar un libro del catálogo
        </h2>
        <form method="POST" action="{{ route('estudiante.reservar-libro') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <select name="libroId" required
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                <option value="">Selecciona un libro...</option>
                @foreach ($libros as $libro)
                    <option value="{{ $libro['id'] }}" @selected((int) $libro['id'] === $libroPreseleccionado)>{{ $libro['titulo'] ?? '' }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-5 py-2 rounded-lg text-sm font-semibold text-white btn-primary-custom shadow">
                <i class="fas fa-plus mr-2"></i> Reservar
            </button>
        </form>
    </div>

    <!-- Mis reservas (tarjetas horizontales) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-200 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Mis reservas activas</h2>
        </div>

        @if (empty($reservas))
            <div class="p-10 text-center text-gray-400">
                <i class="fas fa-calendar-check text-4xl mb-3"></i>
                <p class="text-sm">No tienes reservas.</p>
            </div>
        @else
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($reservas as $reserva)
                    @php $estado = $reserva['estado'] ?? ''; @endphp
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm hover:shadow transition flex flex-col">
                        <div class="px-4 py-3 flex items-start justify-between gap-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 leading-snug">{{ $reserva['libroTitulo'] ?? '—' }}</p>
                            @if ($estado === 'PENDIENTE')
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">Pendiente</span>
                            @elseif ($estado === 'CONFIRMADA')
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">Confirmada</span>
                            @else
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">{{ $estado }}</span>
                            @endif
                        </div>
                        <div class="px-4 py-3 space-y-1.5 text-xs text-gray-500 flex-1">
                            <p><i class="fas fa-calendar-plus mr-1.5 text-gray-300"></i>Solicitada:
                                {{ $reserva['fechaReserva'] ? \Carbon\Carbon::parse($reserva['fechaReserva'])->format('d/m/Y') : '—' }}</p>
                            <p><i class="fas fa-hourglass-end mr-1.5 text-gray-300"></i>Vence:
                                {{ $reserva['fechaVencimiento'] ? \Carbon\Carbon::parse($reserva['fechaVencimiento'])->format('d/m/Y') : '—' }}</p>
                            <p><i class="fas fa-arrow-up mr-1.5 text-gray-300"></i>Posición en lista: {{ $reserva['posicionLista'] ?? '—' }}</p>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-100">
                            @if ($estado === 'PENDIENTE')
                                <form method="POST" action="{{ route('estudiante.cancelar-reserva', $reserva['id']) }}"
                                    onsubmit="return confirm('¿Eliminar esta reserva?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-full px-3 py-2 rounded-lg text-xs font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt mr-1"></i> Eliminar reserva
                                    </button>
                                </form>
                            @else
                                <p class="text-center text-[11px] text-gray-400">Reserva {{ $estado === 'CONFIRMADA' ? 'confirmada' : 'cerrada' }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
