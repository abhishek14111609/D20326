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
        // Check if the old columns exist and drop them if they do
        if (Schema::hasColumn('gifts', 'old_sender_id')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->dropColumn('old_sender_id');
            });
        }
        
        if (Schema::hasColumn('gifts', 'old_receiver_id')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->dropColumn('old_receiver_id');
            });
        }
        
        if (Schema::hasColumn('gifts', 'old_gift_type')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->dropColumn('old_gift_type');
            });
        }
        
        if (Schema::hasColumn('gifts', 'old_context')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->dropColumn('old_context');
            });
        }
        
        // Add any missing columns that should be in the final structure
        if (!Schema::hasColumn('gifts', 'type')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->string('type', 50)->nullable()->after('category_id');
            });
        }
        
        if (!Schema::hasColumn('gifts', 'sender_id')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->unsignedBigInteger('sender_id')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('gifts', 'receiver_id')) {
            Schema::table('gifts', function (Blueprint $table) {
                $table->unsignedBigInteger('receiver_id')->nullable()->after('sender_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a one-way migration
    }
};
