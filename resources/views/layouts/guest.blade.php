<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'OrientationTech') }}</title>

        <link rel="icon" type="image/png" href="{{ $siteSettings?->favicon_path ? Storage::url($siteSettings->favicon_path) : asset('favicon.png') }}" />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @stack('styles')
    </head>
    <body class="font-sans antialiased text-slate-900 dark:text-slate-100">
        <div class="relative flex min-h-screen flex-col overflow-hidden bg-slate-950">
            <header class="relative z-10">
                @include('layouts.navbar')
            </header>

            <div class="pointer-events-none absolute inset-0 bg-cover bg-center opacity-70" style="background-image: url('{{ asset('images/img1.jpg') }}'); filter: blur(6px);"></div>
            <div class="pointer-events-none absolute inset-0 opacity-85" style="background: radial-gradient(circle at top left, rgba(59,130,246,.22), transparent 56%), radial-gradient(circle at bottom right, rgba(14,165,233,.18), transparent 48%), linear-gradient(140deg, rgba(15,23,42,.92) 0%, rgba(15,23,42,.88) 52%, rgba(17,24,39,.94) 100%);"></div>
            <div class="pointer-events-none absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 400 400\' fill=\'none\'%3E%3Cg opacity=\'0.05\' stroke=\'%2394a3b8\' stroke-width=\'0.6\'%3E%3Cpath d=\'M0 80h400M0 160h400M0 240h400M0 320h400\'/%3E%3Cpath d=\'M80 0v400M160 0v400M240 0v400M320 0v400\'/%3E%3C/g%3E%3C/svg%3E')] bg-[length:360px_360px] bg-center mix-blend-screen"></div>

            <main class="relative flex flex-1 items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>

        </div>
              @include('layouts.footer')

        @stack('scripts')
    </body>
</html>
