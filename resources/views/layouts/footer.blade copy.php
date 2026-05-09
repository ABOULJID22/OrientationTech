<footer class="relative overflow-hidden text-white">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('images/bgSide.png') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-tr from-slate-950/95 via-slate-900/80 to-slate-950/90 backdrop-blur"></div>
    <div class="absolute inset-y-0 -left-1/3 hidden w-[45rem] rotate-12 rounded-full bg-primary-500/20 blur-3xl md:block"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
        <div class="grid gap-12 md:grid-cols-12">
            <div class="md:col-span-4 lg:col-span-5">
                <div class="flex items-center gap-3">
                    <span class="relative flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/15">
                        <img src="{{ $siteSettings?->logo_path ? Storage::url($siteSettings->logo_path) : asset('images/logo.png') }}" alt="OrientationTech Logo" class="h-8 w-auto">
                    </span>
                    <p class="text-sm uppercase tracking-[0.3em] text-white/60">OrientationTech</p>
                </div>
                <p class="mt-6 text-sm leading-relaxed text-white/80">
                    {{ __('site.footer.desc') }}
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-3 text-xs text-white/60">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4"><path fill="currentColor" d="M12 2a10 10 0 0 0-3.162 19.477c.5.094.683-.217.683-.482v-1.693c-2.778.604-3.361-1.34-3.361-1.34-.454-1.153-1.11-1.46-1.11-1.46-.908-.62.069-.607.069-.607 1.004.071 1.532 1.03 1.532 1.03.892 1.53 2.341 1.088 2.91.833.091-.647.35-1.088.636-1.339-2.22-.253-4.555-1.11-4.555-4.944 0-1.091.39-1.984 1.03-2.683-.104-.253-.447-1.27.098-2.646 0 0 .84-.27 2.75 1.025A9.556 9.556 0 0 1 12 6.844a9.52 9.52 0 0 1 2.505.337c1.909-1.295 2.748-1.025 2.748-1.025.547 1.376.204 2.393.1 2.646.641.699 1.028 1.592 1.028 2.683 0 3.843-2.339 4.688-4.566 4.936.359.309.679.92.679 1.854v2.747c0 .267.181.58.688.481A10 10 0 0 0 12 2Z"/></svg>
                        <span>{{ __('site.footer.open_source') }}</span>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 px-4 py-2 backdrop-blur">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4"><path fill="currentColor" d="M12 5c-3.859 0-7 3.141-7 7c0 2.943 1.843 5.441 4.431 6.406c.324.059.442-.141.442-.313c0-.153-.01-.662-.01-1.203c-1.61.294-2.031-.393-2.159-.754c-.073-.186-.39-.754-.667-.906c-.228-.124-.553-.43-.01-.438c.512-.008.877.472.998.667c.585.982 1.517.706 1.89.535c.059-.42.228-.706.415-.868c-1.43-.162-2.924-.726-2.924-3.229c0-.713.253-1.299.667-1.757c-.065-.168-.291-.841.065-1.753c0 0 .54-.176 1.77.669c.512-.142 1.058-.213 1.603-.213c.545 0 1.091.071 1.603.213c1.23-.853 1.77-.669 1.77-.669c.356.912.13 1.585.065 1.753c.415.458.667 1.035.667 1.757c0 2.511-1.503 3.067-2.933 3.229c.237.203.444.591.444 1.203c0 .869-.01 1.569-.01 1.784c0 .172.118.38.444.313C17.157 17.441 19 14.943 19 12c0-3.859-3.141-7-7-7Z"/></svg>
                        <span>{{ __('site.footer.community') }}</span>
                    </span>
                </div>
            </div>

            <div class="md:col-span-2 lg:col-span-2">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/70">{{ __('site.footer.navigation') }}</h4>
                <ul class="mt-6 space-y-3 text-sm text-white/70">
                    <li><a href="/" class="transition hover:text-white">{{ __('site.nav.home') }}</a></li>
                    <li><a href="#about" class="transition hover:text-white">{{ __('site.nav.about') }}</a></li>
                    <li><a href="{{ route('pourquoi') }}" class="transition hover:text-white">{{ __('site.nav.blog') }}</a></li>
                    <li><a href="#contact" class="transition hover:text-white">{{ __('site.nav.contact') }}</a></li>
                </ul>
            </div>

            <div class="md:col-span-2 lg:col-span-2">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/70">{{ __('site.footer.support') }}</h4>
                <ul class="mt-6 space-y-3 text-sm text-white/70">
                    <li><a href="#faq" class="transition hover:text-white">{{ __('site.nav.faq') }}</a></li>
                    <li><a href="{{ route('legal') }}" class="transition hover:text-white">{{ __('site.footer.legal') }}</a></li>
                    <li><a href="{{ route('privacy') }}" class="transition hover:text-white">{{ __('site.footer.privacy') }}</a></li>
                </ul>
            </div>

            <div class="md:col-span-2 lg:col-span-2">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/70">{{ __('site.footer.account') }}</h4>
                <ul class="mt-6 space-y-3 text-sm text-white/70">
                    @guest
                        <li><a href="{{ route('login') }}" class="transition hover:text-white">{{ __('site.footer.login') }}</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-white">{{ __('site.footer.register') }}</a></li>
                    @endguest
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="transition hover:text-white">{{ __('site.footer.profile') }}</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="transition hover:text-white">{{ __('site.footer.logout') }}</button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>

            <div class="md:col-span-4 lg:col-span-3">
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white/70">{{ __('site.footer.follow_us') }}</h4>
                <p class="mt-6 text-sm text-white/70">{{ __('site.footer.follow_desc') }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ $siteSettings?->linkedin_url ?? 'https://www.linkedin.com/company/OrientationTech' }}" target="_blank" rel="noopener" aria-label="LinkedIn" class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 transition hover:-translate-y-1 hover:border-white/30 hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 text-white/80 transition group-hover:text-white"><path fill="currentColor" d="M20.451 20.452h-3.554v-5.569c0-1.328-.026-3.037-1.849-3.037c-1.852 0-2.135 1.445-2.135 2.939v5.667H9.359V9h3.414v1.561h.049c.476-.899 1.64-1.85 3.377-1.85c3.606 0 4.272 2.372 4.272 5.457v6.284ZM5.337 7.433a2.065 2.065 0 1 1 0-4.13a2.065 2.065 0 0 1 0 4.13ZM6.925 20.452H3.75V9h3.175v11.452ZM22.225 0H1.771C.792 0 0 .77 0 1.723v20.554C0 23.23.792 24 1.771 24h20.454C23.2 24 24 23.23 24 22.277V1.723C24 .77 23.2 0 22.225 0Z"/></svg>
                    </a>
                    <a href="{{ $siteSettings?->instagram_url ?? 'https://www.instagram.com/Orientationtech.ma' }}" target="_blank" rel="noopener" aria-label="Instagram" class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 transition hover:-translate-y-1 hover:border-white/30 hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 text-white/80 transition group-hover:text-white"><path fill="currentColor" d="M12 7.09a4.91 4.91 0 1 0 0 9.82a4.91 4.91 0 0 0 0-9.82Zm0 8.08a3.17 3.17 0 1 1 0-6.34a3.17 3.17 0 0 1 0 6.34Zm6.19-8.58a1.15 1.15 0 1 1-2.3 0a1.15 1.15 0 0 1 2.3 0Z"/><path fill="currentColor" d="M21.54 6.5a5.5 5.5 0 0 0-.36-1.85a3.77 3.77 0 0 0-2.16-2.16A5.5 5.5 0 0 0 17.17 2c-1.12-.05-1.47-.06-5.17-.06s-4.05 0-5.17.06A5.5 5.5 0 0 0 5 2.49a3.77 3.77 0 0 0-2.16 2.16A5.5 5.5 0 0 0 2.5 6.5C2.45 7.62 2.44 7.97 2.44 11.67s0 4.05.06 5.17A5.5 5.5 0 0 0 3 18.7a3.77 3.77 0 0 0 2.16 2.16a5.5 5.5 0 0 0 1.85.36c1.12.05 1.47.06 5.17.06s4.05 0 5.17-.06a5.5 5.5 0 0 0 1.85-.36a3.77 3.77 0 0 0 2.16-2.16a5.5 5.5 0 0 0 .36-1.85c.05-1.12.06-1.47.06-5.17s0-4.05-.06-5.17Zm-2.06 11a3.76 3.76 0 0 1-.21 1.25a1.94 1.94 0 0 1-1.11 1.11a3.76 3.76 0 0 1-1.25.21c-1.1.05-1.43.06-4.91.06s-3.81 0-4.91-.06a3.76 3.76 0 0 1-1.25-.21a1.94 1.94 0 0 1-1.11-1.11a3.76 3.76 0 0 1-.21-1.25c-.05-1.1-.06-1.43-.06-4.91s0-3.81.06-4.91a3.76 3.76 0 0 1 .21-1.25a1.94 1.94 0 0 1 1.11-1.11a3.76 3.76 0 0 1 1.25-.21c1.1-.05 1.43-.06 4.91-.06s3.81 0 4.91.06a3.76 3.76 0 0 1 1.25.21a1.94 1.94 0 0 1 1.11 1.11a3.76 3.76 0 0 1 .21 1.25c.05 1.1.06 1.43.06 4.91s0 3.81-.06 4.91Z"/></svg>
                    </a>
                    <a href="{{ $siteSettings?->youtube_url ?? 'https://www.youtube.com/@OrientationTech' }}" target="_blank" rel="noopener" aria-label="YouTube" class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 transition hover:-translate-y-1 hover:border-white/30 hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 text-white/80 transition group-hover:text-white"><path fill="currentColor" d="M21.6 7.2a2.5 2.5 0 0 0-1.76-1.76C18.3 5 12 5 12 5s-6.3 0-7.84.44A2.5 2.5 0 0 0 2.4 7.2A26.2 26.2 0 0 0 2 12a26.2 26.2 0 0 0 .4 4.8a2.5 2.5 0 0 0 1.76 1.76C5.7 19 12 19 12 19s6.3 0 7.84-.44a2.5 2.5 0 0 0 1.76-1.76A26.2 26.2 0 0 0 22 12a26.2 26.2 0 0 0-.4-4.8ZM10 15V9l5 3Z"/></svg>
                    </a>
                    <a href="{{ $siteSettings?->twitter_url ?? 'https://x.com/OrientationTech' }}" target="_blank" rel="noopener" aria-label="X" class="group flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 transition hover:-translate-y-1 hover:border-white/30 hover:bg-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5 text-white/80 transition group-hover:text-white"><path fill="currentColor" d="M18.244 2h3.308l-7.227 8.26l8.498 11.74H16.17l-5.214-6.818L4.947 22H1.637l7.73-8.843L1.254 2H7.98l4.713 6.231L18.243 2Zm-1.162 17.52h1.833L7.05 4.39H5.082l12 15.13Z"/></svg>
                    </a>
                </div>
                <div class="mt-6 space-y-2 text-sm text-white/70">
                    <p><span class="font-semibold text-white">{{ __('site.footer.email') }}:</span> {{ $siteSettings?->email ?? 'contact@Orientationtech.ma' }}</p>
                    <p><span class="font-semibold text-white">{{ __('site.footer.phone') }}:</span> {{ $siteSettings?->phone ?? '+33 07 67 70 67 26' }}</p>
                    <p class="max-w-xs leading-relaxed"><span class="font-semibold text-white">{{ __('site.footer.address') }}:</span> {{ $siteSettings?->address ?? '14 rue Beffory, 92200 Neuilly-sur-Seine, France' }}</p>
                </div>
            </div>
        </div>

        <div class="mt-16 flex flex-col items-center justify-between gap-6 border-t border-white/10 pt-6 text-xs text-white/60 sm:flex-row">
            <p>
                &copy;
                @if (now()->year > 2025)
                    2025-{{ now()->year }}
                @else
                    2025
                @endif
                OrientationTech. {{ __('site.footer.copyright') }}
            </p>
            <div class="flex items-center gap-4">
                <span>{!! __('site.footer.made_with') !!}</span>
                <a href="#" class="inline-flex items-center gap-2 rounded-full border border-white/10 px-3 py-1 text-white/70 transition hover:border-white/30 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 text-primary-300"><path fill="currentColor" d="m12 21l-1.45-1.32C5.4 15.36 2 12.28 2 8.5C2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3C19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.18Z"/></svg>
                    <span>{{ __('site.footer.trust_badge') }}</span>
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
    console.log('%c👨‍💻 Développé par abouljid', 'color: #4f6ba3; font-size: 16px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);');
    console.log('%c💙 Merci de respecter ce travail', 'color: #5a7bbf; font-size: 12px;');
</script>

{{-- Exemple : montrer autre chose si connecté --}}
    <!-- Fin contenu footer z-10 -->
</footer>

<script>
    // Message caché dans la console
    console.log('%c👨‍💻 Développé par mohamed abouljid', 'color: #4f6ba3; font-size: 16px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);');
    console.log('%c💙 Merci ', 'color: #5a7bbf; font-size: 12px;');
</script>
