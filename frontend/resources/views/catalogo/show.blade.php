<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $libro['titulo'] ?? 'Libro' }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Detalle del material bibliográfico</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('catalogo.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                    <a href="{{ route('catalogo.edit', $libro['id']) }}"
                        class="btn-primary-custom inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow">
                        <i class="fas fa-edit mr-2"></i> Editar
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 flex items-center justify-between px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <span><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info principal -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-primary-400 mr-2"></i>
                    Información del libro
                </h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">ISBN</p>
                    <p class="text-sm text-gray-700">{{ $libro['isbn'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Año de publicación</p>
                    <p class="text-sm text-gray-700">{{ $libro['anioPublicacion'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Edición</p>
                    <p class="text-sm text-gray-700">{{ $libro['edicion'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Ubicación</p>
                    <p class="text-sm text-gray-700">{{ $libro['ubicacion'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Categoría</p>
                    <p class="text-sm text-gray-700">
                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-medium rounded-full">{{ $libro['categoria'] ?? 'Sin categoría' }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Editorial</p>
                    <p class="text-sm text-gray-700">{{ $libro['editorial'] ?? '—' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-400 uppercase font-medium mb-1">Descripción</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $libro['descripcion'] ?? 'Sin descripción.' }}</p>
                </div>
            </div>
        </div>

        <!-- Stock y autores -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 flex items-center mb-4">
                    <i class="fas fa-boxes text-primary-400 mr-2"></i>
                    Ejemplares
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-green-700">{{ $libro['ejemplaresDisponibles'] ?? 0 }}</p>
                        <p class="text-xs text-green-600 mt-1">Disponibles</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-2xl font-bold text-gray-700">{{ $libro['ejemplaresTotales'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500 mt-1">Totales</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 flex items-center mb-4">
                    <i class="fas fa-user-pen text-gold-400 mr-2"></i>
                    Autores
                </h3>
                @forelse ($libro['autores'] ?? [] as $autor)
                    <div class="flex items-center py-2 border-b border-gray-50 last:border-0">
                        <div class="w-7 h-7 rounded-full bg-primary-50 flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas fa-user text-primary-400 text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">{{ $autor }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Sin autores registrados.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>