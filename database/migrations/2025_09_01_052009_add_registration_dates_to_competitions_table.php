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
        Schema::table('competitions', function (Blueprint $table) {
            $table->dateTime('registration_start')->after('banner_image');
            $table->dateTime('registration_end')->after('registration_start');
        });
    }
    
    public function down()
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn(['registration_start', 'registration_end']);
        });
    }
};
