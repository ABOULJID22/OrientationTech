<x-guest-layout>
    <div class="relative min-h-screen overflow-hidden">

        <div class="relative flex min-h-screen items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-[#b8cbe1] bg-white/96 shadow-[0_28px_50px_-18px_rgba(79,107,163,0.32)] ring-1 ring-[#dce6f4]/80 backdrop-blur-xl dark:border-slate-700 dark:bg-slate-900/95 dark:ring-slate-700/70">
                <div class="relative px-8 py-12 sm:px-12">
                    <div class="absolute right-8 top-8 hidden text-xs font-medium tracking-[0.25em] text-slate-400 dark:text-[#94a6c6] sm:block">
                        {{ now()->format('d M Y') }}
                    </div>

                    <div class="mb-10 flex flex-col items-center text-center">
                        <svg class="mb-6 h-16 w-16 text-[#4f6ba3] dark:text-[#8aaed0]" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2.5" opacity="0.25" />
                            <path d="M22 30.5L30.2 38.5L44 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21 44H43" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.45" />
                        </svg>

                        <h1 class="text-3xl font-semibold tracking-tight text-[#2a3d5d] dark:text-slate-100">
                            {{ __('auth.login.title_prefix') }} <span class="text-[#4f6ba3] dark:text-[#8aaed0]">Offitrade</span>
                        </h1>
                        <p class="mt-3 max-w-sm text-sm leading-relaxed text-[#5c6f8a] dark:text-slate-300">
                            {{ __('auth.login.subtitle') }}
                        </p>
                    </div>

                    <div>
                        <x-auth-session-status class="mb-4 rounded-2xl border border-[#b8e5d1] bg-[#ecf9f1] px-4 py-3 text-sm text-[#2f7653] shadow-sm dark:border-emerald-500/40 dark:bg-emerald-500/15 dark:text-emerald-200" :status="session('status')" />

                        @if ($errors->any())
                            <div class="mb-4 rounded-2xl border border-[#f4c7c3] bg-[#fdeceb] px-4 py-3 text-sm text-[#c23d3d] shadow-sm dark:border-rose-500/40 dark:bg-rose-500/15 dark:text-rose-200">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </div>

                    <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div class="space-y-2">
                            <x-input-label for="email" :value="__('Adresse e-mail')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                            <x-text-input
                                id="email"
                                class="block w-full rounded-2xl border border-[#c6d6ea] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#8aaed0] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#8aaed0]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#8aaed0] dark:focus:ring-[#5b7db5]/30"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="{{ __('auth.placeholder.email') }}"
                            />
                            <x-input-error :messages="$errors->get('email')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <x-input-label for="password" :value="__('Mot de passe')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#4f6ba3] transition hover:text-[#3c5482] dark:text-[#8aaed0] dark:hover:text-[#a2c0e4]">{{ __('auth.forgot') }}</a>
                                @endif
                            </div>
                            <x-text-input
                                id="password"
                                class="block w-full rounded-2xl border border-[#c6d6ea] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#8aaed0] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#8aaed0]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#8aaed0] dark:focus:ring-[#5b7db5]/30"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ __('auth.placeholder.password') }}"
                            />
                            <x-input-error :messages="$errors->get('password')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                        </div>

                        <label for="remember_me" class="flex items-center justify-center gap-3 rounded-2xl bg-[#e8f0fb] px-4 py-3 text-sm text-[#4d6185] transition hover:bg-[#dce6f4] dark:bg-slate-900/60 dark:text-slate-200 dark:hover:bg-slate-900/70">
                            <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-[#5b7db5]/60 text-[#4f6ba3] focus:ring-[#4f6ba3] dark:border-[#8aaed0]/70 dark:text-[#8aaed0] dark:focus:ring-[#8aaed0]" />
                            <span>{{ __('auth.remember') }}</span>
                        </label>

                        <x-primary-button id="login-submit" class="group relative inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#4f6ba3] px-6 py-3 text-base font-semibold text-white shadow-lg shadow-[#4f6ba3]/30 transition duration-300 hover:bg-[#465f92] focus:outline-none focus:ring-4 focus:ring-[#8aaed0]/40 active:translate-y-[1px] dark:bg-[#4f6ba3] dark:hover:bg-[#405b8a] dark:focus:ring-[#5b7db5]/35">
                            <svg data-button-spinner class="hidden h-5 w-5 animate-spin text-white/90" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                            </svg>
                            <svg data-button-icon class="h-5 w-5 text-white/90 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 16l-4-4m0 0l4-4m-4 4h14" /></svg>
                            <span data-button-label>{{ __('auth.login.button') }}</span>
                        </x-primary-button>

                        <div class="relative">
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="w-full border-t border-[#d0dfed] dark:border-slate-800"></div>
                            </div>
                            <div class="relative flex justify-center text-[0.65rem] uppercase tracking-[0.35em] text-[#7a8cab]">
                                <span class="bg-white px-3 dark:bg-slate-900">{{ __('auth.login.or') }}</span>
                            </div>
                        </div>

                        <div class="text-center text-sm text-[#4d6185] dark:text-slate-300">
                            {{ __('auth.login.no_account') }}
                            <a href="{{ route('register') }}" class="font-semibold text-[#4f6ba3] transition hover:text-[#3c5482] dark:text-[#8aaed0] dark:hover:text-[#a2c0e4]">{{ __('auth.login.create') }}</a>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const form = document.getElementById('login-form');
                            const submit = document.getElementById('login-submit');
                            if (!form || !submit) return;

                            const spinner = submit.querySelector('[data-button-spinner]');
                            const icon = submit.querySelector('[data-button-icon]');
                            const label = submit.querySelector('[data-button-label]');
                            const originalText = label ? label.textContent : '';

                            form.addEventListener('submit', function () {
                                submit.disabled = true;
                                submit.classList.add('cursor-wait', 'opacity-90');
                                if (spinner) spinner.classList.remove('hidden');
                                if (icon) icon.classList.add('hidden');
                                if (label) label.textContent = 'Connexion...';
                            }, { once: true });

                            form.addEventListener('turbo:submit-end', function () {
                                submit.disabled = false;
                                submit.classList.remove('cursor-wait', 'opacity-90');
                                if (spinner) spinner.classList.add('hidden');
                                if (icon) icon.classList.remove('hidden');
                                if (label) label.textContent = originalText;
                            });
                        });
                    </script>
                    
                </div>
            </div>
        </div>
    </div>

</x-guest-layout>