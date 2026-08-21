<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Editar Libro</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $libro['titulo'] ?? 'Libro' }}</p>
            </div>
            <a href="{{ route('catalogo.show', $libro['id']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-4xl">
        <form method="POST" action="{{ route('catalogo.update', $libro['id']) }}">
            @csrf
            @method('PUT')
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Título -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="titulo" value="{{ old('titulo', $libro['titulo'] ?? '') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="Título del libro">
                </div>

                <!-- ISBN -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">ISBN</label>
                    <input type="text" name="isbn" value="{{ old('isbn', $libro['isbn'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="978-...">
                </div>

                <!-- Año -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Año de publicación</label>
                    <input type="number" name="anioPublicacion" value="{{ old('anioPublicacion', $libro['anioPublicacion'] ?? '') }}" min="1500" max="2100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                <!-- Edición -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Edición</label>
                    <input type="text" name="edicion" value="{{ old('edicion', $libro['edicion'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="1ra edición">
                </div>

                <!-- Ejemplares -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ejemplares totales</label>
                    <input type="number" name="ejemplaresTotales" value="{{ old('ejemplaresTotales', $libro['ejemplaresTotales'] ?? 1) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                <!-- Ubicación -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Ubicación</label>
                    <input type="text" name="ubicacion" value="{{ old('ubicacion', $libro['ubicacion'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="Estante A-01">
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Categoría</label>
                    <select name="categoriaId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        <option value="">Sin categoría</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria['id'] }}" @selected(old('categoriaId', $libro['categoriaId'] ?? null) == $categoria['id'])>
                                {{ $categoria['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Editorial -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Editorial</label>
                    <select name="editorialId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        <option value="">Sin editorial</option>
                        @foreach ($editoriales as $editorial)
                            <option value="{{ $editorial['id'] }}" @selected(old('editorialId', $libro['editorialId'] ?? null) == $editorial['id'])>
                                {{ $editorial['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Autores -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Autores</label>
                    <select name="autorIds[]" multiple size="5"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        @foreach ($autores as $autor)
                            <option value="{{ $autor['id'] }}">
                                {{ $autor['nombre'] }} {{ $autor['apellido'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Autores actuales: {{ implode(', ', $libro['autores'] ?? []) ?: 'ninguno' }}. Selecciona los que quieras conservar.
                        Mantén presionado Ctrl para seleccionar varios.
                    </p>
                </div>

                <!-- Descripción -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                    <textarea name="descripcion" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="Descripción breve del libro">{{ old('descripcion', $libro['descripcion'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a href="{{ route('catalogo.show', $libro['id']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Actualizar Libro
                </button>
            </div>
        </form>
    </div>
</x-app-layout>