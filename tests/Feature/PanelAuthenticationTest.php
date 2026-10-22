<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_has_a_login_page_and_requires_authentication_for_the_panel(): void
    {
        foreach (['app', 'petugas', 'warga'] as $panel) {
            $this->get("/{$panel}/login")->assertOk();
            $this->get("/{$panel}")->assertRedirect("/{$panel}/login");
        }
    }

    public function test_authenticated_users_can_open_only_their_role_panel(): void
    {
        $panels = [
            UserRole::Manager->value => 'app',
            UserRole::FieldStaff->value => 'petugas',
            UserRole::Citizen->value => 'warga',
        ];

        foreach ($panels as $role => $allowedPanel) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            foreach ($panels as $panel) {
                $response = $this->actingAs($user)->get("/{$panel}");

                if ($panel === $allowedPanel) {
                    $response->assertOk();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }

    public function test_manager_can_log_in_to_the_manager_panel(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        Filament::setCurrentPanel('app');

        Livewire::test(Login::class)
            ->set('data.email', $manager->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertRedirect('/app');

        $this->assertAuthenticatedAs($manager);
    }

    public function test_user_with_another_role_cannot_log_in_to_the_manager_panel(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        Filament::setCurrentPanel('app');

        Livewire::test(Login::class)
            ->set('data.email', $fieldStaff->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors();

        $this->assertGuest();
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->post('/app/logout')
            ->assertRedirect('/app/login');

        $this->assertGuest();
    }

    public function test_inactive_user_is_denied_from_a_panel(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => false,
        ]);

        $this->actingAs($manager)
            ->get('/app')
            ->assertForbidden();
    }
}
