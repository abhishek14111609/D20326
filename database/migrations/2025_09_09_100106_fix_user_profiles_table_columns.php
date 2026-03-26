<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            // Check and add only if columns don't exist
            if (!Schema::hasColumn('user_profiles', 'relationship_status')) {
                $table->string('relationship_status')->nullable()->after('hobby');
            }
            if (!Schema::hasColumn('user_profiles', 'occupation')) {
                $table->string('occupation')->nullable()->after('relationship_status');
            }
            if (!Schema::hasColumn('user_profiles', 'languages')) {
                $table->string('languages')->nullable()->after('occupation');
            }
            if (!Schema::hasColumn('user_profiles', 'dob')) {
                $table->date('dob')->nullable()->after('location');
            }
            if (!Schema::hasColumn('user_profiles', 'gender')) {
                $table->string('gender', 10)->nullable()->after('dob');
            }
        });
    }
    
    public function down()
    {
        // Safe to keep as is, it will only drop if they exist
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumnIfExists('relationship_status');
            $table->dropColumnIfExists('occupation');
            $table->dropColumnIfExists('languages');
            $table->dropColumnIfExists('dob');
            $table->dropColumnIfExists('gender');
        });
    }
};
