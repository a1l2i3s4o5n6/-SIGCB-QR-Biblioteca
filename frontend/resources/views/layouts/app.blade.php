<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SIGCB-QR') }} — @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <!-- Navigation + Sidebar -->
    @include('layouts.navigation')

    <!-- Main Content -->
    <div class="pt-16 md:pl-[260px] transition-all duration-300">
        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main class="p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-xs text-gray-500">
                    &copy; {{ date('Y') }} <span class="text-primary-400 font-semibold">SIGCB-QR</span> — Todos los derechos reservados
                </p>
                <p class="text-xs text-gray-400">
                    Versión 1.0 | Biblioteca Universitaria
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
