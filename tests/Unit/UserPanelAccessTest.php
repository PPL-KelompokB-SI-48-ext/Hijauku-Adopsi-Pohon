<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Panel;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserPanelAccessTest extends TestCase
{
    public static function activeRoles(): iterable
    {
        yield 'manager only enters manager panel' => [UserRole::Manager, 'app', true];
        yield 'manager cannot enter field panel' => [UserRole::Manager, 'petugas', false];
        yield 'manager cannot enter citizen panel' => [UserRole::Manager, 'warga', false];
        yield 'field staff only enters field panel' => [UserRole::FieldStaff, 'petugas', true];
        yield 'field staff cannot enter manager panel' => [UserRole::FieldStaff, 'app', false];
        yield 'field staff cannot enter citizen panel' => [UserRole::FieldStaff, 'warga', false];
        yield 'citizen only enters citizen panel' => [UserRole::Citizen, 'warga', true];
        yield 'citizen cannot enter manager panel' => [UserRole::Citizen, 'app', false];
        yield 'citizen cannot enter field panel' => [UserRole::Citizen, 'petugas', false];
    }

    #[DataProvider('activeRoles')]
    public function test_active_users_only_access_their_role_panel(UserRole $role, string $panelId, bool $expected): void
    {
        $user = new User;
        $user->forceFill([
            'role' => $role,
            'is_active' => true,
        ]);

        self::assertSame($expected, $user->canAccessPanel(Panel::make()->id($panelId)));
    }

    public function test_inactive_users_cannot_access_the_panel(): void
    {
        $user = new User;
        $user->forceFill([
            'role' => UserRole::Manager,
            'is_active' => false,
        ]);

        self::assertFalse($user->canAccessPanel(Panel::make()->id('app')));
    }

    public function test_unknown_panels_are_denied(): void
    {
        $user = new User;
        $user->forceFill([
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        self::assertFalse($user->canAccessPanel(Panel::make()->id('unknown')));
    }
}
