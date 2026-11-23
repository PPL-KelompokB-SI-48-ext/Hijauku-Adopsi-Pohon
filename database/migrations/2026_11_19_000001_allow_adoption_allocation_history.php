<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tree_adoption_allocations', function (Blueprint $table) {
            $table->index(['tree_id', 'status']);
        });

        Schema::table('tree_adoption_allocations', function (Blueprint $table) {
            $table->dropUnique('tree_adoption_allocations_tree_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tree_adoption_allocations', function (Blueprint $table) {
            $table->unique('tree_id');
        });

        Schema::table('tree_adoption_allocations', function (Blueprint $table) {
            $table->dropIndex('tree_adoption_allocations_tree_id_status_index');
        });
    }
};
