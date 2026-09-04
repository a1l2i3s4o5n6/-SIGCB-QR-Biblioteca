<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Renovaciones pendientes</h1>
                <p class="text-sm text-gray-500 mt-0.5">Solicitudes de renovación a espera de aprobación</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                <i class="fas fa-clock mr-1"></i>{{ $total }} pendiente{{ $total === 1 ? '' : 's' }}
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if (empty($prestamos))
            <div class="p-10 text-center text-gray-400">
                <i class="fas fa-check-double text-4xl mb-3"></i>
                <p class="text-sm">No hay solicitudes de renovación pendientes.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Estudiante</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Libro</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Ejemplar</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Préstamo</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Vence</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Renovaciones</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($prestamos as $prestamo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800">{{ $prestamo['usuarioNombre'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $prestamo['libroTitulo'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $prestamo['codigoEjemplar'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $prestamo['fechaPrestamo'] ? \Carbon\Carbon::parse($prestamo['fechaPrestamo'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-amber-700 font-medium">
                                        {{ $prestamo['fechaVencimiento'] ? \Carbon\Carbon::parse($prestamo['fechaVencimiento'])->format('d/m/Y') : '—' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $prestamo['numRenovaciones'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex gap-2">
                                        <form method="POST" action="{{ route('prestamos.aprobar-renovacion', $prestamo['id']) }}"
                                            onsubmit="return confirm('¿Aprobar la renovación de este préstamo?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 transition">
                                                <i class="fas fa-check mr-1"></i> Aprobar
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('prestamos.rechazar-renovacion', $prestamo['id']) }}"
                                            onsubmit="return confirm('¿Rechazar la solicitud de renovación?')">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                                <i class="fas fa-times mr-1"></i> Rechazar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($totalPages > 1)
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-xs text-gray-500">Mostrando {{ count($prestamos) }} de {{ $total }}</p>
                    <div class="flex gap-2">
                        @if (!$first)
                            <a href="{{ route('prestamos.renovaciones-pendientes', ['page' => max(0, $page - 1)]) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100">Anterior</a>
                        @endif
                        @if (!$last)
                            <a href="{{ route('prestamos.renovaciones-pendientes', ['page' => $page + 1]) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100">Siguiente</a>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
