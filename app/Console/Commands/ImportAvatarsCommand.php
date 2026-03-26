<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImportAvatarsCommand extends Command
{
    protected $signature = 'import:avatars';
    protected $description = 'Import avatar images from public/assets/img/avatars to users and user_profiles tables';

    public function handle()
    {
        $this->info('Starting avatar import process...');
        
        // Path to the avatars directory
        $avatarsPath = public_path('assets/img/avatars');
        
        // Get all image files from the directory
        $imageFiles = File::files($avatarsPath);
        
        if (empty($imageFiles)) {
            $this->error('No image files found in the avatars directory.');
            return 1;
        }

        $this->info('Found ' . count($imageFiles) . ' image(s) to process.');
        
        // Create a transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            $importedCount = 0;
            $skippedCount = 0;
            
            foreach ($imageFiles as $image) {
                $filename = $image->getFilename();
                
                // Skip non-image files
                if (!in_array(strtolower($image->getExtension()), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $this->warn("Skipping non-image file: " . $filename);
                    $skippedCount++;
                    continue;
                }
                
                // Create a unique username based on the filename
                $username = pathinfo($filename, PATHINFO_FILENAME);
                $email = $username . '@example.com';
                
                // Check if user already exists
                $user = User::where('email', $email)->first();
                
                if (!$user) {
                    // Create a new user
                    $user = User::create([
                        'name' => ucfirst($username),
                        'email' => $email,
                        'password' => bcrypt('password'), // Default password
                        'email_verified_at' => now(),
                    ]);
                    
                    $this->info("Created user: " . $user->email);
                } else {
                    $this->info("Updating user: " . $user->email);
                }
                
                // Add avatar to user's media collection
                $user->addMedia($image->getPathname())
                    ->preservingOriginal()
                    ->toMediaCollection('profile_image');
                
                // Create or update user profile with gallery images
                $galleryImages = [];
                
                // Add the current image to gallery
                $galleryImages[] = 'assets/img/avatars/' . $filename;
                
                // Add some default gallery images (you can modify this as needed)
                for ($i = 1; $i <= 3; $i++) {
                    $galleryImages[] = 'assets/img/avatars/default-avatar.png';
                }
                
                // Create or update user profile
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->name,
                        'bio' => 'Bio for ' . $user->name,
                        'gender' => ['male', 'female', 'other'][array_rand(['male', 'female', 'other'])],
                        'dob' => now()->subYears(rand(18, 50))->subMonths(rand(0, 11))->subDays(rand(0, 30)),
                        'location' => json_encode([
                            'latitude' => 22.3039 + (rand(-100, 100) / 1000), // Random location near Rajkot
                            'longitude' => 70.8022 + (rand(-100, 100) / 1000),
                            'address' => 'Rajkot, Gujarat, India'
                        ]),
                        'interest' => json_encode($this->getRandomInterests()),
                        'hobby' => json_encode($this->getRandomHobbies()),
                        'gallery_images' => json_encode($galleryImages),
                    ]
                );
                
                $importedCount++;
            }
            
            DB::commit();
            
            $this->info("\nImport completed successfully!");
            $this->info("Imported: {$importedCount} users");
            $this->info("Skipped: {$skippedCount} files");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during import: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Get random interests for user profiles
     */
    private function getRandomInterests(): array
    {
        $interests = [
            'Music', 'Sports', 'Travel', 'Photography', 'Cooking',
            'Reading', 'Gaming', 'Movies', 'Technology', 'Art',
            'Fashion', 'Fitness', 'Food', 'Dancing', 'Singing'
        ];
        
        shuffle($interests);
        return array_slice($interests, 0, rand(2, 5));
    }
    
    /**
     * Get random hobbies for user profiles
     */
    private function getRandomHobbies(): array
    {
        $hobbies = [
            'Hiking', 'Cycling', 'Painting', 'Writing', 'Blogging',
            'Yoga', 'Meditation', 'Chess', 'Gardening', 'DIY Projects',
            'Collecting', 'Fishing', 'Swimming', 'Running', 'Drawing'
        ];
        
        shuffle($hobbies);
        return array_slice($hobbies, 0, rand(2, 4));
    }
}
