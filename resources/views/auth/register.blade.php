<x-guest-layout>
    <div class="relative min-h-screen overflow-hidden">
        <div class="relative flex min-h-screen items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
            <div class="w-full max-w-lg overflow-hidden rounded-[28px] border border-[#b8cbe1] bg-white/96 shadow-[0_28px_50px_-18px_rgba(37,99,235,0.32)] ring-1 ring-[#dce6f4]/80 backdrop-blur-xl dark:border-slate-700 dark:bg-slate-900/95 dark:ring-slate-700/70">
                <div class="relative px-8 py-12 sm:px-12">
                    <div class="absolute right-8 top-8 hidden text-xs font-medium tracking-[0.25em] text-[#7a8cab] dark:text-[#94a6c6] sm:block">
                        {{ now()->format('d M Y') }}
                    </div>

                    <div class="mb-10 flex flex-col items-center text-center">
                        <svg class="mb-6 h-16 w-16 text-[#2563eb] dark:text-[#93c5fd]" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="32" cy="32" r="30" stroke="currentColor" stroke-width="2.5" opacity="0.25" />
                            <path d="M20 42c2.5-6 9-10 12-10s9.5 4 12 10" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="32" cy="26" r="6" stroke="currentColor" stroke-width="2.5" />
                        </svg>

                        <h1 class="text-3xl font-semibold tracking-tight text-[#2a3d5d] dark:text-slate-100">
                            {{ __('auth.register.title_prefix') }} <span class="text-[#2563eb] dark:text-[#93c5fd]">OrientationTech</span>
                        </h1>
                        <p class="mt-3 max-w-sm text-sm leading-relaxed text-[#5c6f8a] dark:text-slate-300">
                            {{ __('auth.login.subtitle') }}
                        </p>
                    </div>

                <form id="register-form" method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2">
                        <x-input-label for="user_type" :value="__('Type de profil')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex cursor-pointer items-center rounded-2xl border-2 border-[#bfdbfe] bg-white/90 p-4 transition hover:border-[#5b7db5] dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-[#93c5fd]">
                                <input
                                    type="radio"
                                    name="user_type"
                                    value="student"
                                    required
                                    class="h-4 w-4 cursor-pointer accent-[#2563eb]"
                                    :checked="old('user_type') === 'student'"
                                />
                                <span class="ml-3 flex flex-col">
                                    <span class="font-semibold text-[#2a3d5d] dark:text-slate-100">Élève</span>
                                    <span class="text-xs text-[#5c6f8a] dark:text-slate-400">Accédez au test d'orientation</span>
                                </span>
                            </label>
                            <label class="relative flex cursor-pointer items-center rounded-2xl border-2 border-[#bfdbfe] bg-white/90 p-4 transition hover:border-[#5b7db5] dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-[#93c5fd]">
                                <input
                                    type="radio"
                                    name="user_type"
                                    value="teacher"
                                    required
                                    class="h-4 w-4 cursor-pointer accent-[#2563eb]"
                                    :checked="old('user_type') === 'teacher'"
                                />
                                <span class="ml-3 flex flex-col">
                                    <span class="font-semibold text-[#2a3d5d] dark:text-slate-100">Enseignant</span>
                                    <span class="text-xs text-[#5c6f8a] dark:text-slate-400">Tableau de bord enseignant</span>
                                </span>
                            </label>
                        </div>
                        <x-input-error :messages="$errors->get('user_type')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="name" :value="__('Nom')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                        <x-text-input
                            id="name"
                            class="block w-full rounded-2xl border border-[#bfdbfe] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#93c5fd] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#93c5fd]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#93c5fd] dark:focus:ring-[#5b7db5]/30"
                            type="text"
                            name="name"
                            :value="old('name')"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="{{ __('auth.placeholder.name') }}"
                        />
                        <x-input-error :messages="$errors->get('name')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="email" :value="__('Adresse e-mail')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                        <x-text-input
                            id="email"
                            class="block w-full rounded-2xl border border-[#bfdbfe] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#93c5fd] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#93c5fd]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#93c5fd] dark:focus:ring-[#5b7db5]/30"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autocomplete="username"
                            placeholder="{{ __('auth.placeholder.email') }}"
                        />
                        <x-input-error :messages="$errors->get('email')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="password" :value="__('Mot de passe')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                        <x-text-input
                            id="password"
                            class="block w-full rounded-2xl border border-[#bfdbfe] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#93c5fd] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#93c5fd]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#93c5fd] dark:focus:ring-[#5b7db5]/30"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('auth.placeholder.password') }}"
                        />
                        <x-input-error :messages="$errors->get('password')" class="text-xs text-[#c23d3d] dark:text-rose-300" />

                        <div id="password-rules" class="mt-3 rounded-2xl border border-[#bfdbfe] bg-[#eff6ff] px-4 py-3 text-xs text-[#4d6185] shadow-sm dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200">
                            <div class="mb-2 font-semibold uppercase tracking-[0.18em] text-[#7a8cab] dark:text-[#b3c4e0]">{{ __('auth.password.rules.title') }}</div>
                            <ul class="space-y-1">
                                <li id="rule-length">{{ __('auth.password.rules.length') }}</li>
                                <li id="rule-letters">{{ __('auth.password.rules.letters') }}</li>
                                <li id="rule-numbers">{{ __('auth.password.rules.numbers') }}</li>
                                <li id="rule-special">{{ __('auth.password.rules.special') }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="password_confirmation" :value="__('Confirmer le mot de passe')" class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7a8cab] dark:text-[#b3c4e0]" />
                        <x-text-input
                            id="password_confirmation"
                            class="block w-full rounded-2xl border border-[#bfdbfe] bg-white/90 px-4 py-3 text-base text-[#2a3d5d] shadow-sm transition placeholder:text-[#93c5fd] focus:border-[#5b7db5] focus:outline-none focus:ring-4 focus:ring-[#93c5fd]/35 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-100 dark:placeholder:text-slate-400 dark:focus:border-[#93c5fd] dark:focus:ring-[#5b7db5]/30"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('auth.placeholder.confirm_password') }}"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="text-xs text-[#c23d3d] dark:text-rose-300" />
                    </div>

                    <div class="flex items-center justify-between rounded-2xl bg-[#e8f0fb] px-4 py-3 text-sm text-[#4d6185] dark:bg-slate-900/60 dark:text-slate-200">
                        <span>{{ __('auth.register.already') }}</span>
                        <a href="{{ route('login') }}" class="font-semibold text-[#2563eb] transition hover:text-[#3f5987] dark:text-[#93c5fd] dark:hover:text-[#a2c0e4]">
                            {{ __('auth.register.sign_in') }}
                        </a>
                    </div>

                    <div id="password-error" class="min-h-[1.25rem] text-sm text-[#c23d3d] opacity-0 transition-opacity duration-200 dark:text-rose-300">
                        {{ __('auth.password.error') }}
                    </div>

                    <x-primary-button
                        id="register-submit"
                        class="group relative inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#2563eb] px-6 py-3 text-base font-semibold text-white shadow-lg shadow-[#2563eb]/30 transition duration-300 hover:bg-[#1d4ed8] focus:outline-none focus:ring-4 focus:ring-[#93c5fd]/40 active:translate-y-[1px] dark:bg-[#2563eb] dark:hover:bg-[#405b8a] dark:focus:ring-[#5b7db5]/35">
                        <svg data-button-spinner class="hidden h-5 w-5 animate-spin text-white/90" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                        </svg>
                        <svg data-button-icon class="h-5 w-5 text-white/90 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" /></svg>
                        <span data-button-label>{{ __('auth.register.button') }}</span>
                    </x-primary-button>
                </form>

                <script>
                    (function(){
                        const form = document.getElementById('register-form');
                        const submit = document.getElementById('register-submit');
                        const pwd = document.getElementById('password');
                        const errBox = document.getElementById('password-error');

                        if (form && submit) {
                            const spinner = submit.querySelector('[data-button-spinner]');
                            const icon = submit.querySelector('[data-button-icon]');
                            const label = submit.querySelector('[data-button-label]');
                            const originalText = label ? label.textContent : '';

                            form.addEventListener('submit', function () {
                                submit.disabled = true;
                                submit.classList.add('cursor-wait', 'opacity-90');
                                if (spinner) spinner.classList.remove('hidden');
                                if (icon) icon.classList.add('hidden');
                                if (label) label.textContent = 'Inscription...';
                            }, { once: true });

                            form.addEventListener('turbo:submit-end', function () {
                                submit.disabled = false;
                                submit.classList.remove('cursor-wait', 'opacity-90');
                                if (spinner) spinner.classList.add('hidden');
                                if (icon) icon.classList.remove('hidden');
                                if (label) label.textContent = originalText;
                            });
                        }

                        if (!pwd || !submit || !errBox) return;

                        const ruleLength = document.getElementById('rule-length');
                        const ruleLetters = document.getElementById('rule-letters');
                        const ruleNumbers = document.getElementById('rule-numbers');
                        const ruleSpecial = document.getElementById('rule-special');

                        function updateRule(el, condition) {
                            if (!el) return;
                            el.style.opacity = condition ? '1' : '0.45';
                            el.style.textDecoration = condition ? 'none' : 'line-through';
                        }

                        function validate(value){
                            const hasLength = value.length >= 8;
                            const hasLetters = /[A-Za-z]/.test(value);
                            const hasNumbers = /[0-9]/.test(value);
                            const hasSpecial = /[^A-Za-z0-9]/.test(value);

                            updateRule(ruleLength, hasLength);
                            updateRule(ruleLetters, hasLetters);
                            updateRule(ruleNumbers, hasNumbers);
                            updateRule(ruleSpecial, hasSpecial);

                            const ok = hasLength && hasLetters && hasNumbers;
                            submit.disabled = !ok;
                            errBox.classList.toggle('opacity-0', ok);
                            errBox.classList.toggle('opacity-100', !ok);
                        }

                        pwd.addEventListener('input', (event) => validate(event.target.value));
                        validate(pwd.value || '');
                    })();
                </script>
            </div>
        </div>
    </div>
</x-guest-layout>
