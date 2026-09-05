<!-- ===== TOP NAVBAR ===== -->
@php
        $userFoto = session('user.foto');
        $mostrarFoto = !empty($userFoto)
            && (preg_match('#^https?://#i', $userFoto) || file_exists(public_path(trim($userFoto, '/'))));
    @endphp
<nav x-data="{
        userMenuOpen: false,
        notifCount: 0,
        init() {
            this.refrescarNotificaciones();
            setInterval(() => this.refrescarNotificaciones(), 60000);
        },
        async refrescarNotificaciones() {
            try {
                const r = await fetch('/notificaciones/no-leidas');
                const j = await r.json();
                this.notifCount = Number(j.count) || 0;
            } catch (e) { /* sin cambios si falla */ }
        }
    }" class="navbar-custom fixed top-0 left-0 right-0 z-50 h-16">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between">
        <!-- Left side -->
        <div class="flex items-center space-x-4">
            <!-- Sidebar toggle -->
            <button @click="$store.ui.sidebarOpen = !$store.ui.sidebarOpen"
                :class="!$store.ui.sidebarOpen && 'rotate-90'"
                title="Mostrar / ocultar menú"
                class="sidebar-toggle text-white/80 hover:text-gold-400 focus:outline-none text-xl transition duration-300">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <x-application-logo class="h-10 w-auto" on-dark />
            </a>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <a href="{{ route('notificaciones.index') }}" title="Notificaciones"
                class="text-white/80 hover:text-gold-400 focus:outline-none text-lg transition relative">
                <i class="fas fa-bell"></i>
                <span x-show="notifCount > 0"
                    x-text="notifCount > 99 ? '99+' : notifCount"
                    class="absolute -top-1.5 -right-2.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center"
                    style="display: none;"></span>
            </a>

            <!-- User dropdown -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                    class="flex items-center space-x-2 text-white/90 hover:text-white focus:outline-none transition group">
                    @if ($mostrarFoto)
                        <img src="{{ asset($userFoto) }}" alt="Foto de perfil"
                            class="w-8 h-8 rounded-full object-cover border border-white/30">
                    @else
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-semibold text-white">
                            {{ mb_strtoupper(mb_substr(session('user.nombre', '?'), 0, 1)) }}
                        </div>
                    @endif
                    <span class="hidden sm:block text-sm font-medium">{{ session('user.nombre', 'Usuario') }}</span>
                    <i class="fas fa-chevron-down text-xs transition" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Dropdown menu -->
                <div x-show="open"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    style="display: none;">
                    <div class="px-4 py-2 border-b border-gray-100">
                        @if ($mostrarFoto)
                            <div class="flex items-center gap-3 mb-2">
                                <img src="{{ asset($userFoto) }}" alt="Foto de perfil"
                                    class="w-9 h-9 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ session('user.nombre', 'Usuario') }}</p>
                                    <p class="text-xs text-gray-500">{{ session('user.email', '') }}</p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm font-semibold text-gray-800">{{ session('user.nombre', 'Usuario') }}</p>
                            <p class="text-xs text-gray-500">{{ session('user.email', '') }}</p>
                        @endif
                    </div>

                    <a href="{{ route('perfil.index') }}"
                        class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-user-edit w-5 text-gray-400"></i>
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
<aside x-data
    class="sidebar fixed top-16 left-0 h-[calc(100vh-4rem)] w-[260px] overflow-y-auto overflow-x-hidden z-40 transition-transform duration-300 hidden md:block"
    :class="$store.ui.sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

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

        <a href="{{ route('catalogo.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('catalogo.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-book w-5 text-center text-sm"></i>
            <span class="ml-3">Libros</span>
        </a>

        @if (session('rol') === 'ADMIN')
        <a href="{{ route('usuarios.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('usuarios.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-users w-5 text-center text-sm"></i>
            <span class="ml-3">Usuarios</span>
        </a>
        @endif

        @if (in_array(session('rol'), ['ESTUDIANTE', 'LECTOR'], true))
        <a href="{{ route('estudiante.mis-prestamos') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.mis-prestamos') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Préstamos</span>
        </a>
        <a href="{{ route('estudiante.escaneo-qr') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.escaneo-qr') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>
        <a href="{{ route('estudiante.mis-reservas') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.mis-reservas') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Reservas</span>
        </a>
        @else
        <a href="{{ route('prestamos.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('prestamos.index') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Préstamos</span>
        </a>

        <a href="{{ route('prestamos.renovaciones-pendientes') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('prestamos.renovaciones-pendientes') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-redo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Renovaciones</span>
        </a>

        <a href="{{ route('devoluciones.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('devoluciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-undo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Devoluciones</span>
        </a>

        <a href="{{ route('reservas.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('reservas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Reservas</span>
        </a>
        @endif

        <a href="{{ route('notificaciones.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('notificaciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-bell w-5 text-center text-sm"></i>
            <span class="ml-3">Notificaciones</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Gestión</p>

        @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO'], true))
        <a href="{{ route('qr-codigos.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('qr-codigos.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>

        <a href="{{ route('multas.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('multas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Multas</span>
        </a>

        <a href="{{ route('sanciones.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('sanciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-ban w-5 text-center text-sm"></i>
            <span class="ml-3">Sanciones</span>
        </a>

        <a href="{{ route('reportes.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('reportes.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-chart-bar w-5 text-center text-sm"></i>
            <span class="ml-3">Reportes</span>
        </a>
        @else
        <a href="{{ route('multas.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('multas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Multas</span>
        </a>
        @endif

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Sistema</p>

        @if (session('rol') === 'ADMIN')
        <a href="{{ route('auditoria.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('auditoria.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-history w-5 text-center text-sm"></i>
            <span class="ml-3">Auditoría</span>
        </a>
        @endif

        @if (session('rol') === 'ADMIN')
        <a href="{{ route('configuracion.index') }}"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('configuracion.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-cog w-5 text-center text-sm"></i>
            <span class="ml-3">Configuración</span>
        </a>
        @endif
    </nav>

    <!-- Sidebar footer -->
    <div class="px-5 py-3 border-t border-white/10 mt-2">
        <p class="text-[10px] text-white/30 text-center">SIGCB-QR v1.0</p>
    </div>
</aside>

<!-- ===== MOBILE SIDEBAR OVERLAY ===== -->
<div x-data x-show="$store.ui.sidebarOpen"
    @click="$store.ui.sidebarOpen = false"
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
<aside x-data x-show="$store.ui.sidebarOpen"
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

        <a href="{{ route('dashboard') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('dashboard') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-chart-pie w-5 text-center text-sm"></i>
            <span class="ml-3">Dashboard</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Biblioteca</p>

        <a href="{{ route('catalogo.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('catalogo.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-book w-5 text-center text-sm"></i>
            <span class="ml-3">Libros</span>
        </a>
        @if (session('rol') === 'ADMIN')
        <a href="{{ route('usuarios.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('usuarios.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-users w-5 text-center text-sm"></i>
            <span class="ml-3">Usuarios</span>
        </a>
        @endif
        @if (in_array(session('rol'), ['ESTUDIANTE', 'LECTOR'], true))
        <a href="{{ route('estudiante.mis-prestamos') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.mis-prestamos') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Préstamos</span>
        </a>
        <a href="{{ route('estudiante.escaneo-qr') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.escaneo-qr') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>
        <a href="{{ route('estudiante.mis-reservas') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('estudiante.mis-reservas') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Reservas</span>
        </a>
        @else
        <a href="{{ route('prestamos.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('prestamos.index') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exchange-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Préstamos</span>
        </a>
        <a href="{{ route('prestamos.renovaciones-pendientes') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('prestamos.renovaciones-pendientes') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-redo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Renovaciones</span>
        </a>
        <a href="{{ route('devoluciones.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('devoluciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-undo-alt w-5 text-center text-sm"></i>
            <span class="ml-3">Devoluciones</span>
        </a>
        <a href="{{ route('reservas.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('reservas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-calendar-check w-5 text-center text-sm"></i>
            <span class="ml-3">Reservas</span>
        </a>
        @endif
        <a href="{{ route('notificaciones.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('notificaciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-bell w-5 text-center text-sm"></i>
            <span class="ml-3">Notificaciones</span>
        </a>

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Gestión</p>

        @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO'], true))
        <a href="{{ route('qr-codigos.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('qr-codigos.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-qrcode w-5 text-center text-sm"></i>
            <span class="ml-3">Códigos QR</span>
        </a>
        <a href="{{ route('multas.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('multas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Multas</span>
        </a>
        <a href="{{ route('sanciones.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('sanciones.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-ban w-5 text-center text-sm"></i>
            <span class="ml-3">Sanciones</span>
        </a>
        <a href="{{ route('reportes.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('reportes.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-chart-bar w-5 text-center text-sm"></i>
            <span class="ml-3">Reportes</span>
        </a>
        @else
        <a href="{{ route('multas.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('multas.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-exclamation-triangle w-5 text-center text-sm"></i>
            <span class="ml-3">Mis Multas</span>
        </a>
        @endif

        <p class="px-3 text-[10px] font-semibold text-white/40 uppercase tracking-wider mt-4 mb-2">Sistema</p>

        @if (session('rol') === 'ADMIN')
        <a href="{{ route('auditoria.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('auditoria.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-history w-5 text-center text-sm"></i>
            <span class="ml-3">Auditoría</span>
        </a>
        @endif
        @if (session('rol') === 'ADMIN')
        <a href="{{ route('configuracion.index') }}" @click="$store.ui.sidebarOpen = false"
            class="nav-item flex items-center px-3 py-2.5 rounded-lg text-sm text-white/70 hover:text-white transition {{ request()->routeIs('configuracion.*') ? 'active text-white' : '' }}">
            <i class="nav-icon fas fa-cog w-5 text-center text-sm"></i>
            <span class="ml-3">Configuración</span>
        </a>
        @endif
    </nav>

    <div class="px-5 py-3 border-t border-white/10 mt-2">
        <p class="text-[10px] text-white/30 text-center">SIGCB-QR v1.0</p>
    </div>
</aside>
