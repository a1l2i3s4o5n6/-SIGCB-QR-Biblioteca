<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Editar Usuario</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ $usuario['nombre'] ?? 'Usuario' }}</p>
            </div>
            <a href="{{ route('usuarios.show', $usuario['id']) }}"
                class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
        <form method="POST" action="{{ route('usuarios.update', $usuario['id']) }}">
            @csrf
            @method('PUT')
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nombre -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario['nombre'] ?? '') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                <!-- Correo -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $usuario['email'] ?? '') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                <!-- Contraseña -->
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
                    <input type="password" name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                        placeholder="Déjalo vacío para no cambiarla">
                    <p class="text-xs text-gray-400 mt-1">Solo se cambia si escribes una nueva.</p>
                </div>

                <!-- Teléfono -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario['telefono'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                </div>

                <!-- Rol -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rol <span class="text-red-500">*</span></label>
                    <select name="rolId" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 bg-white">
                        @foreach ($roles as $rolId => $rol)
                            <option value="{{ $rolId }}" @selected(old('rolId', $usuario['rol'] ?? '') == $rol)>{{ $rol }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Activo -->
                <div class="sm:col-span-2 flex items-center gap-2 pt-2">
                    <input type="checkbox" name="activo" value="1" id="activo"
                        class="w-4 h-4 rounded border-gray-300 text-primary-400 focus:ring-primary-400" @checked(old('activo', $usuario['activo'] ?? false))>
                    <label for="activo" class="text-sm text-gray-700">Cuenta activa</label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a href="{{ route('usuarios.show', $usuario['id']) }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Actualizar Usuario
                </button>
            </div>
        </form>
    </div>
</x-app-layout>