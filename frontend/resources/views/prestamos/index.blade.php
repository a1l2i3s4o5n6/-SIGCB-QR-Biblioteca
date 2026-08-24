<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Préstamos</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gestión de préstamos de material bibliográfico</p>
            </div>
            @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                <a href="{{ route('prestamos.create') }}"
                    class="btn-primary-custom inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-plus mr-2"></i> Nuevo Préstamo
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

    <div x-data="liveTabla({
            url: '{{ route('datos.prestamos') }}',
            container: 'tabla-prestamos',
            campos: ['q', 'estado', 'desde', 'hasta']
         })">
        <!-- Búsqueda y filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                <div class="lg:col-span-2 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" x-model="q" @input="alEscribir()"
                        placeholder="Buscar por estudiante, libro o código de ejemplar..."
                        class="w-full pl-9 pr-9 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                    <span x-show="cargando" x-cloak
                        class="absolute right-3 top-1/2 -translate-y-1/2 fas fa-circle-notch fa-spin text-primary-400 text-sm"></span>
                </div>
                <select x-model="estado" @change="cargar(1)"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO">Activos</option>
                    <option value="VENCIDO">Vencidos</option>
                    <option value="RENOVADO">Renovados</option>
                    <option value="DEVUELTO">Devueltos</option>
                </select>
                <button type="button" @click="limpiar()"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-broom mr-1.5"></i> Limpiar filtros
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-medium uppercase text-gray-400">Registrados entre</span>
                <input type="date" x-model="desde" @change="cargar(1)"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-400">
                <span class="text-xs text-gray-400">y</span>
                <input type="date" x-model="hasta" @change="cargar(1)"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-400">
            </div>
        </div>

        <!-- Tabla (se recarga en vivo) -->
        <div id="tabla-prestamos">
            @include('prestamos._tabla')
        </div>
    </div>
</x-app-layout>