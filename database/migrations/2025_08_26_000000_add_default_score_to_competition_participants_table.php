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
        Schema::table('competition_participants', function (Blueprint $table) {
            if (Schema::hasColumn('competition_participants', 'score')) {
                $table->float('score')->default(0)->change();
            } else {
                $table->float('score')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this as it's just setting a default value
    }
};
