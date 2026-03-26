<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string');
            $table->string('group', 50)->default('general');
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->json('options')->nullable()->comment('JSON options for select, radio, etc.');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['group', 'key']);
        });
        
        // Insert default settings
        $this->seedDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
    
    /**
     * Seed default settings
     */
    protected function seedDefaultSettings(): void
    {
        $settings = [
            // App Settings
            [
                'key' => 'app_name',
                'value' => 'DUOS',
                'type' => 'string',
                'group' => 'app',
                'display_name' => 'Application Name',
                'description' => 'The name of the application',
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'app_url',
                'value' => 'http://localhost:8000',
                'type' => 'url',
                'group' => 'app',
                'display_name' => 'Application URL',
                'description' => 'The base URL of the application',
                'is_public' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'timezone',
                'group' => 'app',
                'display_name' => 'Timezone',
                'description' => 'The default timezone for the application',
                'is_public' => true,
                'sort_order' => 3,
            ],
            
            // User Settings
            [
                'key' => 'user_registration',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'user',
                'display_name' => 'User Registration',
                'description' => 'Allow new user registration',
                'is_public' => true,
                'sort_order' => 10,
            ],
            [
                'key' => 'email_verification',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'user',
                'display_name' => 'Email Verification',
                'description' => 'Require email verification for new users',
                'is_public' => true,
                'sort_order' => 11,
            ],
            
            // Swipe Settings
            [
                'key' => 'max_daily_swipes',
                'value' => '100',
                'type' => 'number',
                'group' => 'swipe',
                'display_name' => 'Max Daily Swipes',
                'description' => 'Maximum number of swipes allowed per day for free users',
                'is_public' => true,
                'sort_order' => 20,
            ],
            [
                'key' => 'max_super_likes',
                'value' => '5',
                'type' => 'number',
                'group' => 'swipe',
                'display_name' => 'Max Super Likes',
                'description' => 'Maximum number of super likes allowed per day',
                'is_public' => true,
                'sort_order' => 21,
            ],
            
            // Chat Settings
            [
                'key' => 'chat_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'chat',
                'display_name' => 'Enable Chat',
                'description' => 'Enable/disable chat functionality',
                'is_public' => true,
                'sort_order' => 30,
            ],
            [
                'key' => 'chat_message_length',
                'value' => '1000',
                'type' => 'number',
                'group' => 'chat',
                'display_name' => 'Max Message Length',
                'description' => 'Maximum length of a chat message in characters',
                'is_public' => true,
                'sort_order' => 31,
            ],
        ];
        
        // Insert default settings
        foreach ($settings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
