<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PanelShellTest extends DuskTestCase
{
    public function test_manager_panel_uses_the_hijauku_shell_and_works_on_a_phone_width(): void
    {
        $this->browse(function (Browser $browser): void {
            $this->signInAndCheckShell($browser, 'app', 'pengelola@hijauku.test', 'Pengelola');

            $browser->resize(390, 844)
                ->waitFor('.fi-topbar-open-sidebar-btn')
                ->assertScript('document.documentElement.scrollWidth <= window.innerWidth');

            $browser->click('.fi-topbar-open-sidebar-btn')
                ->assertScript('document.querySelector(".fi-sidebar").classList.contains("fi-sidebar-open")');
        });
    }

    public function test_field_staff_panel_uses_the_shared_shell(): void
    {
        $this->browse(fn (Browser $browser) => $this->signInAndCheckShell(
            $browser,
            'petugas',
            'petugas@hijauku.test',
            'Petugas Lapangan',
        ));
    }

    public function test_citizen_panel_uses_the_shared_shell(): void
    {
        $this->browse(fn (Browser $browser) => $this->signInAndCheckShell(
            $browser,
            'warga',
            'warga@hijauku.test',
            'Warga',
        ));
    }

    private function signInAndCheckShell(Browser $browser, string $panel, string $email, string $role): void
    {
        $browser->resize(1440, 900);
        $browser->visit('/app/login');
        $browser->driver->manage()->deleteAllCookies();

        $browser->visit("/{$panel}/login")
            ->type('input[type="email"]', $email)
            ->type('input[type="password"]', (string) env('DEMO_PASSWORD', 'demo-hijauku'))
            ->click('form button[type="submit"]')
            ->waitForLocation("/{$panel}")
            ->assertPresent('.fi-sidebar .fi-logo')
            ->assertSeeIn('.hijauku-user-context__role', $role)
            ->assertScript('getComputedStyle(document.body).fontFamily.includes("Inter")')
            ->assertScript('getComputedStyle(document.body).backgroundColor === "rgb(248, 250, 252)"')
            ->assertScript('document.querySelector(".fi-sidebar").getBoundingClientRect().width === 256')
            ->assertScript('document.querySelector(".fi-topbar").getBoundingClientRect().height === 64');
    }
}
