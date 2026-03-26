<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks to avoid issues during migration
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Drop the existing tokens table if it exists
        Schema::dropIfExists('tokens');
        
        // Create the tokens table with raw SQL to ensure exact structure
        DB::statement("
            CREATE TABLE `tokens` (
                `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` bigint UNSIGNED NOT NULL,
                `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `user_agent` text COLLATE utf8mb4_unicode_ci,
                `login_method` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mobile',
                `otp_verified_at` timestamp NULL DEFAULT NULL,
                `user_details` text COLLATE utf8mb4_unicode_ci,
                `expires_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `tokens_token_unique` (`token`),
                KEY `tokens_user_id_token_index` (`user_id`,`token`),
                KEY `tokens_expires_at_index` (`expires_at`),
                CONSTRAINT `tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the tokens table
        Schema::dropIfExists('tokens');
    }
};
