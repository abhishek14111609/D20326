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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->string('device_type')->nullable()->after('name'); // android, ios, web
            $table->string('device_token')->nullable()->after('device_type'); // FCM/push token
            $table->string('ip_address')->nullable()->after('device_token');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->enum('login_method', ['mobile', 'email', 'social'])->nullable()->after('user_agent');
            $table->timestamp('otp_verified_at')->nullable()->after('login_method');
            $table->json('user_details')->nullable()->after('otp_verified_at'); // Store user profile snapshot
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'device_type',
                'device_token', 
                'ip_address',
                'user_agent',
                'login_method',
                'otp_verified_at',
                'user_details'
            ]);
        });
    }
};
