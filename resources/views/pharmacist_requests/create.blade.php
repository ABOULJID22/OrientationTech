<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Demande profil Pharmacien — {{ config('app.name', 'Offitrade') }}</title>
  <script>
    (function() {
      try {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        const useDark = saved ? (saved === 'dark') : prefersDark;
        document.documentElement.classList.toggle('dark', !!useDark);
      } catch (e) {}
    })();
  </script>
  @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  @endif
  <link rel="icon" type="image/png" href="{{ $siteSettings?->favicon_path ? Storage::url($siteSettings->favicon_path) : asset('favicon.png') }}" />
</head>
<body class="bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">
  @includeIf('layouts.navbar')

  <div class="relative isolate max-w-5xl mx-auto py-16 mt-16 px-4 sm:px-6 lg:px-8">
    <div class="absolute inset-0 -z-10 overflow-hidden">
      <div class="absolute left-1/2 top-6 h-72 w-72 -translate-x-1/2 rounded-full bg-blue-500/10 blur-3xl dark:bg-blue-400/10"></div>
      <div class="absolute right-10 bottom-10 h-48 w-48 rounded-full bg-indigo-400/10 blur-2xl dark:bg-indigo-500/10"></div>
    </div>

    <div class="mb-10 flex flex-col gap-4 text-center sm:text-left">
      <span class="inline-flex items-center gap-2 self-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-blue-700 dark:bg-blue-900/40 dark:text-blue-200 sm:self-start">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2.09 6.26H20l-5.17 3.76 1.97 6.03L12 15.27l-4.8 3.78 1.97-6.03L4 9.26h5.91L12 3z" />
        </svg>
        {{ __('pharmacist_request.badge_title') ?? 'Accès sécurisé Offitrade' }}
      </span>
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">{{ __('pharmacist_request.title') }}</h1>
        <p class="mt-3 max-w-2xl text-base text-gray-600 dark:text-gray-300">{{ __('pharmacist_request.subtitle') ?? 'Complétez le formulaire pour demander l’activation de votre espace Pharmacien et profitez d’un suivi personnalisé.' }}</p>
      </div>
    </div>

    @php
      $submitted = session('status');
      $hasPending = isset($pendingRequest) && $pendingRequest;
      $isApproved = isset($approvedRequest) && $approvedRequest;
      $disabledAttr = $hasPending ? 'disabled' : '';
      $readonlyAttr = $hasPending ? 'readonly' : '';

    // convenience flags (roles and name)
    $userIsClient = isset($user) && method_exists($user, 'hasRole') ? $user->hasRole('client') : false;
    $userIsUser = isset($user) && method_exists($user, 'hasRole') ? $user->hasRole('user') : false;
    // consider a user to have a pharmacy name if either pharmacist_name or pharmacy_name is set
    $hasPharmacyName = isset($user) && (! empty($user->pharmacist_name) || ! empty($user->pharmacy_name));

    // Display rules (priority):
    // 1) dashboard: client + approved + hasPharmacyName
    // 2) deactivated/contact: approved + user + not client + hasPharmacyName
    // 3) request form: user + not client (guests also see the form)

    if (! isset($user)) {
      // Guest: show the form
      $displayForm = true;
    } else {
      // Authenticated user: default hidden
      $displayForm = false;

      // Rule 2 (deactivated) has priority over the form: if approved & user & not client & has name => no form
      if ($isApproved && $userIsUser && ! $userIsClient && $hasPharmacyName) {
        $displayForm = false;
      }

      // Rule 3 (request form): user and not client
      if ($userIsUser && ! $userIsClient) {
        $displayForm = true;
      }
    }
    @endphp

    @if ($errors->has('pharmacy_name') || $errors->has('pharmacist_name'))
      <div class="mb-6 p-4 rounded border border-red-300 bg-red-50 text-red-900 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
        <div class="font-semibold mb-1">{{ __('pharmacist_request.pharmacy_name_taken_title') }}</div>
        <div>{{ $errors->first('pharmacist_name') ?? $errors->first('pharmacy_name') }}</div>
      </div>
    @endif

    {{-- Handle three main states based on active flag and role:
         1) active + client => show dashboard link (access)
         2) active + not client => show request form (displayed below)
         3) inactive => show deactivated/contact support message (do not show form)
         Additionally show pending/approved messages when relevant. --}}

    @if (isset($user) && $isApproved && $userIsUser && ! $userIsClient && $hasPharmacyName)
      <div class="mb-6 p-4 rounded border border-red-300 bg-red-50 text-red-900 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200">
        <div class="font-semibold mb-1">{{ __('pharmacist_request.page.deactivated_title') ?? 'Compte désactivé' }}</div>
        <div class="mb-3">{{ __('pharmacist_request.page.deactivated_message') ?? 'Votre compte a été désactivé par un administrateur. Si vous avez besoin de soumettre une nouvelle demande, contactez le support ci-dessous.' }}</div>

        <div class="mt-2">
          <h4 class="text-lg font-semibold dark:text-white">{{ __('site.contact.email') }}</h4>
          <p class="dark:text-gray-300">{{ $siteSettings?->email ?? 'contact@offitrade.fr' }}</p>
        </div>

        <div class="mt-3">
          <a href="{{ url('/') }}#contact" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('pharmacist_request.page.contact_support') ?? 'Contacter le support' }}</a>
        </div>
      </div>

      

    @else
      {{-- User is other cases or guest: handle approved/pending/status --}}
  @if (isset($user) && $userIsClient && $isApproved && $hasPharmacyName)
        <div class="mb-6 p-4 rounded border border-green-300 bg-green-50 text-green-900 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200">
          <div class="font-semibold mb-1">{{ __('pharmacist_request.page.approved_title') }}</div>
          <div>{{ __('pharmacist_request.page.approved_message') }}</div>
          <div class="mt-3">
            <a href="{{ route('filament.admin.pages.dashboard') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">{{ __('pharmacist_request.page.go_to_space') }}</a>
          </div>
        </div>
      @elseif ($submitted || $hasPending)
        <div class="mb-6 p-4 rounded border border-yellow-300 bg-yellow-50 text-yellow-900 dark:border-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200">
          <div class="font-semibold mb-1">{{ __('pharmacist_request.page.pending_title') }}</div>
          <div>{{ __('pharmacist_request.page.pending_message') }}</div>
        </div>
      @endif

    @endif

  @if ($displayForm)
    @unless ($isApproved)
    <div class="rounded-2xl border border-gray-100 bg-white shadow-xl shadow-blue-500/5 ring-1 ring-gray-900/5 transition hover:shadow-2xl hover:ring-blue-500/10 dark:border-gray-700/70 dark:bg-gray-900 dark:ring-gray-700/60">
      <div class="grid gap-10 p-6 sm:p-10 lg:grid-cols-3">
        <div class="flex flex-col gap-6">
          <div class="rounded-xl border border-blue-100 bg-blue-50/60 p-6 text-blue-800 dark:border-blue-500/30 dark:bg-blue-500/5 dark:text-blue-200">
            <h2 class="text-lg font-semibold">{{ __('pharmacist_request.sidebar.title') ?? 'Pourquoi créer un profil ?' }}</h2>
            <p class="mt-2 text-sm leading-6">{{ __('pharmacist_request.sidebar.description') ?? 'Accédez à la plateforme Offitrade pour gérer vos demandes, consulter vos échanges et collaborer plus facilement avec nos équipes.' }}</p>
            <ul class="mt-4 space-y-3 text-sm">
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-blue-600 shadow dark:bg-blue-500/10">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
                <span>{{ __('pharmacist_request.sidebar.point_one') ?? 'Suivi des demandes et des dossiers en temps réel.' }}</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-blue-600 shadow dark:bg-blue-500/10">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 00-2-2H5a2 2 0 010-4h2a2 2 0 002-2V7a2 2 0 014 0v2a2 2 0 002 2h2a2 2 0 010 4h-2a2 2 0 00-2 2v2a2 2 0 11-4 0z" />
                  </svg>
                </span>
                <span>{{ __('pharmacist_request.sidebar.point_two') ?? 'Espace sécurisé pour partager vos documents et informations.' }}</span>
              </li>
              <li class="flex items-start gap-3">
                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white text-blue-600 shadow dark:bg-blue-500/10">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-10 10-4-4-6 6" />
                  </svg>
                </span>
                <span>{{ __('pharmacist_request.sidebar.point_three') ?? 'Accompagnement prioritaire par nos équipes support.' }}</span>
              </li>
            </ul>
          </div>
          <div class="rounded-xl border border-dashed border-gray-300 p-5 dark:border-gray-600">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">{{ __('pharmacist_request.sidebar.need_help_title') ?? 'Besoin d’aide ?' }}</h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('pharmacist_request.sidebar.need_help_text') ?? 'Notre équipe est disponible pour vous accompagner lors de la création de votre espace Pharmacien.' }}</p>
            <a href="{{ url('/contact') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-300 dark:hover:text-blue-200">
              {{ __('pharmacist_request.sidebar.contact_support') ?? 'Contacter le support' }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>

        <div class="lg:col-span-2">
          <form method="POST" action="{{ route('pharmacist.request.store') }}" class="flex h-full flex-col gap-6" novalidate>
            @csrf

            {{-- Honeypot field to deter simple bots (should remain hidden) --}}
            <input type="text" name="website" value="" autocomplete="off" tabindex="-1" class="hidden" />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
              <div>
                <label class="flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-200">
                  <span>{{ __('pharmacist_request.applicant_name') }}</span>
                  @if ($hasPending)
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">{{ __('pharmacist_request.pending_field') ?? 'En cours' }}</span>
                  @endif
                </label>
                <input type="text" name="applicant_name" value="{{ old('applicant_name', $pendingRequest->applicant_name ?? (auth()->user()->name ?? '')) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="100" required aria-required="true" aria-label="{{ __('pharmacist_request.applicant_name') }}">
                @error('applicant_name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-200">
                  <span>{{ __('pharmacist_request.applicant_email') }}</span>
                  @if ($hasPending)
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">{{ __('pharmacist_request.pending_field') ?? 'En cours' }}</span>
                  @endif
                </label>
                <input type="email" name="applicant_email" value="{{ old('applicant_email', $pendingRequest->applicant_email ?? (auth()->user()->email ?? '')) }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="150" required aria-required="true" aria-label="{{ __('pharmacist_request.applicant_email') }}">
                @error('applicant_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('pharmacist_request.phone') }}</label>
                <input type="tel" name="phone" value="{{ old('phone', $pendingRequest->phone ?? '') }}" pattern="[0-9+()\s-]{6,25}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="25" aria-label="{{ __('pharmacist_request.phone') }}">
                @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('pharmacist_request.pharmacist_name') ?: 'Nom de la pharmacie' }}</label>
                <input type="text" name="pharmacist_name" value="{{ old('pharmacist_name', $pendingRequest->pharmacy_name ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="120" required aria-required="true" aria-label="{{ __('pharmacist_request.pharmacist_name') ?: 'Nom de la pharmacie' }}">
                @if ($errors->has('pharmacist_name') || $errors->has('pharmacy_name'))
                  <p class="mt-1 text-xs text-red-500">{{ $errors->first('pharmacist_name') ?? $errors->first('pharmacy_name') }}</p>
                @endif
              </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
              <div class="lg:col-span-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('pharmacist_request.pharmacy_address') }}</label>
                <textarea name="pharmacy_address" rows="2" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="250" aria-label="{{ __('pharmacist_request.pharmacy_address') }}">{{ old('pharmacy_address', $pendingRequest->pharmacy_address ?? '') }}</textarea>
                @error('pharmacy_address')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
              <div class="lg:col-span-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('pharmacist_request.message_optional') }}</label>
                <textarea name="message" rows="4" class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm shadow-sm transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-50 dark:focus:border-blue-400 dark:focus:ring-blue-400/20" {{ $readonlyAttr }} {{ $disabledAttr }} maxlength="1000" aria-label="{{ __('pharmacist_request.message_optional') }}">{{ old('message', $pendingRequest->message ?? '') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
              </div>
            </div>

            <div class="mt-auto flex flex-col-reverse items-center justify-between gap-4 border-t border-gray-200 pt-6 dark:border-gray-700 sm:flex-row">
              @if ($hasPending)
                <div class="inline-flex items-center gap-2 rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-400/20 dark:text-yellow-200">
                  <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-yellow-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-yellow-500"></span>
                  </span>
                  {{ __('pharmacist_request.status.already_pending') }}
                </div>
              @endif

              @unless($hasPending)
                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition hover:-translate-y-0.5 hover:bg-blue-500 hover:shadow-blue-500/40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:bg-blue-500 dark:hover:bg-blue-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0113.607-4.5M21 12a9 9 0 11-9-9" />
                  </svg>
                  {{ __('pharmacist_request.submit') }}
                </button>
              @endunless
            </div>
          </form>
        </div>
      </div>
    </div>
    @endunless
  @else
    
  
  @endif

    @if (session('status'))
      <div class="mt-4 p-3 bg-green-100 text-green-800 rounded">{{ session('status') }}</div>
    @endif
  </div>

  @includeIf('layouts.footer')
  <script>
    (function() {
      var btn = document.getElementById('themeToggle');
      if (!btn) return;
      btn.addEventListener('click', function() {
        var html = document.documentElement;
        var isDark = html.classList.toggle('dark');
        try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch (e) {}
        // Swap button labels
        btn.querySelector('.dark\\:block')?.classList.toggle('hidden', !isDark);
        btn.querySelector('.dark\\:hidden, .dark\\:block ~ .block')?.classList;
      });
    })();
  </script>
</body>
</html>