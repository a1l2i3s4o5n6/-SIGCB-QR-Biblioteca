<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Panel de control del sistema bibliotecario</p>
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <i class="fas fa-calendar-alt text-primary-400"></i>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </x-slot>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
        <!-- Libros prestados hoy -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Libros Prestados Hoy</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['librosPrestadosHoy'] ?? 0) }}</p>
                </div>
                <div class="stat-icon bg-primary-50 text-primary-400">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>

        <!-- Libros disponibles -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Libros Disponibles</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['librosDisponibles'] ?? 0) }}</p>
                </div>
                <div class="stat-icon bg-blue-50 text-blue-500">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <!-- Estudiantes activos -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Estudiantes Activos</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats['estudiantesActivos'] ?? 0) }}</p>
                </div>
                <div class="stat-icon bg-gold-50 text-gold-400">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>

        <!-- Multas pendientes -->
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Multas Pendientes</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($stats['totalMultas'] ?? 0, 2) }}</p>
                </div>
                <div class="stat-icon bg-red-50 text-red-400">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Actividad Reciente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-history text-primary-400 mr-2"></i>
                    Actividad Reciente
                </h3>
                <a href="#" class="text-xs text-primary-400 hover:text-primary-500 font-medium">Ver todo</a>
            </div>
            <div class="p-5 space-y-4">
                @forelse ($prestamos as $prestamo)
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exchange-alt text-primary-400 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800"><span class="font-semibold">{{ $prestamo['usuarioNombre'] ?? 'Usuario' }}</span> {{ $prestamo['estado'] === 'DEVUELTO' ? 'devolvió' : 'realizó un préstamo' }} de <span class="font-semibold">{{ $prestamo['libroTitulo'] ?? '' }}</span></p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($prestamo['fechaPrestamo'] ?? null)->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No hay actividad reciente.</p>
                @endforelse
            </div>
        </div>

        <!-- Próximas reservas -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calendar-check text-gold-400 mr-2"></i>
                    Próximas Reservas
                </h3>
                <a href="#" class="text-xs text-primary-400 hover:text-primary-500 font-medium">Ver todo</a>
            </div>
            <div class="p-5">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 text-xs uppercase">
                            <th class="pb-3 font-medium">Estudiante</th>
                            <th class="pb-3 font-medium">Libro</th>
                            <th class="pb-3 font-medium">Fecha</th>
                            <th class="pb-3 font-medium">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($reservas as $reserva)
                            <tr>
                                <td class="py-3">{{ $reserva['usuarioNombre'] ?? '' }}</td>
                                <td class="py-3 text-gray-600">{{ $reserva['libroTitulo'] ?? '' }}</td>
                                <td class="py-3 text-gray-500">{{ \Carbon\Carbon::parse($reserva['fechaReserva'] ?? null)->format('d/m/Y') }}</td>
                                <td class="py-3">
                                    @php
                                        $color = match($reserva['estado'] ?? '') {
                                            'CONFIRMADA' => 'bg-green-50 text-green-700',
                                            'COMPLETADA' => 'bg-blue-50 text-blue-700',
                                            'CANCELADA' => 'bg-red-50 text-red-700',
                                            default => 'bg-yellow-50 text-yellow-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 {{ $color }} text-[11px] font-medium rounded-full">{{ $reserva['estado'] ?? 'PENDIENTE' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 text-center text-gray-400">No hay reservas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
