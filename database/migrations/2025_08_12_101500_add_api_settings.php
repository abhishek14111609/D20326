<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $settings = [
            [
                'key' => 'google_maps_api_key',
                'value' => '',
                'type' => 'password',
                'group' => 'api',
                'display_name' => 'Google Maps API Key',
                'description' => 'API key for Google Maps services',
                'is_public' => false,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'video_call_api_key',
                'value' => '',
                'type' => 'password',
                'group' => 'api',
                'display_name' => 'Video Call API Key',
                'description' => 'API key for video call services',
                'is_public' => false,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'audio_call_api_key',
                'value' => '',
                'type' => 'password',
                'group' => 'api',
                'display_name' => 'Audio Call API Key',
                'description' => 'API key for audio call services',
                'is_public' => false,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'enable_push_notifications',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'notifications',
                'display_name' => 'Enable Push Notifications',
                'description' => 'Enable or disable push notifications system-wide',
                'is_public' => false,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($settings as $setting) {
            if (!DB::table('settings')->where('key', $setting['key'])->exists()) {
                DB::table('settings')->insert($setting);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'google_maps_api_key',
            'video_call_api_key',
            'audio_call_api_key',
            'enable_push_notifications',
        ];

        DB::table('settings')->whereIn('key', $keys)->delete();
    }
};
