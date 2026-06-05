@php($compact = $compact ?? false)

<section>
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2563eb]">Securite</p>
            <h2 class="mt-2 {{ $compact ? 'text-xl' : 'text-2xl' }} font-black text-slate-950">Mot de passe</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Utilise un mot de passe long et unique pour proteger ton espace OrientationTech, tes resultats et tes interactions.
            </p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#2563eb] ring-1 ring-blue-100">
            Protection du compte
        </span>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('put')

        <div class="grid gap-6 {{ $compact ? '' : 'md:grid-cols-2' }}">
            <div class="{{ $compact ? '' : 'md:col-span-2' }}">
                <x-input-label for="update_password_current_password" :value="'Mot de passe actuel'" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-2 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" :value="'Nouveau mot de passe'" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="'Confirmer le mot de passe'" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="rounded-2xl border border-blue-100 bg-[#eff6ff] p-5 text-sm leading-6 text-slate-700">
            <p class="font-bold text-slate-950">Recommandations</p>
            <ul class="mt-3 grid gap-2 {{ $compact ? '' : 'sm:grid-cols-3' }}">
                <li class="rounded-xl bg-white px-4 py-3">Au moins 8 caracteres.</li>
                <li class="rounded-xl bg-white px-4 py-3">Melange lettres, chiffres et symboles.</li>
                <li class="rounded-xl bg-white px-4 py-3">Evite les mots faciles a deviner.</li>
            </ul>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Mettre a jour</x-primary-button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2400)"
                    class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 text-sm font-bold text-[#2563eb]"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Mot de passe mis a jour.
                </div>
            @endif
        </div>
    </form>
</section>
