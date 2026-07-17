<!-- ===== TOP NAVBAR ===== -->
<nav x-data="{ sidebarOpen: false, userMenuOpen: false }" class="navbar-custom fixed top-0 left-0 right-0 z-50 h-16">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between">
        <!-- Left side -->
        <div class="flex items-center space-x-4">
            <!-- Sidebar toggle -->
            <button @click="sidebarOpen = !sidebarOpen"
                class="sidebar-toggle text-white/80 hover:text-gold-400 focus:outline-none text-xl transition">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <x-application-logo class="h-10 w-auto" />
            </a>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="text-white/80 hover:text-gold-400 focus:outline-none text-lg transition relative">
                <i class="fas fa-bell"></i>
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full w-4 h-4 flex items-center justify-center">3</span>
            </button>

            <!-- User dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                    class="flex items-center space-x-2 text-white/90 hover:text-white focus:outline-none transition group">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-semibold text-white">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="hidden sm:block text-sm font-medium">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs transition" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Dropdown menu -->
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    style="display: none;">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition">
                        <i class="fas fa-user-circle w-5 text-gray-400"></i>
                        <span class="ml-2">Mi Perfil</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition">
                            <i class="fas fa-sign-out-alt w-5 text-gray-400"></i>
                            <span class="ml-2">Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- ===== SIDEBAR ===== -->
<aside x-data="{ open: false }"
    :class="{'open': open}"
    class="sidebar fixed top-16 left-0 h-[calc(100vh-4rem)] w-[260px] overflow-y-auto overflow-x-hidden z-40 transition-all duration-300 hidden md:block">

    <!-- Sidebar Header -->
    <div class="px-5 py-4 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-gold-400 flex items-center justify-center">
                <i class="fas fa-book-open text-white text-sm"></i>
            </div>
            <div>
                <p class="text-white text-sm font-semibold leading-tight">SIGCB-QR</p>
                <p class="text-white/50 text-[10px]">Biblioteca Universitaria</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="px-3 py-4 space-y-1">
        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-2">Principal</p>

        <a href="{{ route('dashboard') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('dashboard') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-chart-pie w-5 text-center text-sm"></i>
            <span class="ml-3">Dashboard</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Biblioteca</p>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-book w-5 text-center text-sm"></i>
            <span class="ml-3">Libros</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-users w-5 text-center text-sm"></i>
            <span class="ml-3">Usuarios</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Préstamos</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-undo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Devoluciones</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Reservas</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Gestión</p>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Multas</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-chart-bar w-5 text-center text-sm"></i>
            <span class="ml-3">Reportes</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Sistema</p>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-history w-5 text-center text-sm"></i>
            <span class="ml-3">Auditoría</span>
        </a>

        <a href="#"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-cog w-5 text-center text-sm"></i>
            <span class="ml-3">Configuración</span>
        </a>
    </nav>

    <!-- Sidebar footer -->
    <div class="px-5 py-3 border-t border-white/10 mt-2">
        <p class="text-[10px] text-white/30 text-center">SIGCB-QR v1.0</p>
    </div>
</aside>

<!-- ===== MOBILE SIDEBAR OVERLAY ===== -->
<div x-show="sidebarOpen"
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black/50 z-30 md:hidden"
    style="display: none;"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
</div>

<!-- ===== MOBILE SIDEBAR ===== -->
<aside x-show="sidebarOpen"
    x-transition:enter="transition ease-in-out duration-300 transform"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in-out duration-300 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="sidebar fixed top-16 left-0 h-[calc(100vh-4rem)] w-[260px] overflow-y-auto z-40 md:hidden"
    style="display: none;">

    <!-- Same content as desktop sidebar -->
    <div class="px-5 py-4 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-gold-400 flex items-center justify-center">
                <i class="fas fa-book-open text-white text-sm"></i>
            </div>
            <div>
                <p class="text-white text-sm font-semibold leading-tight">SIGCB-QR</p>
                <p class="text-white/50 text-[10px]">Biblioteca Universitaria</p>
            </div>
        </div>
    </div>

    <nav class="px-3 py-4 space-y-1">
        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-2">Principal</p>

        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('dashboard') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-chart-pie w-5 text-center text-sm"></i>
            <span class="ml-3">Dashboard</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Biblioteca</p>

        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-book w-5 text-center text-sm"></i>
            <span class="ml-3">Libros</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-users w-5 text-center text-sm"></i>
            <span class="ml-3">Usuarios</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Préstamos</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-undo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Devoluciones</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Reservas</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Gestión</p>

        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Multas</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-chart-bar w-5 text-center text-sm"></i>
            <span class="ml-3">Reportes</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Sistema</p>

        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-history w-5 text-center text-sm"></i>
            <span class="ml-3">Auditoría</span>
        </a>
        <a href="#" @click="sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition">
            <i class="nav-icon fas fa-cog w-5 text-center text-sm"></i>
            <span class="ml-3">Configuración</span>
        </a>
    </nav>

    <div class="px-5 py-3 border-t border-white/10 mt-2">
        <p class="text-[10px] text-white/30 text-center">SIGCB-QR v1.0</p>
    </div>
</aside>
