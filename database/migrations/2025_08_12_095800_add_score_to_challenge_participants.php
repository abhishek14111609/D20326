<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('challenge_participants') && !Schema::hasColumn('challenge_participants', 'score')) {
            Schema::table('challenge_participants', function (Blueprint $table) {
                $table->decimal('score', 10, 2)->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('challenge_participants', 'score')) {
            Schema::table('challenge_participants', function (Blueprint $table) {
                $table->dropColumn('score');
            });
        }
    }
};
