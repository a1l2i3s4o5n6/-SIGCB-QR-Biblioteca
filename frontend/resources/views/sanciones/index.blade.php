<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Sanciones</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) ? 'Gestión de sanciones a usuarios' : 'Sanciones registradas en tu cuenta' }}
                </p>
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
                <span class="block">{{ $error }}</span>
            @endforeach
        </div>
    @endif

    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
    <!-- Aplicar sanción -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-1"><i class="fas fa-gavel mr-2 text-gold-400"></i>Aplicar sanción</h2>
        <p class="text-xs text-gray-500 mb-4">Una sanción activa bloquea nuevos préstamos del usuario hasta que sea levantada.</p>
        <form method="POST" action="{{ route('sanciones.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            @csrf
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Usuario</label>
                <select name="usuarioId" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
                    <option value="">— Seleccionar usuario —</option>
                    @foreach ($usuarios as $u)
                        <option value="{{ $u['id'] }}" @selected(old('usuarioId') == $u['id'])>{{ $u['nombre'] }} ({{ $u['email'] }})</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo</label>
                <select name="tipo" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
                    <option value="ADVERTENCIA" @selected(old('tipo') === 'ADVERTENCIA')>ADVERTENCIA</option>
                    <option value="SUSPENSION" @selected(old('tipo') === 'SUSPENSION')>SUSPENSION</option>
                    <option value="BLOQUEO_TEMPORAL" @selected(old('tipo') === 'BLOQUEO_TEMPORAL')>BLOQUEO TEMPORAL</option>
                </select>
            </div>
            <div class="lg:col-span-3">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Inicio</label>
                <input type="datetime-local" name="fechaInicio" value="{{ old('fechaInicio') }}" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Fin (opcional)</label>
                <input type="datetime-local" name="fechaFin" value="{{ old('fechaFin') }}"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Motivo</label>
                <input type="text" name="motivo" value="{{ old('motivo') }}" placeholder="Motivo"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
            </div>
            <div class="lg:col-span-12 -mt-1">
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-primary-400 text-white text-sm font-medium hover:bg-primary-500 transition">
                    <i class="fas fa-gavel mr-1"></i> Aplicar sanción
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Tabla de sanciones -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium">Tipo</th>
                        <th class="px-5 py-3 font-medium">Motivo</th>
                        <th class="px-5 py-3 font-medium">Inicio</th>
                        <th class="px-5 py-3 font-medium">Fin</th>
                        <th class="px-5 py-3 font-medium text-center">Estado</th>
                        <th class="px-5 py-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sanciones as $sancion)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $sancion['usuarioNombre'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 {{ ($sancion['tipo'] ?? '') === 'BLOQUEO_TEMPORAL' ? 'bg-red-50 text-red-700' : 'bg-gold-400/15 text-gold-600' }} text-[11px] font-medium rounded-full">
                                    {{ str_replace('_', ' ', $sancion['tipo'] ?? '—') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 max-w-xs truncate" title="{{ $sancion['motivo'] ?? '' }}">{{ $sancion['motivo'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ !empty($sancion['fechaInicio']) ? \Carbon\Carbon::parse($sancion['fechaInicio'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ !empty($sancion['fechaFin']) ? \Carbon\Carbon::parse($sancion['fechaFin'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-1 {{ ($sancion['activa'] ?? false) ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }} text-[11px] font-medium rounded-full">
                                    {{ ($sancion['activa'] ?? false) ? 'ACTIVA' : 'LEVANTADA' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) && ($sancion['activa'] ?? false))
                                        <form method="POST" action="{{ route('sanciones.levantar', $sancion['id']) }}"
                                            onsubmit="return confirm('¿Levantar esta sanción? El usuario podrá volver a realizar préstamos.')">
                                            @csrf
                                            <button type="submit" title="Levantar sanción"
                                                class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-600 hover:bg-green-50 hover:text-green-500 text-xs font-medium transition">
                                                <i class="fas fa-check mr-1"></i> Levantar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <i class="fas fa-shield-alt text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">No hay sanciones registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($sanciones) }}</span> de {{ number_format($total) }} sanciones · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('sanciones.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('sanciones.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>