<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class AddTestLocationsSeeder extends Seeder
{
    public function run()
    {
        // Update locations for single profiles
        $singleProfiles = UserProfile::where('is_couple', false)->take(5)->get();
        $singleLocations = [
            'Mavadi, Rajkot',
            'Kalavad Road, Rajkot',
            'University Road, Rajkot',
            '150ft Ring Road, Rajkot',
            'Gondal Road, Rajkot'
        ];

        foreach ($singleProfiles as $index => $profile) {
            $location = $singleLocations[$index % count($singleLocations)] ?? 'Rajkot';
            $profile->update(['location' => $location]);
            $this->command->info("Updated location for single profile {$profile->id}: {$location}");
        }

        // Update locations for duo profiles
        $duoProfiles = UserProfile::where('is_couple', true)->take(5)->get();
        $partner1Locations = [
            'Mavadi, Rajkot',
            'Kalavad Road, Rajkot',
            'University Road, Rajkot',
        ];

        $partner2Locations = [
            '150ft Ring Road, Rajkot',
            'Gondal Road, Rajkot',
            'Kalawad Road, Rajkot'
        ];

        foreach ($duoProfiles as $index => $profile) {
            $loc1 = $partner1Locations[$index % count($partner1Locations)] ?? 'Rajkot';
            $loc2 = $partner2Locations[$index % count($partner2Locations)] ?? 'Rajkot';
            
            $profile->update([
                'partner1_location' => $loc1,
                'partner2_location' => $loc2
            ]);
            
            $this->command->info("Updated locations for duo profile {$profile->id}: Partner1={$loc1}, Partner2={$loc2}");
        }
    }
}