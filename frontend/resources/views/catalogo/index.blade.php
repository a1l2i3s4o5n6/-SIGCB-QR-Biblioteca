<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Catálogo de Libros</h1>
                <p class="text-sm text-gray-500 mt-0.5">Material bibliográfico del sistema</p>
            </div>
            @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                <a href="{{ route('catalogo.create') }}"
                    class="btn-primary-custom inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-plus mr-2"></i> Nuevo Libro
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

    <!-- Buscador -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
        <form method="GET" action="{{ route('catalogo.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="q" value="{{ $q }}"
                    placeholder="Buscar por título del libro..."
                    class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
            </div>
            <div class="flex gap-2">
                <button type="submit"
                    class="btn-primary-custom px-5 py-2 rounded-lg text-sm font-semibold text-white">Buscar</button>
                @if ($q !== '')
                    <a href="{{ route('catalogo.index') }}"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">Limpiar</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Título</th>
                        <th class="px-5 py-3 font-medium">ISBN</th>
                        <th class="px-5 py-3 font-medium">Categoría</th>
                        <th class="px-5 py-3 font-medium">Editorial</th>
                        <th class="px-5 py-3 font-medium text-center">Año</th>
                        <th class="px-5 py-3 font-medium text-center">Disponibles</th>
                        <th class="px-5 py-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($libros as $libro)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('catalogo.show', $libro['id']) }}"
                                    class="font-semibold text-gray-800 hover:text-primary-400">
                                    {{ $libro['titulo'] ?? 'Sin título' }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ implode(', ', $libro['autores'] ?? []) ?: 'Sin autores' }}
                                </p>
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $libro['isbn'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-medium rounded-full">{{ $libro['categoria'] ?? 'Sin categoría' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $libro['editorial'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-gray-500">{{ $libro['anioPublicacion'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @php
                                    $total = $libro['ejemplaresTotales'] ?? 0;
                                    $disp = $libro['ejemplaresDisponibles'] ?? 0;
                                    $badge = $disp > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700';
                                @endphp
                                <span class="px-2 py-1 {{ $badge }} text-[11px] font-semibold rounded-full">{{ $disp }} / {{ $total }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('catalogo.show', $libro['id']) }}"
                                        title="Ver detalle"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-primary-50 hover:text-primary-400 transition">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                                        <a href="{{ route('catalogo.edit', $libro['id']) }}"
                                            title="Editar"
                                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-blue-50 hover:text-blue-500 transition">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                    @endif
                                    @if (session('rol') === 'ADMIN')
                                        <form method="POST" action="{{ route('catalogo.destroy', $libro['id']) }}"
                                            onsubmit="return confirm('¿Desactivar este libro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                title="Desactivar"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-red-50 hover:text-red-500 transition">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <i class="fas fa-book-open text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">{{ $q !== '' ? 'No se encontraron libros con ese criterio.' : 'Aún no hay libros registrados.' }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($libros) }}</span> de {{ number_format($total) }} libros · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('catalogo.index', ['q' => $q, 'page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('catalogo.index', ['q' => $q, 'page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>