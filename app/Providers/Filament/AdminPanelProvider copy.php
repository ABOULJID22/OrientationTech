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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\CheckUserIsSuperAdmin;
use Filament\Navigation\NavigationItem;
use Filament\Actions\Action;
use App\Http\Middleware\SetLocaleFromSession;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use App\Filament\Pages\Calendar as CalendarPage;

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
                'primary' => Color::hex('#4f6ba3'),
            ])
            ->brandLogo(fn() => view('filament.admin.logo'))
            ->favicon(asset('favicon.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                CalendarPage::class,
            ])
            //->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                BlogStats::class,
                \App\Filament\Widgets\ClientKpis::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.partials.lang-switch')->render(),
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    .fi-main { max-width: none !important; }
                    
                    /* Topbar avec image de fond */
                    .fi-topbar, .filament-topbar { 
                        background-image: url("/images/bgSide.png") !important;
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                        position: relative !important;
                    }
                    
                    /* Overlay sombre sur topbar pour meilleure lisibilité */
                    .fi-topbar::before, .filament-topbar::before {
                        content: "" !important;
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        right: 0 !important;
                        bottom: 0 !important;
                        z-index: 0 !important;
                    }
                    
                    /* Contenu du topbar au-dessus de l\'overlay */
                    .fi-topbar > *, .filament-topbar > * {
                        position: relative !important;
                        z-index: 1 !important;
                    }
                    
                    .filament-brand { filter: none !important; }
                    
                    .fi-topbar a, .fi-topbar button, .fi-topbar svg, .filament-topbar a, .filament-topbar button, .filament-topbar svg { 
                        color: #ffffff !important; 
                        stroke: #ffffff !important; 
                        fill: #ffffff !important; 
                    }
                    
                    
                    
                    /* Sidebar avec image de fond */
                    .fi-sidebar { 
                        height: auto !important;
                        background-image: url("/images/bgSide.png") !important;
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                        position: relative !important;
                    }
                    
                    /* Overlay sombre sur sidebar pour meilleure lisibilité */
                    .fi-sidebar::before {
                        content: "" !important;
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                        right: 0 !important;
                        bottom: 0 !important;
                        z-index: 0 !important;
                    }
                    
                    /* Navigation et contenu du sidebar au-dessus de l\'overlay */
                    .fi-sidebar-nav, .fi-sidebar > * {
                        position: relative !important;
                        z-index: 1 !important;
                    }
                    
                    .fi-sidebar-nav { 
                        height: 113vh !important; 
                    }
                    
                    /* Navigation items avec meilleure visibilité */
                    .fi-sidebar-nav a, .fi-sidebar-nav button {
                        color: #ffffff !important;
                    }
                    
                    .fi-sidebar-nav .fi-sidebar-item:hover {
                        background-color: rgba(255, 255, 255, 0.1) !important;
                    }
                    
                    body { zoom: 80%; }
                    
                    .fi-dropdown { 
                        transform: scale(1) !important; 
                        z-index: 10 !important; 
                    }
                    
                    .fi-dropdown-panel { 
                        z-index: 10 !important; 
                    }
                    
                    .fi-modal-window { 
                        min-height: 100vh !important; 
                        height: 100vh !important; 
                    }
                </style>',
            )
            ->homeUrl('/')
            ->userMenuItems([
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
            ->databaseNotifications()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make()
                    ->label(__('filament.nav.groups.trade'))
                    ->icon('heroicon-m-shopping-cart')
                    ->sort(1),

                NavigationItem::make()
                    ->label(__('filament.nav.groups.blog'))
                    ->icon('heroicon-m-rectangle-stack')
                    ->sort(2),

                NavigationItem::make()
                    ->label(__('filament.nav.groups.settings'))
                    ->icon('heroicon-m-cog')
                    ->sort(3),
                 NavigationItem::make()
                    ->label(__('filament.nav.groups.support'))
                    ->icon('heroicon-m-cog')
                    ->sort(3),
            ]);

           
            
            


            
             
    }



            public static function canAccess(): bool
    {
        $user = auth()->user();

                // Allow super_admin, client, and assistant to access the panel.
                return $user && ($user->isSuperAdmin() || $user->isClient() || $user->isAssistant());
    }

}
