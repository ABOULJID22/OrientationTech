@php
    $roleKey = $roleKey ?? 'student';
    $roleLabel = $roleLabel ?? 'Utilisateur';
@endphp

<section>
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#2563eb]">Profil {{ $roleLabel }}</p>
            <h2 class="mt-2 text-2xl font-black text-slate-950">Informations personnelles</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Ces informations permettent a OrientationTech de personnaliser l'accompagnement, securiser ton compte et ameliorer les echanges sur la plateforme.
            </p>
        </div>
        <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#2563eb] ring-1 ring-blue-100">
            Donnees de compte
        </span>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-8" enctype="multipart/form-data" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('patch')

        <div class="rounded-2xl border border-blue-100 bg-[#eff6ff] p-5 sm:p-6">
            <label class="block text-sm font-bold text-slate-900">Photo de profil</label>
            <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-center">
                <div class="relative h-24 w-24 shrink-0">
                    <div class="absolute inset-0 rounded-3xl bg-blue-200"></div>
                    <img id="avatarPreview" src="{{ $user->avatar }}" alt="Avatar" class="relative h-24 w-24 rounded-3xl border-4 border-white object-cover shadow-md">
                </div>

                <div class="min-w-0 flex-1">
                    <label for="avatar" class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#1d4ed8]">
                        Choisir une image
                    </label>
                    <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                    <span id="avatarFilename" class="ml-0 mt-3 block text-sm text-slate-600 sm:ml-3 sm:mt-0 sm:inline">Aucun fichier choisi</span>
                    <p id="avatarBadge" class="mt-2 hidden text-xs font-semibold text-[#2563eb]">Apercu non encore enregistre.</p>
                    <p id="avatarError" class="mt-2 hidden text-xs font-semibold text-red-600" role="alert" aria-live="polite"></p>
                    <p class="mt-2 text-xs leading-5 text-slate-500">Formats acceptes : JPG, PNG ou WEBP. Taille maximale : 2 Mo.</p>
                    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <x-input-label for="name" :value="'Nom complet'" />
                <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email_display" :value="'Adresse email'" />
                <input id="email" name="email" type="hidden" value="{{ old('email', $user->email) }}" autocomplete="username">
                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700">
                    {{ $user->email }}
                </div>
                <p class="mt-2 text-xs text-slate-500">L'email est utilise pour la connexion et les notifications importantes.</p>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Ton adresse email n'est pas encore verifiee.
                        <button form="send-verification" class="font-bold underline">Renvoyer le lien de verification</button>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-bold">Un nouveau lien de verification a ete envoye.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if (in_array($roleKey, ['teacher', 'super_admin'], true))
            <div>
                <x-input-label for="job_title" :value="$roleKey === 'teacher' ? 'Titre ou specialite pedagogique' : 'Fonction administrative'" />
                <x-text-input id="job_title" name="job_title" type="text" class="mt-2 block w-full" :value="old('job_title', $user->job_title)" placeholder="Ex: Enseignant informatique, Conseiller, Administrateur plateforme" />
                <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-[#2563eb]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97c.36-.27.527-.727.417-1.173L6.963 3.102A1.125 1.125 0 0 0 5.872 2.25H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-black text-slate-950">Contacts</h3>
                    <p class="text-sm text-slate-500">Coordonnees utiles pour le support et le suivi pedagogique.</p>
                </div>
            </div>

            <div class="mt-5 grid gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="phone" :value="'Telephone principal'" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="phone_2" :value="'Telephone secondaire'" />
                    <x-text-input id="phone_2" name="phone_2" type="text" class="mt-2 block w-full" :value="old('phone_2', $user->phone_2)" autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone_2')" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-[#2563eb]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="font-black text-slate-950">Adresse</h3>
                    <p class="text-sm text-slate-500">Ville et pays pour adapter les ressources au contexte local.</p>
                </div>
            </div>

            <div class="mt-5">
                <x-input-label for="address" :value="'Adresse'" />
                <x-text-input id="address" name="address" type="text" class="mt-2 block w-full" :value="old('address', $user->address)" autocomplete="street-address" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div class="mt-5 grid gap-6 md:grid-cols-3">
                <div>
                    <x-input-label for="city" :value="'Ville'" />
                    <x-text-input id="city" name="city" type="text" class="mt-2 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>
                <div>
                    <x-input-label for="postal_code" :value="'Code postal'" />
                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-2 block w-full" :value="old('postal_code', $user->postal_code)" autocomplete="postal-code" />
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
                <div>
                    <x-input-label for="country" :value="'Pays'" />
                    <x-text-input id="country" name="country" type="text" class="mt-2 block w-full" :value="old('country', $user->country)" autocomplete="country-name" />
                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button
                x-bind:disabled="isSubmitting"
                x-bind:class="{ 'opacity-70 cursor-not-allowed': isSubmitting }"
                class="gap-2"
            >
                <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                </svg>
                <span>Enregistrer</span>
            </x-primary-button>

            @if (session('status') === 'profile-updated')
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
                    Profil enregistre.
                </div>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('avatarPreview');
        const badge = document.getElementById('avatarBadge');
        const errorEl = document.getElementById('avatarError');
        const filenameEl = document.getElementById('avatarFilename');

        if (!input || !preview) {
            return;
        }

        const originalSrc = preview.src;
        let objectUrl = null;

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (!file) {
                preview.src = originalSrc;
                badge.classList.add('hidden');
                errorEl.classList.add('hidden');
                filenameEl.textContent = 'Aucun fichier choisi';
                return;
            }

            const allowed = ['image/png', 'image/jpeg', 'image/webp'];
            if (!allowed.includes(file.type)) {
                preview.src = originalSrc;
                errorEl.textContent = 'Format non accepte. Utilise JPG, PNG ou WEBP.';
                errorEl.classList.remove('hidden');
                badge.classList.add('hidden');
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                preview.src = originalSrc;
                errorEl.textContent = 'Image trop lourde. Taille maximale : 2 Mo.';
                errorEl.classList.remove('hidden');
                badge.classList.add('hidden');
                return;
            }

            errorEl.classList.add('hidden');
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            filenameEl.textContent = file.name;
            badge.classList.remove('hidden');
        });
    })();
</script>
@endpush
