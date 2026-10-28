<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Activity;
use App\Models\AdoptionRequest;
use App\Models\CareRecord;
use App\Models\FieldAssignment;
use App\Models\Program;
use App\Models\TreeIssue;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    public function test_only_managers_can_manage_master_data(): void
    {
        $manager = $this->user(1, UserRole::Manager);
        $fieldStaff = $this->user(2, UserRole::FieldStaff);
        $citizen = $this->user(3, UserRole::Citizen);

        self::assertTrue(Gate::forUser($manager)->allows('create', Program::class));
        self::assertFalse(Gate::forUser($fieldStaff)->allows('create', Program::class));
        self::assertFalse(Gate::forUser($citizen)->allows('viewAny', Program::class));

        self::assertTrue(Gate::forUser($manager)->allows('create', Activity::class));
        self::assertFalse(Gate::forUser($fieldStaff)->allows('create', Activity::class));
        self::assertFalse(Gate::forUser($citizen)->allows('viewAny', Activity::class));
    }

    public function test_field_staff_can_only_view_their_own_assignment(): void
    {
        $fieldStaff = $this->user(20, UserRole::FieldStaff);
        $own = new FieldAssignment;
        $own->forceFill(['assignee_id' => 20]);
        $other = new FieldAssignment;
        $other->forceFill(['assignee_id' => 21]);

        self::assertTrue(Gate::forUser($fieldStaff)->allows('viewAny', FieldAssignment::class));
        self::assertTrue(Gate::forUser($fieldStaff)->allows('view', $own));
        self::assertFalse(Gate::forUser($fieldStaff)->allows('view', $other));
        self::assertFalse(Gate::forUser($fieldStaff)->allows('update', $own));
    }

    public function test_citizen_can_create_and_view_only_their_own_records(): void
    {
        $citizen = $this->user(30, UserRole::Citizen);
        $ownIssue = new TreeIssue;
        $ownIssue->forceFill(['reporter_id' => 30]);
        $otherIssue = new TreeIssue;
        $otherIssue->forceFill(['reporter_id' => 31]);

        self::assertTrue(Gate::forUser($citizen)->allows('create', TreeIssue::class));
        self::assertTrue(Gate::forUser($citizen)->allows('view', $ownIssue));
        self::assertFalse(Gate::forUser($citizen)->allows('view', $otherIssue));
        self::assertFalse(Gate::forUser($citizen)->allows('update', $ownIssue));
    }

    public function test_manager_cannot_review_their_own_submission(): void
    {
        $manager = $this->user(40, UserRole::Manager);
        $ownRequest = new AdoptionRequest;
        $ownRequest->forceFill(['requester_id' => 40]);
        $otherRequest = new AdoptionRequest;
        $otherRequest->forceFill(['requester_id' => 41]);

        self::assertFalse(Gate::forUser($manager)->allows('review', $ownRequest));
        self::assertTrue(Gate::forUser($manager)->allows('review', $otherRequest));
        self::assertFalse(Gate::forUser($manager)->allows('update', $ownRequest));
        self::assertTrue(Gate::forUser($manager)->allows('update', $otherRequest));
    }

    public function test_manager_cannot_verify_their_own_care_record(): void
    {
        $manager = $this->user(50, UserRole::Manager);
        $ownRecord = new CareRecord;
        $ownRecord->forceFill(['performed_by' => 50]);
        $otherRecord = new CareRecord;
        $otherRecord->forceFill(['performed_by' => 51]);

        self::assertFalse(Gate::forUser($manager)->allows('verify', $ownRecord));
        self::assertTrue(Gate::forUser($manager)->allows('verify', $otherRecord));
        self::assertFalse(Gate::forUser($manager)->allows('update', $ownRecord));
        self::assertTrue(Gate::forUser($manager)->allows('update', $otherRecord));
    }

    private function user(int $id, UserRole $role): User
    {
        $user = new User;
        $user->forceFill([
            'id' => $id,
            'role' => $role,
            'is_active' => true,
        ]);

        return $user;
    }
}
