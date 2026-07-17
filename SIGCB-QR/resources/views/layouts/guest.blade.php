<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIGCB-QR') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="auth-bg min-h-screen flex flex-col justify-center items-center px-4 py-8">
        <div class="auth-card w-full max-w-md">
            <!-- Card principal -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Borde dorado superior -->
                <div class="auth-card-top"></div>

                <div class="px-8 py-8">
                    <!-- Logo -->
                    <div class="flex justify-center mb-6">
                        <x-application-logo class="h-14 w-auto" />
                    </div>

                    <h2 class="text-center text-2xl font-bold text-gray-800 mb-1">Bienvenido</h2>
                    <p class="text-center text-sm text-gray-500 mb-6">Inicia sesión en el sistema</p>

                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-white/70 text-xs mt-6">
                &copy; {{ date('Y') }} SIGCB-QR &mdash; Sistema Integral de Gestión, Control y Seguimiento de Lectura
            </p>
        </div>
    </div>
</body>
</html>
