<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mentions légales — {{ config('app.name', 'OrientationTech') }}</title>
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif

              <!-- Favicon -->
  <link rel="icon" type="image/png" href="{{ $siteSettings?->favicon_path ? Storage::url($siteSettings->favicon_path) : asset('favicon.png') }}" />

</head>
<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">

  @include('layouts.navbar')

  <main class="max-w-5xl mx-auto px-4 py-16 sm:py-24">
    <h1 class="text-3xl md:text-4xl font-bold mb-6 dark:text-white">{{ __('pages.legal.title') }}</h1>

    <div class="prose prose-blue max-w-none dark:prose-invert space-y-6">
      {{-- Keep dynamic address/email while using the translated HTML for the rest --}}
      {!! __('pages.legal.content_html') !!}


      <p class="text-sm text-gray-500 dark:text-gray-400 mt-6">{{ __('pages.legal.last_updated') }}</p>
    </div>
  </main>

  @include('layouts.footer')

</body>
</html>
