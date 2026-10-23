<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\ActivityRegistration;
use App\Models\AdoptionAgreement;
use App\Models\AdoptionRequest;
use App\Models\CarePlan;
use App\Models\CareRecord;
use App\Models\EnvironmentArticle;
use App\Models\FieldAssignment;
use App\Models\GalleryEntry;
use App\Models\GreenActionProposal;
use App\Models\GrowthObservation;
use App\Models\PlantingBatch;
use App\Models\Plot;
use App\Models\Program;
use App\Models\Site;
use App\Models\Tree;
use App\Models\TreeAdoptionAllocation;
use App\Models\TreeIssue;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Policies\ActivityRegistrationPolicy;
use App\Policies\AdoptionAgreementPolicy;
use App\Policies\AdoptionRequestPolicy;
use App\Policies\CarePlanPolicy;
use App\Policies\CareRecordPolicy;
use App\Policies\FieldAssignmentPolicy;
use App\Policies\GrowthObservationPolicy;
use App\Policies\ManagerOnlyPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\TreeIssuePolicy;
use App\Policies\TreeSpeciesPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $managerOnlyModels = [
            Activity::class,
            ActivityLog::class,
            EnvironmentArticle::class,
            GalleryEntry::class,
            GreenActionProposal::class,
            PlantingBatch::class,
            Plot::class,
            Program::class,
            Site::class,
            Tree::class,
            TreeAdoptionAllocation::class,
            TreeSpecies::class,
            User::class,
        ];

        foreach ($managerOnlyModels as $model) {
            Gate::policy($model, ManagerOnlyPolicy::class);
        }

        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(TreeSpecies::class, TreeSpeciesPolicy::class);

        Gate::policy(ActivityRegistration::class, ActivityRegistrationPolicy::class);
        Gate::policy(AdoptionAgreement::class, AdoptionAgreementPolicy::class);
        Gate::policy(AdoptionRequest::class, AdoptionRequestPolicy::class);
        Gate::policy(CarePlan::class, CarePlanPolicy::class);
        Gate::policy(CareRecord::class, CareRecordPolicy::class);
        Gate::policy(FieldAssignment::class, FieldAssignmentPolicy::class);
        Gate::policy(GrowthObservation::class, GrowthObservationPolicy::class);
        Gate::policy(TreeIssue::class, TreeIssuePolicy::class);
    }
}
