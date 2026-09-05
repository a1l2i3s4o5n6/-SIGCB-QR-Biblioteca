<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Mis Préstamos</h1>
                <p class="text-sm text-gray-500 mt-0.5">Consulta y solicita la renovación de tus préstamos</p>
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

    <form method="GET" action="{{ route('estudiante.mis-prestamos') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Estado</label>
                <select name="estado"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                    <option value="" @selected(request()->has('estado') && request('estado') === '')>Todos</option>
                    <option value="ACTIVO" @selected(!request()->has('estado') || request('estado') === 'ACTIVO')>Activos</option>
                    <option value="RENOVACION_PENDIENTE" @selected(request('estado') === 'RENOVACION_PENDIENTE')>Renovación pendiente</option>
                    <option value="DEVUELTO" @selected(request('estado') === 'DEVUELTO')>Devueltos</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 transition">
                    <i class="fas fa-filter mr-1.5"></i> Filtrar
                </button>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if (empty($prestamos))
            <div class="p-10 text-center text-gray-400">
                <i class="fas fa-book-open text-4xl mb-3"></i>
                <p class="text-sm">No tienes préstamos registrados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Libro</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Ejemplar</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Vence</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Estado</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-600">Renovaciones</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($prestamos as $prestamo)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-800">{{ $prestamo['libroTitulo'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $prestamo['codigoEjemplar'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $prestamo['fechaVencimiento'] ? \Carbon\Carbon::parse($prestamo['fechaVencimiento'])->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php $estado = $prestamo['estado'] ?? ''; @endphp
                                    @if ($estado === 'ACTIVO')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Activo</span>
                                    @elseif ($estado === 'RENOVACION_PENDIENTE')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Renovación pendiente</span>
                                    @elseif ($estado === 'DEVUELTO')
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Devuelto</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $estado }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $prestamo['numRenovaciones'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($estado === 'ACTIVO' && ($prestamo['numRenovaciones'] ?? 0) < 2)
                                        <form method="POST" action="{{ route('estudiante.solicitar-renovacion', $prestamo['id']) }}"
                                            onsubmit="return confirm('¿Solicitar la renovación de este préstamo?')">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-primary-700 bg-primary-50 border border-primary-200 hover:bg-primary-100 transition">
                                                <i class="fas fa-redo-alt mr-1"></i> Solicitar renovación
                                            </button>
                                        </form>
                                    @endif
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
                            <a href="{{ route('estudiante.mis-prestamos', array_merge(request()->query(), ['page' => max(0, $page - 1)])) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100">Anterior</a>
                        @endif
                        @if (!$last)
                            <a href="{{ route('estudiante.mis-prestamos', array_merge(request()->query(), ['page' => $page + 1])) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100">Siguiente</a>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
