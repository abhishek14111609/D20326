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
            // Duo/Couple registration fields
            $table->string('couple_name')->nullable()->after('name'); // Shared couple name
            $table->string('partner1_name')->nullable()->after('couple_name'); // First partner name
            $table->string('partner1_email')->nullable()->after('partner1_name'); // First partner email
            $table->string('partner1_photo')->nullable()->after('partner1_email'); // First partner photo
            $table->string('partner2_name')->nullable()->after('partner1_photo'); // Second partner name
            $table->string('partner2_email')->nullable()->after('partner2_name'); // Second partner email
            $table->string('partner2_photo')->nullable()->after('partner2_email'); // Second partner photo
            $table->enum('registration_type', ['single', 'duo'])->default('single')->after('partner2_photo'); // Registration type
            $table->boolean('is_couple')->default(false)->after('registration_type'); // Is couple profile
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //
        });
    }
};
