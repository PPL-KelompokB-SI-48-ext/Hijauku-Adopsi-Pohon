<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Panel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UserPanelAccessTest extends TestCase
{
    public static function activeRoles(): iterable
    {
        foreach (UserRole::cases() as $role) {
            yield $role->value => [$role];
        }
    }

    #[DataProvider('activeRoles')]
    public function test_active_users_with_each_project_role_can_access_the_panel(UserRole $role): void
    {
        $user = new User;
        $user->forceFill([
            'role' => $role,
            'is_active' => true,
        ]);

        self::assertTrue($user->canAccessPanel(new Panel));
    }

    public function test_inactive_users_cannot_access_the_panel(): void
    {
        $user = new User;
        $user->forceFill([
            'role' => UserRole::Manager,
            'is_active' => false,
        ]);

        self::assertFalse($user->canAccessPanel(new Panel));
    }
}
