<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Mi Perfil</h1>
                <p class="text-sm text-gray-500 mt-0.5">Edita tus datos personales</p>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-700 uppercase tracking-wide">
                {{ session('rol', 'LECTOR') }}
            </span>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
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

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl"
         x-data="{
             fotoPreview: '',
             init() {
                 const actual = @json(old('quitar_foto') ? '' : ($perfil['foto'] ?? ''));
                 if (actual) this.fotoPreview = actual;
             },
             onFile(e) {
                 const file = e.target.files && e.target.files[0];
                 if (file) this.fotoPreview = URL.createObjectURL(file);
             }
         }">

        <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="p-6 grid grid-cols-1 gap-5">
                <!-- Foto de perfil -->
                <div class="flex items-start gap-5">
                    <div class="shrink-0">
                        <template x-if="fotoPreview">
                            <img :src="fotoPreview" alt="Foto de perfil"
                                class="w-20 h-20 rounded-full object-cover border-2 border-primary-200">
                        </template>
                        <template x-if="!fotoPreview">
                            <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center text-2xl font-bold text-primary-600">
                                {{ substr(old('nombre', $perfil['nombre'] ?? '?'), 0, 1) }}
                            </div>
                        </template>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Foto de perfil</label>
                        <input type="file" name="foto" accept="image/*" @change="onFile($event)"
                            class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-400 mt-1.5">JPG, PNG, WEBP o GIF · máximo 2 MB</p>

                        <div class="mt-4">
                            <label for="foto_url" class="block text-sm font-medium text-gray-700 mb-1.5">...o pega una URL de imagen</label>
                            <input type="url" name="foto_url" id="foto_url" value="{{ old('foto_url') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                placeholder="https://ejemplo.com/foto.jpg">
                        </div>

                        @if (!empty($perfil['foto']))
                            <div class="mt-3 flex items-center gap-2">
                                <input type="checkbox" name="quitar_foto" value="1" id="quitar_foto"
                                    class="w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400" @checked(old('quitar_foto'))>
                                <label for="quitar_foto" class="text-sm text-gray-600">Quitar mi foto actual</label>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Nombre -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $perfil['nombre'] ?? '') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                    </div>

                    <!-- Correo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $perfil['email'] ?? '') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $perfil['telefono'] ?? '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
                    </div>
                </div>

                <!-- Cambiar contraseña -->
                <div class="border-t border-gray-100 pt-5">
                    <p class="text-sm font-semibold text-gray-800 mb-1">Cambiar contraseña</p>
                    <p class="text-xs text-gray-400 mb-4">Déjala vacía para mantener la actual. Para cambiarla debes escribir tu contraseña actual.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="password_actual" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Contraseña actual <span class="text-red-500" x-show="$refs.pwNueva.value !== ''">*</span>
                            </label>
                            <input type="password" name="password_actual" id="password_actual"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                placeholder="Tu contraseña actual">
                        </div>
                        <div>
                            <label for="password_nueva" class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
                            <input type="password" name="password_nueva" id="password_nueva" x-ref="pwNueva"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                placeholder="Mínimo 6 caracteres">
                        </div>
                        <div>
                            <label for="password_nueva_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar nueva contraseña</label>
                            <input type="password" name="password_nueva_confirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                placeholder="Repite la nueva contraseña">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition">Cancelar</a>
                <button type="submit"
                    class="btn-primary-custom px-6 py-2 rounded-lg text-sm font-semibold text-white shadow">
                    <i class="fas fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</x-app-layout>