{{-- resources/views/filament/partials/theme-toggle.blade.php --}}
<div class="flex items-center px-2">
    <button
        x-data
        x-on:click="$store.theme.toggleMode()"
        type="button"
        class="fi-btn fi-btn-icon fi-btn-size-md fi-theme-switcher-btn"
        aria-label="Changer le thème"
    >
        <x-filament::icon
            alias="theme-switcher"
            icon="heroicon-m-sun"
            class="dark:hidden w-5 h-5"
        />
        <x-filament::icon
            alias="theme-switcher"
            icon="heroicon-m-moon"
            class="hidden dark:block w-5 h-5"
        />
    </button>
</div>
