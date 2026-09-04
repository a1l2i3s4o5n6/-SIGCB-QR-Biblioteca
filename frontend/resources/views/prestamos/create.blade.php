<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Nuevo Préstamo</h1>
                <p class="text-sm text-gray-500 mt-0.5">Registrar un préstamo de material bibliográfico</p>
            </div>
            <a href="{{ route('prestamos.index') }}"
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
        $oldInventarioId = old('inventarioId');
    @endphp

    <div class="max-w-3xl"
        x-data="formularioPrestamo({
            usuarios: @js($usuarios),
            inventario: @js($inventario),
            solicitudes: @js($solicitudesReg),
            oldUsuarioId: @js($oldUsuarioId ? (int) $oldUsuarioId : null),
            oldInventarioId: @js($oldInventarioId ? (int) $oldInventarioId : null),
         })">
        <form method="POST" action="{{ route('prestamos.store') }}">
            @csrf
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 grid grid-cols-1 gap-6">

                <!-- Usuario (combobox buscable) -->
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

                <!-- Búsqueda por código de ejemplar -->
                <div class="rounded-xl bg-gold-50/40 border border-gold-100 p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-qrcode mr-1 text-gold-400"></i> Buscar por código de ejemplar
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Atajo para escanear o teclear el código físico (ej. LIB-0001-01): autoselecciona libro y ejemplar.</p>
                    <div class="flex gap-2">
                        <input type="text" x-model="codigo" @keydown.enter.prevent="buscarPorCodigo(@js(route('datos.ejemplar.codigo')))"
                            placeholder="LIB-XXXX-XX"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-400">
                        <button type="button" @click="buscarPorCodigo(@js(route('datos.ejemplar.codigo')))" :disabled="cargandoCodigo"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gray-700 hover:bg-gray-800 disabled:opacity-50 transition">
                            <i class="fas" :class="cargandoCodigo ? 'fa-circle-notch fa-spin' : 'fa-search'"></i>
                        </button>
                    </div>
                    <p x-show="errorCodigo" x-cloak class="mt-2 text-xs text-red-600" x-text="errorCodigo"></p>
                </div>

                <!-- Validación por código QR del libro (opcional) -->
                <div class="rounded-xl bg-primary-50/40 border border-primary-100 p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        <i class="fas fa-qrcode mr-1 text-primary-500"></i> Código QR del libro (opcional)
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Escanea la etiqueta QR pegada en el libro. Al registrar, el sistema verifica que el QR exista, esté activo y corresponda al libro del préstamo.</p>
                    <input type="text" name="codigoQr" value="{{ old('codigoQr') ?? '' }}"
                        placeholder="QR-978-0132350884"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-400">
                    @error('codigoQr')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Libro + Ejemplar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Libro <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mb-3" @click.outside="abiertoL = false">
                        <div class="relative">
                            <i class="fas fa-book absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="text" x-model="libroQ" autocomplete="off"
                                @focus="abiertoL = true" @input="abiertoL = true; inventarioSel = ''"
                                placeholder="Escribe el título del libro..."
                                class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                            <button type="button" x-show="libroQ && !abiertoL" @click="limpiarLibro()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times-circle text-xs"></i>
                            </button>
                        </div>
                        <div x-show="abiertoL" x-cloak
                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto">
                            <template x-for="l in librosFiltrados" :key="l.clave">
                                <button type="button" @click="elegirLibro(l)"
                                    class="w-full text-left px-3 py-2.5 hover:bg-primary-50 transition border-b border-gray-50 last:border-0 text-sm font-medium text-gray-800"
                                    x-text="l.titulo">
                                </button>
                            </template>
                            <p x-show="librosFiltrados.length === 0" class="px-3 py-3 text-sm text-gray-400 text-center">
                                Sin libros con ejemplares disponibles
                            </p>
                        </div>
                    </div>

                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Ejemplar disponible <span class="text-red-500">*</span>
                    </label>
                    <select name="inventarioId" required size="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        <template x-for="it in ejemplaresDelLibro" :key="it.id">
                            <option :value="String(it.id)" :selected="inventarioSel === String(it.id)"
                                x-text="`${it.codigoEjemplar} — ${it.libroTitulo} (${it.ubicacionEstante ?? 's/u'})`"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Primero elige un libro (o usa la búsqueda por código). Solo ejemplares DISPONIBLE.</p>
                    @error('inventarioId')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror

                    <div x-show="haySolicitudes" x-cloak
                        class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3">
                        <p class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                            <i class="fas fa-bell"></i>
                            Solicitud(es) pendiente(s) para este libro
                        </p>
                        <p class="text-xs text-amber-700 mt-1.5 leading-relaxed">
                            <span class="font-semibold" x-text="nombresSolicitantes"></span>
                            <span>— atiende la solicitud antes de registrar el préstamo:</span>
                            <a href="{{ route('reservas.index', ['estado' => 'PENDIENTE']) }}"
                                class="underline font-semibold hover:text-amber-900">ver solicitudes</a>.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 rounded-xl shadow-sm border border-gray-200 bg-white px-6 py-4 flex justify-end gap-2">
                <a href="{{ route('prestamos.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Registrar Préstamo
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
