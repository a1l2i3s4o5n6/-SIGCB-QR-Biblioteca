<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Configuración</h1>
                <p class="text-sm text-gray-500 mt-0.5">Parámetros generales del sistema</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
        <form method="POST" action="{{ route('configuracion.update') }}">
            @csrf
            <div class="p-6 grid grid-cols-1 gap-5">
                @forelse ($configuraciones as $config)
                    @php
                        $tipo = in_array($config['clave'] ?? '', ['dias_prestamo', 'max_prestamos_activos']) ? 'number' : ($config['clave'] ?? '' === 'monto_multa_diario' ? 'number' : 'text');
                        if (($config['clave'] ?? '') === 'monto_multa_diario') { $tipo = 'number'; $step = '0.01'; }
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            {{ $config['descripcion'] ?? $config['clave'] }}
                            <span class="text-[11px] text-gray-400 font-mono ml-1">{{ $config['clave'] }}</span>
                        </label>
                        <input type="{{ $tipo }}" name="config[{{ $config['id'] }}]" value="{{ $config['valor'] }}"
                            @isset($step) step="{{ $step }}" @endisset
                            @if($tipo === 'number') min="0" @endif
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-6">No hay parámetros de configuración.</p>
                @endforelse
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>