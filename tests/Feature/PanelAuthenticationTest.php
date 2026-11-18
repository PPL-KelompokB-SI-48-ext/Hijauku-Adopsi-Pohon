<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PanelAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_role_uses_one_login_page_and_panel_aliases_redirect_to_it(): void
    {
        $this->get('/login')->assertOk();

        foreach (['app', 'petugas', 'warga'] as $panel) {
            $this->get("/{$panel}/login")->assertRedirect('/login');
            $this->get("/{$panel}")->assertRedirect('/login');
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

    public function test_shared_login_routes_each_active_role_to_their_panel(): void
    {
        $accounts = [
            [UserRole::Manager, 'app'],
            [UserRole::FieldStaff, 'petugas'],
            [UserRole::Citizen, 'warga'],
        ];

        foreach ($accounts as [$role, $panel]) {
            Auth::logout();
            $this->flushSession();

            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect("/{$panel}");

            $this->assertAuthenticatedAs($user);
        }
    }

    public function test_field_staff_uses_shared_login_and_still_cannot_open_manager_panel(): void
    {
        $fieldStaff = User::factory()->create([
            'role' => UserRole::FieldStaff,
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => $fieldStaff->email,
            'password' => 'password',
        ])->assertRedirect('/petugas');

        $this->assertAuthenticatedAs($fieldStaff);
        $this->get('/app')->assertForbidden();
    }

    public function test_logout_clears_the_authenticated_session(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        $this->actingAs($manager)
            ->post('/app/logout')
            ->assertRedirect('/login');

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
