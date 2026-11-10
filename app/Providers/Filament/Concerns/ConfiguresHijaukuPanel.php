<?php

namespace App\Providers\Filament\Concerns;

use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;

trait ConfiguresHijaukuPanel
{
    protected function configureHijaukuPanel(Panel $panel, string $roleLabel): Panel
    {
        return $panel
            ->brandName('Hijauku')
            ->brandLogo(asset('images/hijauku-brand-sheet.png'))
            ->brandLogoHeight('2.5rem')
            ->font('Inter Variable')
            ->colors(['primary' => Color::hex('#15803D')])
            ->sidebarWidth('16rem')
            ->maxContentWidth(Width::Full)
            ->darkMode(false)
            ->viteTheme('resources/css/filament/theme.css')
            ->navigationGroups([
                $this->navigationGroup('Program & Lokasi'),
                $this->navigationGroup('Inventaris Pohon'),
                $this->navigationGroup('Kegiatan'),
                $this->navigationGroup('Adopsi'),
                $this->navigationGroup('Laporan Masalah'),
                $this->navigationGroup('Perawatan'),
            ])
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn () => view('filament.shared.navigation-heading'),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn () => view('filament.shared.topbar-context', ['roleLabel' => $roleLabel]),
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn () => view('filament.shared.user-context', ['roleLabel' => $roleLabel]),
            );
    }

    private function navigationGroup(string $label): NavigationGroup
    {
        return NavigationGroup::make($label);
    }
}
