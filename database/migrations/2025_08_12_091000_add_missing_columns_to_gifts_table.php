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
        Schema::table('gifts', function (Blueprint $table) {
            // Add sender_id column with foreign key to users table
            $table->unsignedBigInteger('sender_id')->nullable()->after('is_active');
            $table->foreign('sender_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Add receiver_id column with foreign key to users table
            $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
            $table->foreign('receiver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Add type column
            $table->string('type', 50)->default('gift')->after('receiver_id');
            
            // Add context column
            $table->string('context', 100)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            
            // Drop the columns
            $table->dropColumn(['sender_id', 'receiver_id', 'type', 'context']);
        });
    }
};
