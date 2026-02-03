<style>
    .fi-simple-header .fi-logo {
        height: unset !important;
    }

    .fi-simple-header .fi-logo .logo-box,
    .fi-topbar .fi-logo .logo-box {
        height: 137px;
        width: 249px;
    }

    .fi-simple-header .fi-logo img,
    .fi-topbar .fi-logo img {
        height: 100%;
        width: 100%;
    }

    .fi-sidebar-header img,
    .fi-topbar .logo-box img {
        height: unset !important;
        width: 158px !important;
        margin-top: -10px !important;
    }

    .fi-sidebar-header .logo-box {
        margin-top: -27px;
    }

    /* When the Filament sidebar becomes an overlay on small screens,
       hide the sidebar header to avoid a white header bar showing.
       This targets the header inside the aside layout on small viewports. */
    @media (max-width: 1023px) {
        .fi-sidebar-header,
        .fi-layout aside > div > header {
            display: none !important;
        }
    }
</style>

@php
    // Fallback logic: if the admin settings provide a dedicated dark logo, use it.
    // Otherwise we fallback to the regular logo so nothing breaks.
    $logo = $siteSettings?->logo_path ? Storage::url($siteSettings->logo_path) : asset('images/logo.png');
    $logoDark = $siteSettings?->dark_logo_path ? Storage::url($siteSettings->dark_logo_path) : $logo;
@endphp

<div class="logo-box">
    {{-- Light mode logo: visible by default, hidden in dark mode --}}
    <img src="{{ $logo }}" alt="Offitrade Logo" class="h-12 mb-4 block dark:hidden" />

    {{-- Dark mode logo: hidden by default, visible when .dark class is present --}}
    <img src="{{ $logoDark }}" alt="Offitrade Logo (dark)" class="h-12 mb-4 hidden dark:block" />
</div>
