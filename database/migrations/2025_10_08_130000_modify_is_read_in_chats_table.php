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
        Schema::table('chats', function (Blueprint $table) {
            // First, drop the existing is_read column
            $table->dropColumn('is_read');
        });

        Schema::table('chats', function (Blueprint $table) {
            // Add it back as TINYINT(1) which is what MySQL uses for booleans
            $table->boolean('is_read')->default(false)->after('message_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn('is_read');
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->boolean('is_read')->default(false);
        });
    }
};
