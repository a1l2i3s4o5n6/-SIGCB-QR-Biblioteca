<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detalle de Usuario</h1>
                <p class="text-sm text-gray-500 mt-0.5">Usuario #{{ $usuario['id'] ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('usuarios.index') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <a href="{{ route('usuarios.edit', $usuario['id']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-500 hover:bg-blue-600 shadow transition">
                    <i class="fas fa-pen mr-2"></i> Editar
                </a>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 flex items-center">
                <i class="fas fa-user text-primary-400 mr-2"></i>
                Información del usuario
            </h3>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Nombre</p>
                <p class="text-sm text-gray-700">{{ $usuario['nombre'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Correo</p>
                <p class="text-sm text-gray-700">{{ $usuario['email'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Teléfono</p>
                <p class="text-sm text-gray-700">{{ $usuario['telefono'] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Rol</p>
                @php
                    $rolColor = match($usuario['rol'] ?? '') {
                        'ADMIN' => 'bg-red-50 text-red-700',
                        'BIBLIOTECARIO' => 'bg-blue-50 text-blue-700',
                        'ESTUDIANTE' => 'bg-green-50 text-green-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <span class="px-2 py-1 {{ $rolColor }} text-[11px] font-medium rounded-full">{{ $usuario['rol'] ?? '—' }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Estado</p>
                <span class="px-2 py-1 {{ ($usuario['activo'] ?? false) ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }} text-[11px] font-medium rounded-full">
                    {{ ($usuario['activo'] ?? false) ? 'ACTIVO' : 'INACTIVO' }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase font-medium mb-1">Fecha de registro</p>
                <p class="text-sm text-gray-700">{{ !empty($usuario['createdAt']) ? \Carbon\Carbon::parse($usuario['createdAt'])->format('d/m/Y H:i') : '—' }}</p>
            </div>
        </div>
    </div>
</x-app-layout>