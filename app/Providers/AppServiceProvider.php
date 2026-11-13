<?php

namespace App\Providers;

use App\Auth\Http\Responses\SharedLogoutResponse;
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
use App\Policies\ActivityPolicy;
use App\Policies\ActivityRegistrationPolicy;
use App\Policies\AdoptionAgreementPolicy;
use App\Policies\AdoptionRequestPolicy;
use App\Policies\CarePlanPolicy;
use App\Policies\CareRecordPolicy;
use App\Policies\FieldAssignmentPolicy;
use App\Policies\GrowthObservationPolicy;
use App\Policies\ManagerOnlyPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\SitePolicy;
use App\Policies\TreeIssuePolicy;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, SharedLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('shared-login', function (Request $request): Limit {
            $identifier = Str::lower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by(Str::transliterate($identifier.'|'.$request->ip()));
        });

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
        Gate::policy(Site::class, SitePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);

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
