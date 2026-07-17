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
                    <p class="text-2xl font-bold text-gray-800 mt-1">24</p>
                    <p class="text-xs text-green-500 mt-1"><i class="fas fa-arrow-up mr-1"></i>+12% vs ayer</p>
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
                    <p class="text-2xl font-bold text-gray-800 mt-1">1,284</p>
                    <p class="text-xs text-gray-400 mt-1">de 2,450 ejemplares</p>
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
                    <p class="text-2xl font-bold text-gray-800 mt-1">342</p>
                    <p class="text-xs text-gray-400 mt-1">con préstamos activos</p>
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
                    <p class="text-2xl font-bold text-gray-800 mt-1">$1,250</p>
                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-arrow-up mr-1"></i>+8% este mes</p>
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
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exchange-alt text-primary-400 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800"><span class="font-semibold">María García</span> realizó un préstamo</p>
                        <p class="text-xs text-gray-400">Programación en Java - hace 5 min</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-undo-alt text-green-500 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800"><span class="font-semibold">Carlos López</span> devolvió un libro</p>
                        <p class="text-xs text-gray-400">Cálculo Diferencial - hace 15 min</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gold-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-qrcode text-gold-400 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800"><span class="font-semibold">Admin</span> generó 15 códigos QR</p>
                        <p class="text-xs text-gray-400">Nuevos libros registrados - hace 1 hora</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation text-red-400 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800"><span class="font-semibold">Ana Martínez</span> tiene una multa pendiente</p>
                        <p class="text-xs text-gray-400">Retraso de 5 días - hace 2 horas</p>
                    </div>
                </div>
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
                        <tr>
                            <td class="py-3">Laura Sánchez</td>
                            <td class="py-3 text-gray-600">Estructuras de Datos</td>
                            <td class="py-3 text-gray-500">18/07/2026</td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-yellow-50 text-yellow-700 text-[11px] font-medium rounded-full">Pendiente</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3">Pedro Ramírez</td>
                            <td class="py-3 text-gray-600">Redes de Computadoras</td>
                            <td class="py-3 text-gray-500">19/07/2026</td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-green-50 text-green-700 text-[11px] font-medium rounded-full">Confirmada</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-3">Sofía Torres</td>
                            <td class="py-3 text-gray-600">Base de Datos II</td>
                            <td class="py-3 text-gray-500">20/07/2026</td>
                            <td class="py-3">
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 text-[11px] font-medium rounded-full">En espera</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
