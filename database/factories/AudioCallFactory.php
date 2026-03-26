<?php

namespace Database\Factories;

use App\Models\AudioCall;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AudioCallFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AudioCall::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'call_id' => (string) Str::uuid(),
            'caller_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => 'initiated',
            'started_at' => now(),
            'is_muted' => false,
        ];
    }

    /**
     * Set the call as in progress.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inProgress()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'in_progress',
                'accepted_at' => now(),
            ];
        });
    }

    /**
     * Set the call as ended.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'ended',
                'accepted_at' => now()->subMinutes(5),
                'ended_at' => now(),
                'duration' => 300, // 5 minutes in seconds
            ];
        });
    }

    /**
     * Set the call as rejected.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function rejected()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'rejected',
                'ended_at' => now(),
            ];
        });
    }

    /**
     * Set the call as muted.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function muted()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_muted' => true,
            ];
        });
    }
}
