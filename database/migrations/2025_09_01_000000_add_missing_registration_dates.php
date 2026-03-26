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
        Schema::table('competitions', function (Blueprint $table) {
            if (!Schema::hasColumn('competitions', 'registration_start')) {
                $table->dateTime('registration_start')->nullable();
            }
            
            if (!Schema::hasColumn('competitions', 'registration_end')) {
                $table->dateTime('registration_end')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't drop the columns in the down method to prevent data loss
        // If you need to rollback, create a new migration
    }
};
