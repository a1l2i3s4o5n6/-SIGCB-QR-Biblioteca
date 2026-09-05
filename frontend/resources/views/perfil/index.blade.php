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

    @php
        $fotoPerfil = $perfil['foto'] ?? '';
        $tieneFotoReal = !empty($fotoPerfil)
            && (preg_match('#^https?://#i', $fotoPerfil) || file_exists(public_path(trim($fotoPerfil, '/'))));
    @endphp

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl"
         data-foto-actual="{{ $tieneFotoReal ? $fotoPerfil : '' }}"
         x-data="{
             fotoPreview: '',
             quitar: @json(old('quitar_foto') ? true : false),
             urlValue: '',
             tieneActual: @json($tieneFotoReal),
             init() {
                 const actual = this.$el.dataset.fotoActual || '';
                 if (!this.quitar && actual) this.fotoPreview = actual;
             },
             onFile(e) {
                 const file = e.target.files && e.target.files[0];
                 if (!file) return;
                 this.quitar = false;
                 this.fotoPreview = URL.createObjectURL(file);
             },
             onUrl(event) {
                 this.urlValue = event.target.value;
                 const url = this.urlValue.trim();
                 if (url) {
                     this.quitar = false;
                     this.fotoPreview = url;
                 }
             },
             limpiarUrl() {
                 this.urlValue = '';
                 this.quitar = false;
                 this.fotoPreview = this.$el.dataset.fotoActual || '';
             },
             eliminarFoto() {
                 this.quitar = true;
                 this.fotoPreview = '';
                 this.urlValue = '';
                 if (this.$refs.fotoFile) this.$refs.fotoFile.value = '';
                 if (this.$refs.fotoUrl) this.$refs.fotoUrl.value = '';
             }
         }">

        <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="p-6 grid grid-cols-1 gap-5">
                <!-- Foto de perfil -->
                <div class="p-5 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/70 flex flex-col sm:flex-row items-center gap-5">
                    <div class="relative shrink-0">
                        <img x-show="!quitar && (fotoPreview || tieneActual)"
                            x-bind:src="fotoPreview"
                            src="@if ($tieneFotoReal){{ asset($fotoPerfil) }}@endif"
                            alt="Foto de perfil"
                            class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md"
                            style="{{ !$tieneFotoReal ? 'display:none' : '' }}">
                        <div x-show="quitar || !(fotoPreview || tieneActual)"
                            class="w-24 h-24 rounded-full bg-primary-100 flex items-center justify-center text-3xl font-bold text-primary-600 border-4 border-white shadow-sm"
                            style="{{ $tieneFotoReal && empty(old('quitar_foto')) ? 'display:none' : '' }}">
                            {{ mb_strtoupper(mb_substr(old('nombre', $perfil['nombre'] ?? '?'), 0, 1)) }}
                        </div>
                        <span x-show="quitar"
                            class="absolute -bottom-1 left-1/2 -translate-x-1/2 px-2 py-0.5 rounded-full bg-red-500 text-white text-[10px] font-semibold uppercase tracking-wide"
                            style="display: none;">eliminada</span>
                    </div>

                    <div class="flex-1 w-full space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-1">Foto de perfil</p>
                            <p class="text-xs text-gray-400">JPG, PNG, WEBP o GIF · máximo 2 MB</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <label for="foto"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-primary-700 bg-primary-50 border border-primary-200 hover:bg-primary-100 cursor-pointer transition">
                                <i class="fas fa-upload"></i> Subir nueva foto
                            </label>
                            <input type="file" name="foto" id="foto" accept="image/*" class="hidden"
                                x-ref="fotoFile" @change="onFile($event)">

                            <button type="button" @click="eliminarFoto()"
                                x-show="quitar || fotoPreview || @json($tieneFotoReal)"
                                x-transition
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition"
                                style="display: none;">
                                <i class="fas fa-trash-alt"></i> Eliminar foto
                            </button>
                        </div>

                        <div>
                            <label for="foto_url" class="block text-sm font-medium text-gray-700 mb-1.5">...o pega una URL de imagen</label>
                            <div class="flex gap-2">
                                <input type="url" name="foto_url" id="foto_url" x-ref="fotoUrl" @input="onUrl($event)"
                                    value="{{ old('foto_url') }}"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-400"
                                    placeholder="https://ejemplo.com/foto.jpg">
                                <button type="button" @click="limpiarUrl()" x-show="urlValue !== ''"
                                    class="px-3 py-2 rounded-lg text-sm text-gray-500 hover:text-gray-700 border border-gray-200 hover:bg-gray-100 transition"
                                    title="Limpiar URL" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <input type="checkbox" name="quitar_foto" value="1" x-ref="quitarCheck" x-model="quitar" class="hidden">
                        <p x-show="quitar" class="text-xs font-medium text-red-500" style="display: none;">
                            <i class="fas fa-info-circle mr-1"></i>Tu foto se eliminará al guardar los cambios.
                        </p>
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