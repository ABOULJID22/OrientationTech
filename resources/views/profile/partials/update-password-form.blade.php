<section class="space-y-8">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                {{ __('Mettre à jour le mot de passe') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ __('Assurez-vous d\'utiliser un mot de passe long et aléatoire pour sécuriser votre compte.') }}
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full bg-[#b0cae0]/60 dark:bg-[#4f6ba3]/20 px-4 py-2 text-xs font-medium uppercase tracking-wide text-[#4f6ba3] dark:text-[#b0cae0] shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5V9m0 3v3m9-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ __('profile.security.password_section') }}
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <x-input-label for="update_password_current_password" :value="__('Mot de passe actuel')" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="__('Nouveau mot de passe')" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirmer le mot de passe')" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="rounded-2xl border border-[#b0cae0]/70 dark:border-[#4f6ba3]/40 bg-[#b0cae0]/30 dark:bg-slate-800/40 px-5 py-4 text-xs sm:text-sm text-slate-700 dark:text-slate-200">
            <ul class="space-y-2 list-disc list-inside">
                <li>{{ __('profile.password.guideline_one') }}</li>
                <li>{{ __('profile.password.guideline_two') }}</li>
                <li>{{ __('profile.password.guideline_three') }}</li>
            </ul>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>{{ __('profile.actions.save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-100 dark:bg-emerald-500/10 px-4 py-2 text-sm font-medium text-emerald-700 dark:text-emerald-300"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    {{ __('Enregistré.') }}
                </div>
            @endif
        </div>
    </form>
</section>
