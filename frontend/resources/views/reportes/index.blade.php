<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Reportes</h1>
                <p class="text-sm text-gray-500 mt-0.5">Estadísticas y reportes del sistema</p>
            </div>
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

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">Préstamos de hoy</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $prestamosHoy['total'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $prestamosHoy['fecha'] ?? '' }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-primary-50 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-primary-400"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">Multas cobradas (mes)</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">${{ number_format($multasMes['totalCobrado'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ ucfirst(strtolower($multasMes['mes'] ?? '')) }}</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-green-50 flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-500"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase font-medium">Multas pendientes</p>
                    <p class="text-2xl font-bold text-gold-400 mt-1">{{ $multasMes['pendientes'] ?? 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Por cobrar</p>
                </div>
                <div class="w-11 h-11 rounded-lg bg-yellow-50 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-gold-400"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Préstamos del día -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calendar-day text-primary-400 mr-2"></i>
                    Préstamos del día
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                            <th class="px-5 py-2.5 font-medium">Usuario</th>
                            <th class="px-5 py-2.5 font-medium">Libro</th>
                            <th class="px-5 py-2.5 font-medium text-center">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($prestamosHoy['prestamos'] ?? [] as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-2.5 font-medium text-gray-800">{{ $p['usuario'] ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-gray-600">{{ $p['libro'] ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-center text-gray-500">{{ $p['hora'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center">
                                    <i class="fas fa-coffee text-gray-300 text-2xl mb-2"></i>
                                    <p class="text-gray-400 text-sm">Sin préstamos registrados hoy.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Libros más solicitados -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-fire text-gold-400 mr-2"></i>
                    Top 10 libros más solicitados
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 text-xs uppercase bg-gray-50">
                            <th class="px-5 py-2.5 font-medium">#</th>
                            <th class="px-5 py-2.5 font-medium">Título</th>
                            <th class="px-5 py-2.5 font-medium text-center">Préstamos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($librosTop as $i => $libro)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-2.5">
                                    <span class="w-6 h-6 inline-flex items-center justify-center rounded-full {{ $i < 3 ? 'bg-gold-400 text-white' : 'bg-gray-100 text-gray-500' }} text-[11px] font-bold">{{ $i + 1 }}</span>
                                </td>
                                <td class="px-5 py-2.5">
                                    <p class="font-medium text-gray-800">{{ $libro['titulo'] ?? '—' }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $libro['isbn'] ?? '' }}</p>
                                </td>
                                <td class="px-5 py-2.5 text-center">
                                    <span class="px-2 py-1 {{ ($libro['vecesPrestado'] ?? 0) > 0 ? 'bg-primary-50 text-primary-400' : 'bg-gray-100 text-gray-500' }} text-[11px] font-medium rounded-full">
                                        {{ $libro['vecesPrestado'] ?? 0 }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center">
                                    <i class="fas fa-book text-gray-300 text-2xl mb-2"></i>
                                    <p class="text-gray-400 text-sm">Sin datos disponibles.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>