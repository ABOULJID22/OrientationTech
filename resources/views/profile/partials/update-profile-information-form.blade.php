<section class="space-y-8">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                {{ __('profile.header.title') }}
            </h2>

            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ __('profile.header.subtitle') }}
            </p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-full bg-[#b0cae0]/60 dark:bg-[#4f6ba3]/20 px-4 py-2 text-xs font-medium uppercase tracking-wide text-[#4f6ba3] dark:text-[#b0cae0] shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12a7.5 7.5 0 0 1 15 0c0 4.28-3.22 8.99-6.31 11.4a1.5 1.5 0 0 1-2.38 0C7.72 20.99 4.5 16.28 4.5 12Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" />
            </svg>
            {{ __('profile.header.tag') }}
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-10" enctype="multipart/form-data" x-data="{ isSubmitting: false }" x-on:submit="isSubmitting = true">
        @csrf
        @method('patch')

        <div class="rounded-2xl border border-[#b0cae0]/70 dark:border-slate-700/60 bg-[#b0cae0]/20 dark:bg-slate-800/50 p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('profile.avatar.label') }}</label>
                <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-5">
                    <div class="relative flex flex-col items-center justify-center">
                        <div class="absolute -inset-1 rounded-full bg-[#4f6ba3]/15 blur"></div>
                        <img id="avatarPreview" src="{{ $user->avatar }}" alt="avatar" class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border border-white/40 shadow-md" />
                        <p id="avatarBadge" class="mt-2 text-xs font-medium text-[#4f6ba3] dark:text-[#b0cae0] hidden">
                            {{ __('profile.avatar.preview_not_saved') }}
                        </p>
                    </div>
                    <div class="flex-1 w-full">
                        <div class="relative">
                            {{-- Custom file input: hide native filename and show our own text for consistent color/control --}}
                            <label for="avatar" class="flex items-center gap-3">
                                <input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only" />
                                <span class="inline-flex items-center px-4 py-2 bg-[#4f6ba3] hover:bg-[#5b7db5] text-white rounded-full text-sm font-semibold cursor-pointer">
                                    {{ __('Choose file') }}
                                </span>
                                <span id="avatarFilename" class="text-sm text-slate-900 dark:text-white ml-3">{{ __('Aucun fichier choisi') }}</span>
                            </label>
                        </div>
                        <p id="avatarHelp" class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('profile.avatar.help') }}</p>
                        <p id="avatarError" class="mt-2 text-xs text-red-600 dark:text-red-400 hidden" role="alert" aria-live="polite"></p>
                        <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div class="space-y-3">
                    <input id="email" name="email" type="hidden" value="{{ old('email', $user->email) }}" autocomplete="username" />
<!--                     @php($maskedEmail = \Illuminate\Support\Str::mask($user->email, '*', 3, 4))
                    <div class="rounded-2xl border border-[#8aaed0]/60 dark:border-slate-700/60 bg-white/70 dark:bg-slate-800/50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="h-9 w-9 rounded-2xl bg-[#4f6ba3]/15 text-[#4f6ba3] dark:text-[#8aaed0] flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25c0-1.012.859-1.875 1.875-1.875h14.25c1.016 0 1.875.863 1.875 1.875v.75c0 1.012-.859 1.875-1.875 1.875H4.875A1.88 1.88 0 0 1 3 9v-.75ZM3 15.75c0-1.012.859-1.875 1.875-1.875h14.25c1.016 0 1.875.863 1.875 1.875v.75c0 1.012-.859 1.875-1.875 1.875H4.875A1.88 1.88 0 0 1 3 16.5v-.75Z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-300">{{ __('profile.security.email_masked_label') }}</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $maskedEmail }}</p>
                            </div>
                        </div>
                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200/80 dark:border-amber-700/50 px-4 py-3 text-xs sm:text-sm text-amber-800 dark:text-amber-200">
                                <p>
                                    {{ __('Your email address is unverified.') }}

                                    <button form="send-verification" class="ml-1 underline font-medium text-amber-900 dark:text-amber-100 hover:text-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 dark:focus:ring-offset-slate-900">
                                        {{ __('Click here to re-send the verification email.') }}
                                    </button>
                                </p>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 font-semibold">
                                        {{ __('A new verification link has been sent to your email address.') }}
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div> -->
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-[#b0cae0]/70 dark:border-slate-700/60 bg-white/80 dark:bg-slate-800/50 p-6 space-y-6">
            <div class="flex items-center gap-3">
                <span class="h-9 w-9 rounded-2xl bg-[#4f6ba3]/15 text-[#4f6ba3] dark:text-[#8aaed0] flex items-center justify-center">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 1-2.25 2.25H8.25A2.25 2.25 0 0 1 6 12c0-1.24 1.01-2.25 2.25-2.25h10.5A2.25 2.25 0 0 1 21 12Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12V6.75A2.25 2.25 0 0 1 8.25 4.5h7.5A2.25 2.25 0 0 1 18 6.75V12" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 16.5h6" />
                    </svg>
                </span>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('profile.section.contacts') }}</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input-label for="phone" :value="__('Téléphone')" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-2 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="phone_2" :value="__('Téléphone secondaire')" />
                    <x-text-input id="phone_2" name="phone_2" type="text" class="mt-2 block w-full" :value="old('phone_2', $user->phone_2)" autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone_2')" />
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-[#b0cae0]/70 dark:border-slate-700/60 bg-white/80 dark:bg-slate-800/50 p-6 space-y-6">
            <div class="flex items-center gap-3">
                <span class="h-9 w-9 rounded-2xl bg-[#5b7db5]/15 text-[#5b7db5] dark:text-[#8aaed0] flex items-center justify-center">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5V6a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6v1.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5v10.5A2.25 2.25 0 0 0 5.25 20.25h13.5A2.25 2.25 0 0 0 21 18V7.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5l9 6 9-6" />
                    </svg>
                </span>
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('profile.section.address') }}</h3>
            </div>

            <div>
                <x-input-label for="address" :value="__('Adresse')" />
                <x-text-input id="address" name="address" type="text" class="mt-2 block w-full" :value="old('address', $user->address)" autocomplete="street-address" />
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-input-label for="city" :value="__('Ville')" />
                    <x-text-input id="city" name="city" type="text" class="mt-2 block w-full" :value="old('city', $user->city)" autocomplete="address-level2" />
                    <x-input-error class="mt-2" :messages="$errors->get('city')" />
                </div>
                <div>
                    <x-input-label for="postal_code" :value="__('Code postal')" />
                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-2 block w-full" :value="old('postal_code', $user->postal_code)" autocomplete="postal-code" />
                    <x-input-error class="mt-2" :messages="$errors->get('postal_code')" />
                </div>
                <div>
                    <x-input-label for="country" :value="__('Pays')" />
                    <x-text-input id="country" name="country" type="text" class="mt-2 block w-full" :value="old('country', $user->country)" autocomplete="country-name" />
                    <x-input-error class="mt-2" :messages="$errors->get('country')" />
                </div>
            </div>
        </div>

        @if($user->isClient())
            <div class="rounded-2xl border border-[#b0cae0]/70 dark:border-slate-700/60 bg-white/80 dark:bg-slate-800/50 p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <span class="h-9 w-9 rounded-2xl bg-[#8aaed0]/20 text-[#4f6ba3] dark:text-[#8aaed0] flex items-center justify-center">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5V6a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6v1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5v10.5A2.25 2.25 0 0 0 5.25 20.25h13.5A2.25 2.25 0 0 0 21 18V7.5" />
                        </svg>
                    </span>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('profile.sidebar.professional') }}</h3>
                </div>
                <div>
                    <x-input-label for="website" :value="__('Site web')" />
                    <x-text-input id="website" name="website" type="url" class="mt-2 block w-full" :value="old('website', $user->website)" />
                    <x-input-error class="mt-2" :messages="$errors->get('website')" />
                </div>
            </div>
        @endif

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
                <span>{{ __('profile.actions.save') }}</span>
            </x-primary-button>

            @if (session('status') === 'profile-updated')
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
                    {{ __('profile.actions.saved') }}
                </div>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    (function(){
    const input = document.getElementById('avatar');
    const preview = document.getElementById('avatarPreview');
    const badge = document.getElementById('avatarBadge');
    const errorEl = document.getElementById('avatarError');
    const filenameEl = document.getElementById('avatarFilename');
    const defaultFilename = @json(__('Aucun fichier choisi'));
        if (!input || !preview) return;

        const originalSrc = preview.src;
        let objectUrl = null;

        // inject translated messages
        const messages = {
            invalidType: @json(__('profile.avatar.error_invalid_type')) ,
            tooLarge: @json(__('profile.avatar.error_too_large')) ,
            previewNotSaved: @json(__('profile.avatar.preview_not_saved')) ,
        };

        input.addEventListener('change', function(e) {
            const file = this.files && this.files[0];
            // cleanup previous object URL
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            // no file selected -> reset
            if (!file) {
                preview.src = originalSrc;
                badge.classList.add('hidden');
                errorEl.classList.add('hidden');
                errorEl.textContent = '';
                if (filenameEl) filenameEl.textContent = defaultFilename;
                return;
            }

            // validate type
            const allowed = ['image/png', 'image/jpeg', 'image/webp'];
            if (!allowed.includes(file.type)) {
                preview.src = originalSrc;
                errorEl.textContent = messages.invalidType;
                errorEl.classList.remove('hidden');
                badge.classList.add('hidden');
                return;
            }

            // validate size (2MB)
            const maxBytes = 2 * 1024 * 1024;
            if (file.size > maxBytes) {
                preview.src = originalSrc;
                errorEl.textContent = messages.tooLarge;
                errorEl.classList.remove('hidden');
                badge.classList.add('hidden');
                return;
            }

            // valid file
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            badge.classList.remove('hidden');
            if (filenameEl) filenameEl.textContent = file.name;
        });

        // optional: if the form is reset, return preview to original
        const form = input.closest('form');
        if (form) {
            form.addEventListener('reset', function(){
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }
                preview.src = originalSrc;
                badge.classList.add('hidden');
                if (filenameEl) filenameEl.textContent = defaultFilename;
            });

            form.addEventListener('submit', function(){
                badge.classList.add('hidden');
            });
        }
    })();
</script>
@endpush
