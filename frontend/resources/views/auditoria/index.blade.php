<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Auditoría</h1>
                <p class="text-sm text-gray-500 mt-0.5">Registro de actividades del sistema</p>
            </div>
            <button type="button" onclick="window.location.reload()"
                class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition">
                <i class="fas fa-sync-alt mr-2"></i> Actualizar
            </button>
        </div>
    </x-slot>

    <!-- Filtro por rango de fechas -->
    <form method="GET" action="{{ route('auditoria.index') }}"
        class="mb-4 bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3 flex flex-col sm:flex-row items-end sm:items-center gap-3">
        <div>
            <label for="desde" class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Desde</label>
            <input type="date" id="desde" name="desde" value="{{ $desde ?? '' }}"
                class="border-gray-300 focus:border-primary-400 focus:ring-primary-400 rounded-md shadow-sm text-sm">
        </div>
        <div>
            <label for="hasta" class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Hasta</label>
            <input type="date" id="hasta" name="hasta" value="{{ $hasta ?? '' }}"
                class="border-gray-300 focus:border-primary-400 focus:ring-primary-400 rounded-md shadow-sm text-sm">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit"
                class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-700 transition">
                <i class="fas fa-filter mr-2"></i> Filtrar
            </button>
            <button type="submit" formaction="{{ route('auditoria.reporte') }}"
                class="inline-flex items-center px-3 py-2 rounded-lg bg-primary-600 text-white text-sm font-medium hover:bg-primary-700 transition">
                <i class="fas fa-file-pdf mr-2"></i> Generar PDF
            </button>
            @if (($desde ?? '') !== '' || ($hasta ?? '') !== '')
                <a href="{{ route('auditoria.index') }}"
                    class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition"
                    title="Quitar filtro">
                    <i class="fas fa-times mr-1"></i> Limpiar
                </a>
            @endif
        </div>
    </form>

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            @foreach ($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Fecha y hora</th>
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium text-center">Acción</th>
                        <th class="px-5 py-3 font-medium">Entidad</th>
                        <th class="px-5 py-3 font-medium">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($registros as $registro)
                        @php
                            $accion = $registro['accion'] ?? '';
                            $colores = [
                                'LOGIN'      => 'bg-gray-100 text-gray-700',
                                'CREAR'      => 'bg-green-50 text-green-700',
                                'ACTUALIZAR' => 'bg-blue-50 text-blue-700',
                                'ELIMINAR'   => 'bg-red-50 text-red-700',
                                'DEVOLVER'   => 'bg-purple-50 text-purple-700',
                                'RENOVAR'    => 'bg-yellow-50 text-yellow-700',
                                'PAGAR'      => 'bg-emerald-50 text-emerald-700',
                                'CANCELAR'   => 'bg-orange-50 text-orange-700',
                                'REGENERAR'  => 'bg-indigo-50 text-indigo-700',
                                'ACTIVAR'    => 'bg-green-50 text-green-700',
                                'DESACTIVAR' => 'bg-orange-50 text-orange-700',
                            ];
                            $color = $colores[$accion] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($registro['createdAt'])->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $registro['usuarioNombre'] ?? 'Sistema' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-1 {{ $color }} text-[11px] font-medium rounded-full">{{ $accion }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 whitespace-nowrap">
                                {{ $registro['entidad'] ?? '—' }}@isset($registro['entidadId']) <span class="text-gray-400">#{{ $registro['entidadId'] }}</span>@endisset
                            </td>
                            <td class="px-5 py-3 text-gray-500">{{ $registro['detalle'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center">
                                <i class="fas fa-clipboard-list text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">No hay registros de auditoría.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($registros) }}</span> de {{ number_format($total) }} registros · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('auditoria.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('auditoria.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>