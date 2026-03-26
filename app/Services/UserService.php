<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = [];
        
        // Update basic profile fields in user_profiles table
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
            // Also update name in users table
            $user->update(['name' => $data['name']]);
        }
        
        if (isset($data['bio'])) {
            $updateData['bio'] = $data['bio'];
        }
        
        if (isset($data['gender'])) {
            $updateData['gender'] = $data['gender'];
        }
        
        if (isset($data['dob'])) {
            $updateData['dob'] = $data['dob'];
        }
        
        if (isset($data['interests'])) {
            $updateData['interest'] = $data['interests'];
        }
        
        if (isset($data['hobby'])) {
            $updateData['hobby'] = $data['hobby'];
        }
        
        if (isset($data['location'])) {
            $updateData['location'] = $data['location'];
        }
        
        // Update or create user profile
        if ($user->profile) {
            $user->profile->update($updateData);
        } else {
            $user->profile()->create(array_merge($updateData, [
                'name' => $user->name
            ]));
        }
        
        return $user->fresh('profile');
    }

    /**
     * Upload user avatar
     */
	public function uploadAvatar(User $user, UploadedFile $file): User
	{
		// Ensure user has a profile (keep your logic)
		if (!$user->profile) {
			$user->profile()->create([
				'name' => $user->name
			]);
		}

		// 1. Delete old avatar if exists
		if (!empty($user->avatar) && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
			Storage::disk('public')->delete('avatars/' . $user->avatar);
		}

		// 2. Upload new avatar
		$avatarName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
		$file->storeAs('avatars', $avatarName, 'public');

		// 3. Save file name in DB
		$user->avatar = $avatarName;
		$user->save();

		return $user->fresh();
	}

    /**
     * Delete user account
     */
    public function deleteAccount(User $user, string $password): bool
    {
        // Verify password for security
        if ($user->password && !Hash::check($password, $user->password)) {
            throw new \Exception('Invalid password');
        }
        
        // Delete user's files
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }
        
        if ($user->profile_images) {
            $images = json_decode($user->profile_images, true);
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }
        }
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        // Soft delete user
        $user->delete();
        
        return true;
    }

    /**
     * Upload multiple profile images
     */
    public function uploadProfileImages(User $user, array $files): User
    {
        // Ensure user has a profile
        if (!$user->profile) {
            $user->profile()->create([
                'name' => $user->name
            ]);
        }
        
        $existingImages = $user->profile->gallery_images ?? [];
        $newImages = [];
        
        foreach ($files as $file) {
            $path = $file->store('profile-images', 'public');
            $newImages[] = $path;
        }
        
        // Merge with existing images (max 6 total)
        $allImages = array_merge($existingImages, $newImages);
        $allImages = array_slice($allImages, 0, 6);
        
        // Update the gallery_images in the profile
        $user->profile->update(['gallery_images' => $allImages]);
        
        return $user->fresh('profile');
    }

    /**
     * Remove profile image
     */
    public function removeProfileImage(User $user, string $imagePath): User
    {
        $existingImages = $user->profile_images ? json_decode($user->profile_images, true) : [];
        
        // Remove image from array
        $updatedImages = array_filter($existingImages, function($image) use ($imagePath) {
            return $image !== $imagePath;
        });
        
        // Delete file
        Storage::disk('public')->delete($imagePath);
        
        // Update user
        $user->update(['profile_images' => json_encode(array_values($updatedImages))]);
        
        return $user->fresh();
    }

    /**
     * Update user location
     */
    public function updateLocation(User $user, array $location): User
    {
        $user->update(['location' => json_encode($location)]);
        
        return $user->fresh();
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(User $user, array $preferences): User
    {
        $user->update(['preferences' => json_encode($preferences)]);
        
        return $user->fresh();
    }
}
