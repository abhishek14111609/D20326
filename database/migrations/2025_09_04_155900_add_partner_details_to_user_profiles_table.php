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
        Schema::table('user_profiles', function (Blueprint $table) {
            // Partner 1 details
            $table->string('partner1_gender', 20)->nullable()->after('partner1_email');
            $table->date('partner1_dob')->nullable()->after('partner1_gender');
            $table->json('partner1_location')->nullable()->after('partner1_dob');
            $table->json('partner1_interest')->nullable()->after('partner1_location');
            $table->json('partner1_hobby')->nullable()->after('partner1_interest');
            
            // Partner 2 details
            $table->string('partner2_gender', 20)->nullable()->after('partner2_email');
            $table->date('partner2_dob')->nullable()->after('partner2_gender');
            $table->json('partner2_location')->nullable()->after('partner2_dob');
            $table->json('partner2_interest')->nullable()->after('partner2_location');
            $table->json('partner2_hobby')->nullable()->after('partner2_interest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'partner1_gender',
                'partner1_dob',
                'partner1_location',
                'partner1_interest',
                'partner1_hobby',
                'partner2_gender',
                'partner2_dob',
                'partner2_location',
                'partner2_interest',
                'partner2_hobby'
            ]);
        });
    }
};
