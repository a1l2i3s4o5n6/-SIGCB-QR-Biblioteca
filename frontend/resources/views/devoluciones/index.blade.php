<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Devoluciones</h1>
                <p class="text-sm text-gray-500 mt-0.5">Historial de préstamos devueltos</p>
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

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium">Libro</th>
                        <th class="px-5 py-3 font-medium">Ejemplar</th>
                        <th class="px-5 py-3 font-medium">Fecha de préstamo</th>
                        <th class="px-5 py-3 font-medium">Fecha de devolución</th>
                        <th class="px-5 py-3 font-medium text-center">Estado</th>
                        <th class="px-5 py-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($prestamos as $prestamo)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $prestamo['usuarioNombre'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ $prestamo['libroTitulo'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-[11px] font-mono rounded">{{ $prestamo['codigoEjemplar'] ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $prestamo['fechaPrestamo'] ? \Carbon\Carbon::parse($prestamo['fechaPrestamo'])->format('d/m/Y') : '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $prestamo['fechaDevolucion'] ? \Carbon\Carbon::parse($prestamo['fechaDevolucion'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-medium rounded-full">{{ $prestamo['estado'] ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('prestamos.show', $prestamo['id']) }}"
                                        title="Ver detalle"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-400 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <i class="fas fa-undo-alt text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">No hay devoluciones registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($prestamos) }}</span> de {{ number_format($total) }} devoluciones · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('devoluciones.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('devoluciones.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>