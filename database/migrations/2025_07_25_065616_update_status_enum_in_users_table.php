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
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing status column
            $table->dropColumn('status');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Add status column with updated enum values including 'disabled'
            $table->enum('status', ['pending_verification', 'active', 'inactive', 'disabled'])
                  ->default('pending_verification')
                  ->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
