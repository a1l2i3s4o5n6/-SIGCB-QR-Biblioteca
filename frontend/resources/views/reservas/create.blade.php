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

    @php
        $oldUsuarioId = old('usuarioId');
        $oldLibroId = old('libroId') ?: ($prefillLibroId ?? null);
    @endphp

    <div class="max-w-3xl"
        x-data="formularioReserva({
            usuarios: @js($usuarios),
            libros: @js($libros),
            oldUsuarioId: @js($oldUsuarioId ? (int) $oldUsuarioId : null),
            oldLibroId: @js($oldLibroId ? (int) $oldLibroId : null),
         })">
        <form method="POST" action="{{ route('reservas.store') }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 grid grid-cols-1 gap-6">

                <!-- Usuario -->
                @if ($esStaff)
                    <!-- Staff: combobox buscable de usuarios -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Usuario <span class="text-red-500">*</span>
                        </label>
                        <div class="relative" @click.outside="abiertoU = false">
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="text" x-model="usuarioQ" autocomplete="off"
                                    @focus="abiertoU = true" @input="abiertoU = true; usuarioSel = null"
                                    placeholder="Escribe el nombre o correo del usuario..."
                                    class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                                <button type="button" x-show="usuarioQ && !abiertoU" @click="limpiarUsuario()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times-circle text-xs"></i>
                                </button>
                            </div>
                            <div x-show="abiertoU" x-cloak
                                class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                                <template x-for="u in usuariosFiltrados" :key="u.id">
                                    <button type="button" @click="elegirUsuario(u)"
                                        class="w-full text-left px-3 py-2.5 hover:bg-primary-50 transition border-b border-gray-50 last:border-0">
                                        <span class="block text-sm font-medium text-gray-800" x-text="u.nombre"></span>
                                        <span class="block text-xs text-gray-400" x-text="u.email"></span>
                                    </button>
                                </template>
                                <p x-show="usuariosFiltrados.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
                                    Sin resultados
                                </p>
                            </div>
                        </div>
                        <input type="hidden" name="usuarioId" :value="usuarioId">
                        @error('usuarioId')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <!-- Estudiante: se reserva automáticamente para sí mismo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Reservando para</label>
                        <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-lg">
                            <i class="fas fa-user-check text-green-500"></i>
                            <span class="text-sm font-medium text-gray-800">{{ $currentUserName }}</span>
                            <span class="text-xs text-gray-400">(tu cuenta)</span>
                        </div>
                        <input type="hidden" name="usuarioId" value="{{ $currentUserId }}">
                    </div>
                @endif

                <!-- Libro (combobox buscable) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Libro <span class="text-red-500">*</span>
                    </label>
                    <div class="relative" @click.outside="abiertoL = false">
                        <div class="relative">
                            <i class="fas fa-book absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="text" x-model="libroQ" autocomplete="off"
                                @focus="abiertoL = true" @input="abiertoL = true; libroSel = null"
                                placeholder="Escribe el título del libro..."
                                class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                            <button type="button" x-show="libroQ && !abiertoL" @click="limpiarLibro()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                        <div x-show="abiertoL" x-cloak
                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="l in librosFiltrados" :key="l.id">
                                <button type="button" @click="elegirLibro(l)"
                                    class="w-full text-left px-3 py-2.5 hover:bg-primary-50 transition border-b border-gray-50 last:border-0">
                                    <span class="block text-sm font-medium text-gray-800" x-text="l.titulo"></span>
                                </button>
                            </template>
                            <p x-show="librosFiltrados.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
                                Sin resultados
                            </p>
                        </div>
                    </div>
                    <input type="hidden" name="libroId" :value="libroId">
                    <p class="text-xs text-gray-400 mt-1">El sistema valida que el libro no tenga una reserva pendiente.</p>
                    @error('libroId')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4 rounded-xl shadow-sm border border-gray-200 bg-white px-6 py-4 flex justify-end gap-2">
                <a href="{{ $esStaff ? route('reservas.index') : route('dashboard') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> {{ $esStaff ? 'Registrar Reserva' : 'Solicitar Reserva' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
