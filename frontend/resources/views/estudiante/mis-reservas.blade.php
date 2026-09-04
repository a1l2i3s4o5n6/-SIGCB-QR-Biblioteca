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
                    <option value="{{ $libro['id'] }}">{{ $libro['titulo'] ?? '' }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="px-5 py-2 rounded-lg text-sm font-semibold text-white btn-primary-custom shadow">
                <i class="fas fa-plus mr-2"></i> Reservar
            </button>
        </form>
    </div>

    <!-- Mis reservas -->
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
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Libro</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Fecha</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Vence</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Posición</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Estado</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($reservas as $reserva)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800">{{ $reserva['libroTitulo'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $reserva['fechaReserva'] ? \Carbon\Carbon::parse($reserva['fechaReserva'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $reserva['fechaVencimiento'] ? \Carbon\Carbon::parse($reserva['fechaVencimiento'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $reserva['posicionLista'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php $estado = $reserva['estado'] ?? ''; @endphp
                                    @if ($estado === 'PENDIENTE')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Pendiente</span>
                                    @elseif ($estado === 'CONFIRMADA')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Confirmada</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $estado }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($estado === 'PENDIENTE')
                                        <form method="POST" action="{{ route('estudiante.cancelar-reserva', $reserva['id']) }}"
                                            onsubmit="return confirm('¿Cancelar esta reserva?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                                <i class="fas fa-times mr-1"></i> Cancelar
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
