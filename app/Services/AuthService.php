<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Complete registration after OTP verification
     */
    public function completeRegistration(array $data)
    {
        DB::beginTransaction();
		
        try {
            // Validate required fields
            if (empty($data['mobile'])) {
                throw new \Exception('Mobile number is required for registration');
            }

            // Log the registration data being processed (without sensitive info)
            $loggableData = $data;
            if (isset($loggableData['password'])) {
                $loggableData['password'] = '***';
            }
            
            \Log::debug('Starting registration with data', [
                'data_keys' => array_keys($data),
                'has_password' => !empty($data['password']),
                'registration_type' => $data['registration_type'] ?? 'single',
                'mobile' => $data['mobile']
            ]);

            // Check if mobile already exists in user_profiles
            $existingProfile = \App\Models\UserProfile::where('mobile', $data['mobile'])->first();
            if ($existingProfile) {
                $errorMsg = 'Mobile number already registered';
                \Log::warning($errorMsg, [
                    'mobile' => $data['mobile'],
                    'existing_user_id' => $existingProfile->user_id
                ]);
                throw new \Exception($errorMsg);
            }

            // Check if email already exists (if provided)
            if (!empty($data['email'])) {
                $existingUser = User::where('email', $data['email'])->first();
                if ($existingUser) {
                    $errorMsg = 'Email already registered';
                    \Log::warning($errorMsg, [
                        'email' => $data['email'],
                        'existing_user_id' => $existingUser->id
                    ]);
                    throw new \Exception($errorMsg);
                }
            }
            
            // For duo registration, check partner emails don't already exist
            if (($data['registration_type'] ?? '') === 'duo') {
                if (User::where('email', $data['partner1_email'] ?? '')->exists()) {
                    throw new \Exception('Partner 1 email already registered');
                }
                if (User::where('email', $data['partner2_email'] ?? '')->exists()) {
                    throw new \Exception('Partner 2 email already registered');
                }
            }

            // Log the complete registration data
            \Log::debug('Complete registration data', [
                'registration_type' => $data['registration_type'] ?? 'single',
                'has_email' => !empty($data['email']),
                'has_mobile' => !empty($data['mobile']),
                'has_password' => !empty($data['password']),
                'data_keys' => array_keys($data)
            ]);

            // Create user with basic info including avatar and device info
            $userData = [
                'name' => ($data['registration_type'] ?? '') === 'duo' 
                    ? ($data['couple_name'] ?? 'Unknown') 
                    : ($data['name'] ?? 'Unknown'),
                'email' => $data['email'] ?? null,
                'status' => 'active',
                'avatar' => $data['avatar'] ?? null, // Will be updated after user creation
                // Add device information
                'device_token' => $data['device_token'] ?? null,
                'device_type' => $data['device_type'] ?? null,
                'login_type' => 'email', // Default login type for registration
                'last_login_ip' => $data['last_login_ip'] ?? null,
                'last_login_at' => $data['last_login_at'] ?? now(),
                // Add additional profile fields
                'languages' => $data['languages'] ?? null,
                'occupation' => $data['occupation'] ?? null,
				
            ];

            // Create the user first
            $user = User::create($userData);

            if (!$user) {
                throw new \Exception('Failed to create user account');
            }

            // Handle avatar upload if present
            if (isset($data['avatar']) && $data['avatar'] instanceof \Illuminate\Http\UploadedFile) {
                $avatarPath = $this->handleAvatarUpload($data['avatar'], $user->id);
                // Update the user with the avatar path
                $user->update(['avatar' => $avatarPath]);
            }

            // Handle gallery images if present (for both single and duo users)
           $galleryImages = $data['gallery_images'] ?? [];
            
            // Handle multiple file uploads
           

            // Log successful user creation
            \Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mobile' => $data['mobile'] ?? null,
                'has_avatar' => !empty($user->avatar)
            ]);

            // Prepare profile data based on registration type
            $hobbies = [];
            if (!empty($data['hobby'])) {
                if (is_string($data['hobby'])) {
                    // Handle both JSON string and comma-separated string
                    $hobbies = json_decode($data['hobby'], true) ?? array_map('trim', explode(',', $data['hobby']));
                } elseif (is_array($data['hobby'])) {
                    $hobbies = $data['hobby'];
                }
                // Clean up - ensure all values are strings and remove empty/duplicates
                $hobbies = array_values(array_unique(array_filter(array_map('trim', $hobbies))));
            }

            // Handle interests
            $interests = [];
            if (!empty($data['interest'])) {
                if (is_string($data['interest'])) {
                    $interests = json_decode($data['interest'], true) ?? array_map('trim', explode(',', $data['interest']));
                } elseif (is_array($data['interest'])) {
                    $interests = $data['interest'];
                }
                // Clean up - ensure all values are strings and remove empty/duplicates
                $interests = array_values(array_unique(array_filter(array_map('trim', $interests))));
            }

            // Handle languages - ensure it's always an array
            $languages = [];
            if (!empty($data['languages'])) {
                if (is_string($data['languages'])) {
                    $languages = json_decode($data['languages'], true) ?? array_map('trim', explode(',', $data['languages']));
                } elseif (is_array($data['languages'])) {
                    $languages = $data['languages'];
                }
                // Clean up - ensure all values are strings and remove empty/duplicates
                $languages = array_values(array_unique(array_filter(array_map('trim', $languages))));
            }

            // Handle occupation - ensure it's a string or null
            $occupation = null;
            if (isset($data['occupation'])) {
                if (is_array($data['occupation'])) {
                    $occupation = !empty($data['occupation']) ? $data['occupation'][0] : null;
                } else {
                    $occupation = $data['occupation'];
                }
                $occupation = trim($occupation) ?: null;
            }

            // Prepare profile data with proper types (let Eloquent casts handle arrays/JSON)
            $profileData = [
                'name' => $user->name,
                'mobile' => $data['mobile'] ?? null,
                'bio' => $data['bio'] ?? null,
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'location' => $data['location'] ?? null,
				'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                // Store arrays directly; UserProfile::$casts will serialize to JSON
                'interest' => !empty($interests) ? $interests : [],
                'hobby' => !empty($hobbies) ? $hobbies : [],
                'languages' => !empty($languages) ? $languages : [],
                'occupation' => $data['occupation'] ?? null,
                'gallery_images' => $galleryImages,  // ← Stored directly
                'registration_type' => $data['registration_type'] ?? 'single',
                'is_couple' => ($data['registration_type'] ?? '') === 'duo',
				'looking_for' => $data['looking_for'] ?? '',
				'ethnicity' => $data['ethnicity'] ?? '',
				'address' => $data['address'] ?? '',
            ];
			
            // Add partner data for duo registration
            if (($data['registration_type'] ?? '') === 'duo') {
                // Partner 1 data
                $profileData['couple_name'] = $data['couple_name'] ?? null;
                
                $profileData['partner1_name'] = $data['partner1_name'] ?? null;
                $profileData['partner1_email'] = $data['partner1_email'] ?? null;
                $profileData['partner1_mobile'] = $data['partner1_mobile'] ?? null;
                $profileData['partner1_bio'] = $data['partner1_bio'] ?? null;
                $profileData['partner1_gender'] = $data['partner1_gender'] ?? null;
                $profileData['partner1_dob'] = $data['partner1_dob'] ?? null;
                $profileData['partner1_location'] = $data['partner1_location'] ?? null;
                $profileData['partner1_interest'] = !empty($data['partner1_interest']) ? 
                    (is_array($data['partner1_interest']) ? $data['partner1_interest'] : explode(',', $data['partner1_interest'])) : [];
                $profileData['partner1_hobby'] = !empty($data['partner1_hobby']) ? 
                    (is_array($data['partner1_hobby']) ? $data['partner1_hobby'] : explode(',', $data['partner1_hobby'])) : [];
                
                // Partner 2 data
                $profileData['partner2_name'] = $data['partner2_name'] ?? null;
                $profileData['partner2_email'] = $data['partner2_email'] ?? null;
                $profileData['partner2_mobile'] = $data['partner2_mobile'] ?? null;
                $profileData['partner2_bio'] = $data['partner2_bio'] ?? null;
                $profileData['partner2_gender'] = $data['partner2_gender'] ?? null;
                $profileData['partner2_dob'] = $data['partner2_dob'] ?? null;
                $profileData['partner2_location'] = $data['partner2_location'] ?? null;
                $profileData['partner2_interest'] = !empty($data['partner2_interest']) ? 
                    (is_array($data['partner2_interest']) ? $data['partner2_interest'] : explode(',', $data['partner2_interest'])) : [];
                $profileData['partner2_hobby'] = !empty($data['partner2_hobby']) ? 
                    (is_array($data['partner2_hobby']) ? $data['partner2_hobby'] : explode(',', $data['partner2_hobby'])) : [];
                
                // Handle partner photos if provided
                if (isset($data['partner1_photo']) && $data['partner1_photo'] instanceof \Illuminate\Http\UploadedFile) {
                    $profileData['partner1_photo'] = $this->handlePhotoUpload($data['partner1_photo'], 'partner1');
                }
                
                if (isset($data['partner2_photo']) && $data['partner2_photo'] instanceof \Illuminate\Http\UploadedFile) {
                    $profileData['partner2_photo'] = $this->handlePhotoUpload($data['partner2_photo'], 'partner2');
                }
            }

            try {
                // Create or update user profile
                if ($user->profile) {
                    $user->profile()->update($profileData);
                    $profile = $user->profile()->first(); // Get the updated profile
                } else {
                    $profile = $user->profile()->create($profileData);
                }

                if (!$profile) {
                    throw new \Exception('Failed to create/update user profile');
                }


            } catch (\Exception $e) {
                \Log::error('Profile creation/update failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Failed to process user profile: ' . $e->getMessage());
            }

            // Send welcome notification
            try {
                $user->notify(new \App\Notifications\WelcomeNotification($user));
                \Log::info('Welcome notification sent', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the registration if notification fails
            }

            // Load the profile with the user
            $user->load('profile');

            // Log successful registration
            \Log::info('Registration completed successfully', [
                'user_id' => $user->id,
                'has_profile' => !empty($user->profile),
                'registration_type' => $data['registration_type'] ?? 'single'
            ]);

            DB::commit();

            return $user;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error during registration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle photo upload for duo registration
     */
    private function handlePhotoUpload($photo, string $partnerType): ?string
    {
        if (!$photo) {
            return null;
        }

        // Create uploads directory if it doesn't exist
        $uploadPath = storage_path('app/public/uploads/duo_photos');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $filename = $partnerType . '_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
        
        // Store the photo
        $photo->storeAs('public/uploads/duo_photos', $filename);
        
        return 'uploads/duo_photos/' . $filename;
    }

    /**
     * Handle avatar upload
     */
    protected function handleAvatarUpload($avatar, $userId)
    {
        if (!$avatar) {
            return null;
        }

        try {
            // Define the storage path
            $directory = public_path('assets/img/avatars');
            
            // Create directory if it doesn't exist
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Generate a unique filename
            $extension = $avatar->getClientOriginalExtension();
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            
            // Move the file to the target directory
            $avatar->move($directory, $filename);
            
            // Return the relative path
            return 'assets/img/avatars/' . $filename;
            
        } catch (\Exception $e) {
            \Log::error('Avatar upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Handle gallery image upload
     */
    protected function handleGalleryImageUpload($image, $userId)
    {
        if (!$image) {
            return null;
        }

        try {
            // Define the storage path
            $directory = public_path('assets/img/gallery');
            
            // Create directory if it doesn't exist
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Generate a unique filename
            $extension = $image->getClientOriginalExtension();
            $filename = 'gallery_' . $userId . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Move the file to the target directory
            $image->move($directory, $filename);
            
            // Return the relative path
            return 'assets/img/gallery/' . $filename;
            
        } catch (\Exception $e) {
            \Log::error('Gallery image upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Login user with OTP
     * 1. Verifies OTP is valid and marked as 'verified' in OTP table
     * 2. Ensures user status is 'active' in users table
     * 3. Updates last login details
     */
    public function login(array $data): array
    {
        $mobile = $data['mobile'];
        $otp = $data['otp'];

        try {
            // First, check if there's a recent verified OTP for this mobile
            $verifiedOtp = \App\Models\Otp::where('mobile', $mobile)
                ->where('type', 'login')
                ->where('status', 'verified')
                ->where('verified_at', '>=', now()->subMinutes(30)) // OTP verification is valid for 30 minutes
                ->latest('verified_at')
                ->first();

            if ($verifiedOtp) {
                // If we have a recently verified OTP, we can proceed without re-verifying
                \Log::info('Using previously verified OTP', [
                    'mobile' => $mobile,
                    'otp_id' => $verifiedOtp->id,
                    'verified_at' => $verifiedOtp->verified_at
                ]);
            } else {
                // No recent verified OTP found, verify the provided OTP
                $otpResult = $this->otpService->verifyOtp($mobile, $otp, 'login');
                
                if (!$otpResult['valid']) {
                    throw new \Exception($otpResult['message'] ?? 'Invalid or expired OTP');
                }
            }

            // Find user profile by mobile number
            $profile = \App\Models\UserProfile::where('mobile', $mobile)->first();
            
            if (!$profile) {
                throw new \Exception('User profile not found');
            }
            
            // Get the user from the profile
            $user = $profile->user;
            
            if (!$user) {
                throw new \Exception('User account not found');
            }
            
            // Ensure user is active
            if ($user->status !== 'active') {
                throw new \Exception('Your account is not active. Please contact support.');
            }

            // Update device info if provided
            $updateData = [
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
                'status' => 'active' // Mark user as active after successful OTP verification
            ];

            // Add device info if provided
            if (isset($data['device_type'])) {
                $updateData['device_type'] = $data['device_type'];
            }
            if (isset($data['device_token'])) {
                $updateData['device_token'] = $data['device_token'];
            }

            // Update user data
            $user->update($updateData);

            // Generate token
            $token = $user->createToken('auth-token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60
            ];

        } catch (\Exception $e) {
            \Log::error('Login failed', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Reset password with OTP
     */
    public function resetPassword(string $mobile, string $otp, string $password): array
    {
        // Verify OTP
        $otpResult = $this->otpService->verifyOtp($mobile, $otp, 'forgot_password');
        
        $user = $otpResult['user'];
        
        // Update password
        $user->update([
            'password' => Hash::make($password),
            'status' => 'active'
        ]);

        // Generate new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(): bool
    {
        $user = Auth::user();
        
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
            return true;
        }

        return false;
    }
}
