<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Nueva Reserva</h1>
                <p class="text-sm text-gray-500 mt-0.5">Registrar una reserva de libro</p>
            </div>
            <a href="{{ route('reservas.index') }}"
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
        <form method="POST" action="{{ route('reservas.store') }}">
            @csrf
            <div class="p-6 grid grid-cols-1 gap-5">
                <!-- Usuario -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Usuario <span class="text-red-500">*</span></label>
                    <select name="usuarioId" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        <option value="">Selecciona un usuario...</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario['id'] }}" @selected(old('usuarioId') == $usuario['id'])>
                                {{ $usuario['nombre'] }} ({{ $usuario['email'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Libro -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Libro <span class="text-red-500">*</span></label>
                    <select name="libroId" required size="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        @forelse ($libros as $libro)
                            <option value="{{ $libro['id'] }}" @selected(old('libroId') == $libro['id'])>
                                {{ $libro['titulo'] }} ({{ $libro['isbn'] ?? 's/i' }})
                            </option>
                        @empty
                            <option value="" disabled>No hay libros registrados</option>
                        @endforelse
                    </select>
                    <p class="text-xs text-gray-400 mt-1">El sistema valida que el libro no tenga una reserva pendiente.</p>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a href="{{ route('reservas.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Registrar Reserva
                </button>
            </div>
        </form>
    </div>
</x-app-layout>