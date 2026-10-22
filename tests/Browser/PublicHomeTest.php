<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PublicHomeTest extends DuskTestCase
{
    public function test_public_homepage_shows_the_adoption_steps_and_actions(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')
                ->assertTitle('Hijauku - Adopsi pohon, rawat pertumbuhannya')
                ->assertSee('Kenali dan Dukung Perawatan Pohon di Sekitarmu')
                ->assertSeeLink('Jelajahi Pohon')
                ->assertSeeLink('Lihat Kegiatan')
                ->assertSee('Ajukan Adopsi');
        });
    }
}
