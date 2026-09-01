<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-col sm:flex-row gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Notificaciones</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) ? 'Centro de notificaciones del sistema' : 'Tus notificaciones' }}
                </p>
            </div>
            @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']) && $noLeidas > 0)
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 rounded-lg bg-primary-400 text-white text-sm font-medium hover:bg-primary-500 transition">
                        <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
                    </button>
                </form>
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
                <span class="block">{{ $error }}</span>
            @endforeach
        </div>
    @endif

    @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
    <!-- Crear notificación -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-4"><i class="fas fa-paper-plane mr-2 text-gold-400"></i>Enviar notificación</h2>
        <form method="POST" action="{{ route('notificaciones.store') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-4">
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
                <select name="tipo"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
                    @foreach (['INFO', 'SANCION', 'PRESTAMO', 'RESERVA', 'SISTEMA'] as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo', 'INFO') === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Título</label>
                <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="200" placeholder="Título de la notificación"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Mensaje</label>
                <input type="text" name="mensaje" value="{{ old('mensaje') }}" required placeholder="Mensaje corto"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-300 focus:border-primary-400 outline-none">
            </div>
            <div class="lg:col-span-1 flex items-end">
                <button type="submit"
                    class="w-full px-4 py-2 rounded-lg bg-primary-400 text-white text-sm font-medium hover:bg-primary-500 transition">
                    <i class="fas fa-paper-plane mr-1"></i> Enviar
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Lista de notificaciones -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse ($notificaciones as $n)
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3 {{ !($n['leida'] ?? false) ? 'bg-gold-400/5' : '' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        @if (!($n['leida'] ?? false))
                            <span class="w-2 h-2 rounded-full bg-gold-400 flex-shrink-0" title="No leída"></span>
                        @endif
                        <span class="text-sm font-bold text-gray-800 truncate">{{ $n['titulo'] ?? '—' }}</span>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] font-mono rounded-full">{{ $n['tipo'] ?? 'INFO' }}</span>
                        @if (in_array(session('rol'), ['ADMIN', 'BIBLIOTECARIO']))
                            <span class="text-[11px] text-gray-400">para <strong class="text-gray-600">{{ $n['usuarioNombre'] ?? '—' }}</strong></span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-600 truncate">{{ $n['mensaje'] ?? '' }}</p>
                    <p class="text-[11px] text-gray-400 mt-1">
                        <i class="far fa-clock mr-1"></i>{{ !empty($n['createdAt']) ? \Carbon\Carbon::parse($n['createdAt'])->format('d/m/Y H:i') : '—' }}
                    </p>
                </div>
                @if (!($n['leida'] ?? false))
                    <form method="POST" action="{{ route('notificaciones.leida', $n['id']) }}">
                        @csrf
                        <button type="submit" title="Marcar como leída"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 text-gray-500 hover:bg-primary-50 hover:text-primary-400 transition">
                            <i class="fas fa-envelope-open text-xs"></i>
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <i class="fas fa-bell-slash text-gray-300 text-3xl mb-3"></i>
                <p class="text-gray-400">No hay notificaciones.</p>
            </div>
        @endforelse

        @if ($totalPages > 1)
            <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    Mostrando <span class="font-semibold">{{ count($notificaciones) }}</span> de {{ number_format($total) }} notificaciones · Página {{ $page + 1 }} de {{ $totalPages }}
                </p>
                <div class="flex gap-1.5">
                    @if (!$first)
                        <a href="{{ route('notificaciones.index', ['page' => $page - 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">← Anterior</a>
                    @endif
                    @if (!$last)
                        <a href="{{ route('notificaciones.index', ['page' => $page + 1, 'size' => $size]) }}"
                            class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-sm hover:bg-gray-200 transition">Siguiente →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>