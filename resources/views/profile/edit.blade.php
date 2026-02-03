<x-app-layout>
    @php($user = Auth::user())

    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
     
        </div>
    </x-slot>

    <div class="relative isolate overflow-hidden bg-[#b0cae0]/20 dark:bg-slate-900">
        <div aria-hidden="true" class="pointer-events-none absolute -top-40 right-0 h-72 w-72 rounded-full bg-gradient-to-br from-[#4f6ba3]/20 via-[#5b7db5]/15 to-[#8aaed0]/20 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 left-4 h-64 w-64 rounded-full bg-gradient-to-tr from-[#5b7db5]/20 via-[#8aaed0]/15 to-[#b0cae0]/25 blur-3xl"></div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 relative">

                <section class="rounded-3xl bg-gradient-to-br from-[#4f6ba3] via-[#5b7db5] to-[#8aaed0] px-6 sm:px-10 py-8 shadow-2xl shadow-blue-900/40 text-white ring-1 ring-white/10">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-8">
                        <div class="relative">
                            <div class="absolute -inset-1 rounded-full bg-white/15 blur-lg"></div>
                            <img src="{{ $user->avatar }}" alt="Avatar" class="relative w-24 h-24 sm:w-28 sm:h-28 lg:w-32 lg:h-32 rounded-3xl object-cover border-4 border-white/70 shadow-xl">
                        </div>
                        <div class="flex-1 space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-2xl sm:text-3xl font-semibold drop-shadow-lg">{{ $user->name }}</h3>
                                <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                                    {{ $user->isClient() ? __('profile.status.client') : __('profile.status.user') }}
                                </span>
                            </div>
                            <p class="text-sm sm:text-base text-white/80">
                                {{ $user->email }}
                            </p>
                            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-white/85">
                                <div class="rounded-2xl bg-white/20 border border-white/30 px-4 py-3 backdrop-blur">
                                    <dt class="text-white/70 uppercase tracking-wide text-xs">{{ __('profile.summary.last_updated') }}</dt>
                                    <dd class="mt-1 font-semibold">
                                        {{ $user->updated_at ? $user->updated_at->locale(app()->getLocale())->isoFormat('D MMM YYYY HH:mm') : __('profile.summary.missing') }}
                                    </dd>
                                </div>
                                <div class="rounded-2xl bg-[#5b7db5]/30 border border-white/20 px-4 py-3 backdrop-blur">
                                    <dt class="text-white/70 uppercase tracking-wide text-xs">{{ __('profile.summary.phone') }}</dt>
                                    <dd class="mt-1 font-semibold">
                                        {{ $user->phone ?: __('profile.summary.missing') }}
                                    </dd>
                                </div>
                                <div class="rounded-2xl bg-[#8aaed0]/40 border border-white/20 px-4 py-3 backdrop-blur">
                                    <dt class="text-white/70 uppercase tracking-wide text-xs">{{ __('profile.summary.created_at') }}</dt>
                                    <dd class="mt-1 font-semibold">
                                        {{ $user->created_at->format('d M Y') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </section>

                <div class="grid grid-cols-1 xl:grid-cols-[2fr,1fr] gap-8">
                    <div class="space-y-8">
                        <div class="rounded-3xl bg-white/90 dark:bg-slate-900/70 shadow-xl shadow-slate-900/10 ring-1 ring-[#b0cae0]/60 dark:ring-slate-700/60 backdrop-blur">
                            <div class="p-6 sm:p-8">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>

                        <div class="rounded-3xl bg-white/90 dark:bg-slate-900/70 shadow-xl shadow-slate-900/10 ring-1 ring-[#b0cae0]/60 dark:ring-slate-700/60 backdrop-blur">
                            <div class="p-6 sm:p-8">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>

                    <aside class="space-y-6">
                        <div class="rounded-3xl bg-white/90 dark:bg-slate-900/70 shadow-xl shadow-slate-900/10 ring-1 ring-[#b0cae0]/60 dark:ring-slate-700/60 backdrop-blur p-6 sm:p-8">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="h-9 w-9 rounded-2xl bg-[#4f6ba3]/10 text-[#4f6ba3] dark:text-[#8aaed0] flex items-center justify-center">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z" />
                                    </svg>
                                </span>
                                {{ __('profile.sidebar.primary_contacts') }}
                            </h3>
                            <dl class="mt-6 space-y-4 text-sm text-slate-600 dark:text-slate-300">
                                <div class="flex items-start justify-between">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">{{ __('profile.summary.address') }}</dt>
                                    <dd class="max-w-[65%] text-right">{{ $user->address ?: __('profile.summary.missing') }}</dd>
                                </div>
                                <div class="flex items-start justify-between">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">{{ __('profile.summary.city') }}</dt>
                                    <dd class="max-w-[65%] text-right">{{ $user->city ?: __('profile.summary.missing') }}</dd>
                                </div>
                                <div class="flex items-start justify-between">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">{{ __('profile.summary.country') }}</dt>
                                    <dd class="max-w-[65%] text-right">{{ $user->country ?: __('profile.summary.missing') }}</dd>
                                </div>
                                <div class="flex items-start justify-between">
                                    <dt class="font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide text-xs">{{ __('profile.summary.phone_secondary') }}</dt>
                                    <dd class="max-w-[65%] text-right">{{ $user->phone_2 ?: __('profile.summary.missing') }}</dd>
                                </div>
                            </dl>
                        </div>


                    </aside>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .dark .shadow-blue-900\/40 {
                box-shadow: 0 25px 60px rgba(15, 23, 42, 0.65);
            }
            /* Force file input placeholder text color to black in light mode
               and white in dark mode for better contrast */
            input[type=file]::file-selector-button {
                /* keep default button styles */
            }

            /* browsers display "No file chosen" outside the button; style it */
            input[type=file] {
                color: #000; /* default text color (light mode) */
            }

            .dark input[type=file] {
                color: #fff; /* dark mode: white text */
            }
        </style>
    @endpush
</x-app-layout>