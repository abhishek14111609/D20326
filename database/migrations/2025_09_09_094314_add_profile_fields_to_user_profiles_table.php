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
            $table->string('relationship_status')->nullable()->after('hobby');
            $table->string('occupation')->nullable()->after('hobby');
            $table->string('languages')->nullable()->after('occupation');
        });
    }
    
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'relationship_status',
                'occupation',
                'languages',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    
};
