<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Reservas</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gestión de reservas de libros</p>
            </div>
            @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                <a href="{{ route('reservas.create') }}"
                    class="btn-primary-custom inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-plus mr-2"></i> Nueva Reserva
                </a>
            @endif
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

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium">Libro</th>
                        <th class="px-5 py-3 font-medium">Fecha de reserva</th>
                        <th class="px-5 py-3 font-medium">Vencimiento</th>
                        <th class="px-5 py-3 font-medium text-center">Estado</th>
                        <th class="px-5 py-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reservas as $reserva)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('reservas.show', $reserva['id']) }}"
                                    class="font-semibold text-gray-800 hover:text-primary-400">
                                    {{ $reserva['usuarioNombre'] ?? '—' }}
                                </a>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $reserva['libroTitulo'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $reserva['fechaReserva'] ? \Carbon\Carbon::parse($reserva['fechaReserva'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $reserva['fechaVencimiento'] ? \Carbon\Carbon::parse($reserva['fechaVencimiento'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    $color = match($reserva['estado'] ?? '') {
                                        'PENDIENTE' => 'bg-yellow-50 text-yellow-700',
                                        'COMPLETADA' => 'bg-green-50 text-green-700',
                                        'CANCELADA' => 'bg-red-50 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 {{ $color }} text-[11px] font-medium rounded-full">{{ $reserva['estado'] ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('reservas.show', $reserva['id']) }}"
                                        title="Ver detalle"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-400 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) && ($reserva['estado'] ?? '') === 'PENDIENTE')
                                        <form method="POST" action="{{ route('reservas.cancelar', $reserva['id']) }}"
                                            onsubmit="return confirm('¿Cancelar esta reserva?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                title="Cancelar"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-500 transition">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center">
                                <i class="fas fa-calendar-check text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">No hay reservas registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($reservas) }}</span> de {{ number_format($total) }} reservas · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('reservas.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('reservas.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>