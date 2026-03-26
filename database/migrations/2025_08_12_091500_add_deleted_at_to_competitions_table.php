<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the column doesn't exist before adding it
        if (!Schema::hasColumn('competitions', 'deleted_at')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->softDeletes();
            });
            
            // Comment not supported in MariaDB, using standard Laravel soft deletes
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only remove the column if it exists
        if (Schema::hasColumn('competitions', 'deleted_at')) {
            Schema::table('competitions', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
