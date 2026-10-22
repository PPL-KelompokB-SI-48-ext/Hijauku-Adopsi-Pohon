<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('objective');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->index(['status', 'starts_on']);
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->index(['program_id', 'name']);
        });

        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->unsignedSmallInteger('capacity');
            $table->string('status', 24)->default('open');
            $table->timestamps();
            $table->unique(['site_id', 'code']);
        });

        Schema::create('tree_species', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('care_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'name']);
        });

        Schema::create('planting_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained()->restrictOnDelete();
            $table->string('code', 40);
            $table->date('planted_on');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['plot_id', 'code']);
            $table->unique(['id', 'plot_id']);
        });

        Schema::create('trees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained('tree_species')->restrictOnDelete();
            $table->foreignId('plot_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('code', 60)->unique();
            $table->date('planted_on')->nullable();
            $table->string('initial_condition', 32);
            $table->timestamps();
            $table->foreign(['batch_id', 'plot_id'])
                ->references(['id', 'plot_id'])
                ->on('planting_batches')
                ->restrictOnDelete();
            $table->index(['plot_id', 'initial_condition']);
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('kind', 32);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedSmallInteger('quota');
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->index(['site_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });

        Schema::create('activity_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('status', 24)->default('registered');
            $table->dateTime('attended_at')->nullable();
            $table->timestamps();
            $table->unique(['activity_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('field_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->restrictOnDelete();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('assignee_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('due_at')->nullable();
            $table->string('status', 24)->default('assigned');
            $table->timestamps();
            $table->index(['assignee_id', 'status', 'due_at']);
            $table->index(['activity_id', 'tree_id']);
        });

        Schema::create('adoption_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('requester_id')->constrained('users')->restrictOnDelete();
            $table->text('reason');
            $table->string('status', 24)->default('draft');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_reason')->nullable();
            $table->timestamps();
            $table->index(['tree_id', 'status']);
            $table->index(['requester_id', 'status']);
        });

        Schema::create('adoption_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('adoption_requests')->restrictOnDelete();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('adopter_id')->constrained('users')->restrictOnDelete();
            $table->text('terms');
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('status', 24)->default('awaiting_acceptance');
            $table->dateTime('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['adopter_id', 'status']);
            $table->index(['tree_id', 'status']);
        });

        Schema::create('tree_adoption_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('request_id')->unique()->constrained('adoption_requests')->restrictOnDelete();
            $table->foreignId('agreement_id')->nullable()->unique()->constrained('adoption_agreements')->restrictOnDelete();
            $table->string('status', 24)->default('awaiting_acceptance');
            $table->dateTime('reserved_at');
            $table->timestamps();
        });

        Schema::create('tree_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->restrictOnDelete();
            $table->text('description');
            $table->string('severity', 24)->default('normal');
            $table->string('evidence_path')->nullable();
            $table->string('status', 24)->default('draft');
            $table->text('resolution')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'severity']);
            $table->index(['tree_id', 'status']);
        });

        Schema::create('care_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('assignment_id')->nullable()->constrained('field_assignments')->restrictOnDelete();
            $table->date('planned_on');
            $table->text('action');
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->index(['status', 'planned_on']);
            $table->index(['tree_id', 'planned_on']);
        });

        Schema::create('care_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('care_plans')->restrictOnDelete();
            $table->foreignId('performed_by')->constrained('users')->restrictOnDelete();
            $table->date('performed_on');
            $table->text('action');
            $table->string('photo_path')->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['tree_id', 'performed_on']);
            $table->index(['status', 'performed_on']);
        });

        Schema::create('growth_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tree_id')->constrained()->restrictOnDelete();
            $table->foreignId('observed_by')->constrained('users')->restrictOnDelete();
            $table->date('observed_on');
            $table->decimal('height_cm', 8, 2)->nullable();
            $table->decimal('diameter_cm', 8, 2)->nullable();
            $table->string('condition', 32);
            $table->string('photo_path')->nullable();
            $table->string('status', 24)->default('draft');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->unique(['tree_id', 'observed_on']);
            $table->index(['status', 'observed_on']);
        });

        Schema::create('environment_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->string('status', 24)->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('gallery_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->text('caption')->nullable();
            $table->string('image_path');
            $table->string('status', 24)->default('draft');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('green_action_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposer_id')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('site_description');
            $table->date('proposed_on')->nullable();
            $table->unsignedSmallInteger('participants_estimate')->nullable();
            $table->longText('description');
            $table->string('status', 24)->default('draft');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_reason')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index(['proposer_id', 'status']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->nullableMorphs('subject');
            $table->string('from_status', 24)->nullable();
            $table->string('to_status', 24)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['actor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('green_action_proposals');
        Schema::dropIfExists('gallery_entries');
        Schema::dropIfExists('environment_articles');
        Schema::dropIfExists('growth_observations');
        Schema::dropIfExists('care_records');
        Schema::dropIfExists('care_plans');
        Schema::dropIfExists('tree_issues');
        Schema::dropIfExists('tree_adoption_allocations');
        Schema::dropIfExists('adoption_agreements');
        Schema::dropIfExists('adoption_requests');
        Schema::dropIfExists('field_assignments');
        Schema::dropIfExists('activity_registrations');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('trees');
        Schema::dropIfExists('planting_batches');
        Schema::dropIfExists('tree_species');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('programs');
    }
};
