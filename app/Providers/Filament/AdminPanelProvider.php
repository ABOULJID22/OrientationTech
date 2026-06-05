<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Widgets\BlogStats;
use App\Filament\Widgets\ContentGuidanceOverviewWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\CheckUserIsSuperAdmin;
use Filament\Actions\Action;
use App\Http\Middleware\SetLocaleFromSession;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            /* ->login() */
             ->colors([
                'primary' => Color::hex('#2563eb'),
            ])
            ->darkMode()
            ->brandLogo(fn() => view('filament.admin.logo'))
            ->favicon(asset('favicon.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            //->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\StudentDashboardStats::class,
                \App\Filament\Widgets\StudentPersonalityRadarChart::class,
                \App\Filament\Widgets\StudentDomainCompatibilityChart::class,
                \App\Filament\Widgets\StudentOrientationSummaryWidget::class,
                \App\Filament\Widgets\AdminDashboardStats::class,
                \App\Filament\Widgets\AdminRegistrationsChart::class,
                \App\Filament\Widgets\AdminDomainTrendsChart::class,
                \App\Filament\Widgets\AdminPlatformOverviewWidget::class,
                BlogStats::class,
                ContentGuidanceOverviewWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.partials.lang-switch')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.partials.hide-header')->render(),
            )
            ->globalSearch(false)
            ->databaseNotificationsPolling('10s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocaleFromSession::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                CheckUserIsSuperAdmin::class, //middleware 
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
               
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->homeUrl('/')
            ->userMenuItems([
                Action::make('home')
                    ->label(__('filament.user_menu.home'))
                    ->icon('heroicon-m-home')
                    ->url('/'),
                'profile' => Action::make('profile')
                    ->label('Profil')
                    ->icon('heroicon-m-user')
                    ->url('/profile'),
                Action::make('lang-fr')
                    ->label('Français')
                    ->icon('heroicon-m-language')
                    ->url('/locale/fr')
                    ->sort(-10),
                Action::make('lang-en')
                    ->label('English')
                    ->icon('heroicon-m-language')
                    ->url('/locale/en')
                    ->sort(-9),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->databaseNotifications();

           
            
            


            
             
    }



            public static function canAccess(): bool
    {
        $user = auth()->user();

                return $user && ($user->isSuperAdmin() || $user->isTeacher() || $user->isStudent());
    }

}
