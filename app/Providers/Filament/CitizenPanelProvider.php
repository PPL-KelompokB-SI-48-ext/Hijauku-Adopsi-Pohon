<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectToSharedLogin;
use App\Providers\Filament\Concerns\ConfiguresHijaukuPanel;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CitizenPanelProvider extends PanelProvider
{
    use ConfiguresHijaukuPanel;

    public function panel(Panel $panel): Panel
    {
        return $this->configureHijaukuPanel($panel, 'Warga')
            ->id('warga')
            ->path('warga')
            ->login(null)
            ->discoverResources(in: app_path('Filament/Citizen/Resources'), for: 'App\\Filament\\Citizen\\Resources')
            ->discoverPages(in: app_path('Filament/Citizen/Pages'), for: 'App\\Filament\\Citizen\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Citizen/Widgets'), for: 'App\\Filament\\Citizen\\Widgets')
            ->widgets([AccountWidget::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([RedirectToSharedLogin::class]);
    }
}
