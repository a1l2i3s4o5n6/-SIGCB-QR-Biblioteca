<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Multas</h1>
                <p class="text-sm text-gray-500 mt-0.5">Gestión de multas por retraso en devoluciones</p>
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
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    <!-- Filtros -->
    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('multas.index') }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $filtro === '' ? 'bg-primary-400 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Todas
        </a>
        <a href="{{ route('multas.index', ['estado' => 'pendientes']) }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $filtro === 'pendientes' ? 'bg-gold-400 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Pendientes
        </a>
        <a href="{{ route('multas.index', ['estado' => 'pagadas']) }}"
            class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $filtro === 'pagadas' ? 'bg-green-500 text-white shadow' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            Pagadas
        </a>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium">Préstamo</th>
                        <th class="px-5 py-3 font-medium">Concepto</th>
                        <th class="px-5 py-3 font-medium text-right">Monto</th>
                        <th class="px-5 py-3 font-medium">Fecha de pago</th>
                        <th class="px-5 py-3 font-medium text-center">Estado</th>
                        <th class="px-5 py-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($multas as $multa)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-semibold text-gray-800">{{ $multa['usuarioNombre'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-[11px] font-mono rounded">#{{ $multa['prestamoId'] ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 max-w-xs truncate" title="{{ $multa['concepto'] ?? '' }}">{{ $multa['concepto'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-gray-800">${{ number_format($multa['monto'] ?? 0, 2) }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ !empty($multa['fechaPago']) ? \Carbon\Carbon::parse($multa['fechaPago'])->format('d/m/Y H:i') : '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="px-2 py-1 {{ ($multa['pagada'] ?? false) ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }} text-[11px] font-medium rounded-full">
                                    {{ ($multa['pagada'] ?? false) ? 'PAGADA' : 'PENDIENTE' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) && !($multa['pagada'] ?? false))
                                        <form method="POST" action="{{ route('multas.pagar', $multa['id']) }}"
                                            onsubmit="return confirm('¿Registrar el pago de esta multa?')">
                                            @csrf
                                            <button type="submit"
                                                title="Registrar pago"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-600 hover:bg-green-50 hover:text-green-500 transition">
                                                <i class="fas fa-dollar-sign text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center">
                                <i class="fas fa-exclamation-triangle text-gray-300 text-3xl mb-3"></i>
                                <p class="text-gray-400">No hay multas {{ $filtro === 'pendientes' ? 'pendientes' : ($filtro === 'pagadas' ? 'pagadas' : 'registradas') }}.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($totalPages > 1 && $filtro === '')
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($multas) }}</span> de {{ number_format($total) }} multas · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('multas.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('multas.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>