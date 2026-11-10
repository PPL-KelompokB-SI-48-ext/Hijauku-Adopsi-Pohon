<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_plans', function (Blueprint $table): void {
            $table->string('priority', 24)->default('normal')->after('action');
            $table->index(['priority', 'planned_on']);
        });
    }

    public function down(): void
    {
        Schema::table('care_plans', function (Blueprint $table): void {
            $table->dropIndex(['priority', 'planned_on']);
            $table->dropColumn('priority');
        });
    }
};

