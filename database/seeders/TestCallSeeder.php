<?php

namespace Database\Seeders;

use App\Models\AudioCall;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestCallSeeder extends Seeder
{
    public function run()
    {
        // Create test users if they don't exist
        $caller = User::firstOrCreate(
            ['email' => 'caller@example.com'],
            [
                'name' => 'Test Caller',
                'password' => bcrypt('password'),
            ]
        );

        $receiver = User::firstOrCreate(
            ['email' => 'receiver@example.com'],
            [
                'name' => 'Test Receiver',
                'password' => bcrypt('password'),
            ]
        );

        // Create a test call
        $call = AudioCall::create([
            'call_id' => (string) Str::uuid(),
            'caller_id' => $caller->id,
            'receiver_id' => $receiver->id,
            'status' => AudioCall::STATUS_INITIATED,
            'agora_channel' => 'test_channel_' . time(),
            'agora_token' => 'test_token_' . time(),
            'agora_rtm_token' => 'test_rtm_token_' . time(),
            'started_at' => now(),
            'is_muted' => false,
        ]);

        $this->command->info('Test call created successfully!');
        $this->command->info('Call ID: ' . $call->call_id);
        $this->command->info('Caller ID: ' . $caller->id);
        $this->command->info('Receiver ID: ' . $receiver->id);
    }
}
