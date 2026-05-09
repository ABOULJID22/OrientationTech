@php
    $current = app()->getLocale();
    $locales = [
        'fr' => 'FR',
        'en' => 'EN',
    ];
@endphp

@if (filament()->auth()->check())
    <div class="fi-topbar-lang inline-flex items-center gap-2">
        <nav class="inline-flex items-center gap-1 rounded-full border border-primary-500/20 bg-white/80 p-0.5 text-xs font-medium shadow-sm backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:border-primary-400/30 dark:bg-gray-900/40">
            @foreach ($locales as $locale => $label)
                <a
                    href="/locale/{{ $locale }}"
                    title="{{ $label }}"
                    @class([
                        'inline-flex items-center rounded-full px-3 py-1 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/60 focus-visible:ring-offset-0',
                        'bg-primary-500 text-white shadow-sm dark:bg-primary-400' => $current === $locale,
                        'text-gray-600 hover:bg-primary-500/10 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-primary-400/10 dark:hover:text-primary-300' => $current !== $locale,
                    ])
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <button
            x-data="OrientationTechThemeToggle()"
            x-on:click="toggle()"
            type="button"
            class="fi-theme-toggle"
            aria-label="Changer le thème"
        >
            <span class="fi-theme-toggle-track">
                <span class="fi-theme-toggle-thumb" x-bind:class="{ 'translate-x-full rotate-180': isDark }"></span>
            </span>
            <span class="fi-theme-toggle-icons">
                <x-filament::icon
                    alias="theme-switcher"
                    icon="heroicon-m-sun"
                    class="w-4 h-4"
                    x-show="!isDark"
                    x-cloak
                />
                <x-filament::icon
                    alias="theme-switcher"
                    icon="heroicon-m-moon"
                    class="w-4 h-4"
                    x-show="isDark"
                    x-cloak
                />
            </span>
        </button>
    </div>
@endif

@once
    @push('scripts')
        <script>
            window.OrientationTechThemeToggle = function () {
                return {
                    isDark: document.documentElement.classList.contains('dark'),
                    init() {
                        if (this.$store?.theme) {
                            this.isDark = this.$store.theme.mode === 'dark';
                            this.$watch('$store.theme.mode', (value) => {
                                this.isDark = value === 'dark';
                            });
                        }
                    },
                    toggle() {
                        if (this.$store?.theme?.toggleMode) {
                            this.$store.theme.toggleMode();
                            this.isDark = this.$store.theme.mode === 'dark';
                            return;
                        }

                        this.isDark = !this.isDark;
                        document.documentElement.classList.toggle('dark', this.isDark);
                        window.localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    }
                };
            };
        </script>
    @endpush
@endonce
