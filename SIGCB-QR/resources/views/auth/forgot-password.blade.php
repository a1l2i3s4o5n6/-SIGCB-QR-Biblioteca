<x-guest-layout>
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-full bg-gold-50 flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-key text-gold-400 text-xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">¿Olvidaste tu contraseña?</h3>
        <p class="text-sm text-gray-500 mt-1">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 text-sm"></i>
                </div>
                <x-text-input id="email" class="block w-full pl-10" type="email" name="email" :value="old('email')" required autofocus placeholder="correo@ejemplo.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-gold-400 hover:text-gold-500 font-medium transition" href="{{ route('login') }}">
                <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
            </a>

            <x-primary-button>
                <i class="fas fa-paper-plane mr-2"></i>
                {{ __('Enviar enlace') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
