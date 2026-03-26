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
            if (!Schema::hasColumn('competitions', 'max_participants')) {
                $table->integer('max_participants')->nullable()->after('prizes');
            }
            if (!Schema::hasColumn('competitions', 'min_participants')) {
                $table->integer('min_participants')->default(1)->after('max_participants');
            }
            if (!Schema::hasColumn('competitions', 'entry_fee')) {
                $table->integer('entry_fee')->default(0)->after('min_participants');
            }
            if (!Schema::hasColumn('competitions', 'prizes')) {
                $table->text('prizes')->nullable()->after('entry_fee');
            }
            if (!Schema::hasColumn('competitions', 'rules')) {
                $table->text('rules')->nullable()->after('prizes');
            }
            if (!Schema::hasColumn('competitions', 'judging_criteria')) {
                $table->text('judging_criteria')->nullable()->after('rules');
            }
            if (!Schema::hasColumn('competitions', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('judging_criteria');
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
