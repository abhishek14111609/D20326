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
        if (Schema::hasTable('competition_participants')) {
            Schema::table('competition_participants', function (Blueprint $table) {
                if (!Schema::hasColumn('competition_participants', 'status')) {
                    $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending')->after('user_id');
                }
                
                if (!Schema::hasColumn('competition_participants', 'submission')) {
                    $table->text('submission')->nullable()->after('status');
                }
                
                if (Schema::hasColumn('competition_participants', 'submitted_at')) {
                    $table->dropColumn('submitted_at');
                }
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
        if (Schema::hasTable('competition_participants')) {
            Schema::table('competition_participants', function (Blueprint $table) {
                if (Schema::hasColumn('competition_participants', 'status')) {
                    $table->dropColumn('status');
                }
                
                if (Schema::hasColumn('competition_participants', 'submission')) {
                    $table->dropColumn('submission');
                }
                
                if (!Schema::hasColumn('competition_participants', 'submitted_at')) {
                    $table->dateTime('submitted_at')->nullable();
                }
            });
        }
    }
};
