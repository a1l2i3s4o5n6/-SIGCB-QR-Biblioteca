<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 text-sm"></i>
                </div>
                <x-text-input id="email"
                    class="block w-full pl-10 pr-3 py-2.5"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="correo@ejemplo.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ mostrar: false }">
            <x-input-label for="password" :value="__('Contraseña')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400 text-sm"></i>
                </div>
                <x-text-input id="password"
                    class="block w-full pl-10 pr-11 py-2.5"
                    x-bind:type="mostrar ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••" />
                <button type="button" @click="mostrar = !mostrar"
                    :aria-label="mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    title="Mostrar / ocultar contraseña"
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-primary-400 focus:outline-none transition">
                    <i class="fas text-sm" :class="mostrar ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-primary-400 shadow-sm focus:ring-primary-400"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-gold-400 hover:text-gold-500 font-medium transition duration-150"
                    href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Submit -->
        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                <i class="fas fa-sign-in-alt mr-2"></i>
                {{ __('Iniciar Sesión') }}
            </x-primary-button>
        </div>
    </form>

    <!-- Register link -->
    @if (Route::has('register'))
        <p class="text-center text-sm text-gray-500 mt-6">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-primary-400 hover:text-primary-500 font-semibold transition">
                Registrarse
            </a>
        </p>
    @endif
</x-guest-layout>
