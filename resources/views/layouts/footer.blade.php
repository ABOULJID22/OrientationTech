<footer class="relative overflow-hidden bg-gradient-to-br from-[#2563eb] to-[#172554] pt-14 text-white dark:from-gray-950 dark:to-gray-900 sm:pt-16">
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="hidden md:block absolute -top-28 -right-20 h-96 w-96 rounded-full bg-white/10 blur-3xl opacity-25"></div>
        <div class="hidden md:block absolute -bottom-28 -left-20 h-80 w-80 rounded-full bg-white/10 blur-2xl opacity-20"></div>
        <div class="absolute -top-6 -right-8 h-64 w-64 rounded-full bg-white/10 mix-blend-overlay animate-blob"></div>
        <div class="absolute -bottom-10 -left-8 h-72 w-72 rounded-full bg-white/10 mix-blend-overlay animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-6 right-1/3 h-40 w-40 rounded-full bg-white/10 mix-blend-overlay animate-blob animation-delay-4000"></div>
    </div>

    @php
        $homeUrl = route('home');
        $onHome = url()->current() === $homeUrl;
        $base = $onHome ? '' : $homeUrl;

        $navigationLinks = [
            ['label' => __('site.nav.home'), 'href' => $homeUrl],
            ['label' => __('site.nav.about'), 'href' => $base . '#about'],
            ['label' => __('site.nav.services'), 'href' => $base . '#services'],
            ['label' => __('site.nav.domains'), 'href' => route('domains.index')],
            ['label' => __('site.nav.blog'), 'href' => route('pages.blog.index')],
        ];

        $resourceLinks = [
            ['label' => __('site.footer.resources'), 'href' => $base . '#ressources'],
            ['label' => __('site.nav.faq'), 'href' => $base . '#faq'],
            ['label' => __('site.nav.contact'), 'href' => $base . '#contact'],
            ['label' => __('site.footer.legal'), 'href' => route('legal')],
            ['label' => __('site.footer.privacy'), 'href' => route('privacy')],
        ];

        $quickActions = [
            ['label' => __('site.footer.start_orientation'), 'href' => $base . '#how', 'primary' => true],
            ['label' => __('site.footer.explore_domains'), 'href' => route('domains.index'), 'primary' => false],
            ['label' => __('site.footer.contact_us'), 'href' => $base . '#contact', 'primary' => false],
        ];

        $socialLinks = [
            ['label' => 'LinkedIn', 'icon' => 'linkedin', 'href' => $siteSettings?->linkedin_url ?? 'https://www.linkedin.com/company/OrientationTech'],
            ['label' => 'Instagram', 'icon' => 'instagram', 'href' => $siteSettings?->instagram_url ?? 'https://www.instagram.com/Orientationtech.ma'],
            ['label' => 'YouTube', 'icon' => 'youtube', 'href' => $siteSettings?->youtube_url ?? 'https://youtube.com/@OrientationTech'],
            ['label' => 'Facebook', 'icon' => 'facebook', 'href' => $siteSettings?->facebook_url ?? 'https://www.facebook.com/'],
        ];

        $footerLinkClass = 'group inline-flex items-center gap-2 text-sm text-white/70 transition hover:translate-x-1 hover:text-white';
    @endphp

    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 border-b border-white/20 pb-10 sm:gap-8 lg:grid-cols-[1.35fr_.85fr_.95fr_.95fr_1.15fr] lg:pb-12">
            <div class="space-y-5">
                <a href="{{ $homeUrl }}" class="inline-flex items-center focus:outline-none focus:ring-2 focus:ring-white/60 focus:ring-offset-2 focus:ring-offset-[#172554]" aria-label="{{ __('site.aria.home') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="OrientationTech Logo" class="h-14 w-auto opacity-95 transition hover:scale-105 hover:opacity-100 sm:h-16">
                </a>

                <p class="max-w-sm text-sm leading-7 text-white/80">
                    {{ __('site.footer.desc') }}
                </p>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">{{ __('site.footer.open_source') }}</span>
                    <span class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white/80">{{ __('site.footer.community') }}</span>
                </div>
            </div>

            <nav aria-label="{{ __('site.footer.navigation') }}">
                <h4 class="text-sm font-bold uppercase tracking-[.18em] text-white">{{ __('site.footer.navigation') }}</h4>
                <ul class="mt-5 space-y-3">
                    @foreach ($navigationLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="{{ $footerLinkClass }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-white/40 transition group-hover:bg-white"></span>
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <nav aria-label="{{ __('site.footer.support') }}">
                <h4 class="text-sm font-bold uppercase tracking-[.18em] text-white">{{ __('site.footer.support') }}</h4>
                <ul class="mt-5 space-y-3">
                    @foreach ($resourceLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" class="{{ $footerLinkClass }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-white/40 transition group-hover:bg-white"></span>
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-[.18em] text-white">{{ __('site.footer.quick_actions') }}</h4>
                <div class="mt-5 grid gap-3">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['href'] }}"
                           class="inline-flex items-center justify-center rounded-lg border px-4 py-2.5 text-sm font-bold transition hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-white/60 {{ $action['primary'] ? 'border-white bg-white text-[#1d4ed8] shadow-lg shadow-blue-950/20 hover:bg-blue-50' : 'border-white/20 bg-white/10 text-white hover:border-white/40 hover:bg-white/20' }}">
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-bold uppercase tracking-[.18em] text-white">{{ __('site.footer.account') }}</h4>
                    <ul class="mt-4 space-y-3">
                        @guest
                            <li><a href="{{ route('login') }}" class="{{ $footerLinkClass }}">{{ __('site.footer.login') }}</a></li>
                            <li><a href="{{ route('register') }}" class="{{ $footerLinkClass }}">{{ __('site.footer.register') }}</a></li>
                        @endguest
                        @auth
                            <li><a href="{{ route('profile.edit') }}" class="{{ $footerLinkClass }}">{{ __('site.footer.profile') }}</a></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="{{ $footerLinkClass }}">{{ __('site.footer.logout') }}</button>
                                </form>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-[.18em] text-white">{{ __('site.footer.follow_us') }}</h4>
                <p class="mt-4 text-sm leading-6 text-white/70">{{ __('site.footer.follow_desc') }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($socialLinks as $social)
                        <a href="{{ $social['href'] }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           aria-label="{{ $social['label'] }}"
                           title="{{ $social['label'] }}"
                           class="group flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white/80 shadow-sm transition hover:-translate-y-1 hover:border-white hover:bg-white hover:text-[#2563eb] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-white/70">
                            @switch($social['icon'])
                                @case('linkedin')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.61 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13ZM6.93 20.45H3.75V9h3.18v11.45ZM22.23 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.46C23.2 24 24 23.23 24 22.28V1.72C24 .77 23.2 0 22.23 0Z"/></svg>
                                    @break
                                @case('instagram')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.1" fill="currentColor" stroke="none"/></svg>
                                    @break
                                @case('youtube')
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.13-2.14C19.49 3.56 12 3.56 12 3.56s-7.49 0-9.37.5A3.02 3.02 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3.02 3.02 0 0 0 2.13 2.14c1.88.5 9.37.5 9.37.5s7.49 0 9.37-.5a3.02 3.02 0 0 0 2.13-2.14A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.8ZM9.6 15.57V8.43L15.85 12 9.6 15.57Z"/></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.7 4.53-4.7 1.31 0 2.68.24 2.68.24v2.96h-1.51c-1.49 0-1.96.93-1.96 1.89v2.27h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07Z"/></svg>
                            @endswitch
                        </a>
                    @endforeach
                </div>

                <div class="mt-6 space-y-3 rounded-xl border border-white/20 bg-white/10 p-4 text-sm text-white/75">
                    <p><span class="font-semibold text-white">{{ __('site.footer.email') }}:</span> {{ $siteSettings?->email ?? 'contact@Orientationtech.ma' }}</p>
                    <p><span class="font-semibold text-white">{{ __('site.footer.phone') }}:</span> {{ $siteSettings?->phone ?? '+212 71549452' }}</p>
                    <p class="leading-relaxed"><span class="font-semibold text-white">{{ __('site.footer.address') }}:</span> {{ $siteSettings?->address ?? 'Agadir, 85000 Tiznit, Maroc' }}</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 py-6 text-center text-xs text-white/70 sm:flex-row sm:items-center sm:justify-between sm:text-left">
            <p>
                &copy;
                @if (now()->year > 2025)
                    2025-{{ now()->year }}
                @else
                    2025
                @endif
                OrientationTech. {{ __('site.footer.copyright') }}
            </p>
            <p>{!! __('site.footer.made_with') !!}</p>
        </div>
    </div>
</footer>

<script>
    console.log('%cDeveloped by mohamed abouljid', 'color: #2563eb; font-size: 16px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);');
    console.log('%cMerci', 'color: #3b82f6; font-size: 12px;');
</script>
