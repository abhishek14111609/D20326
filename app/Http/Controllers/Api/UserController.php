<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UpdateDuoProfileRequest;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\UserService;
use App\Services\DuoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Jobs\SendPushNotificationJob;

/**
 * @OA\Tag(
 *     name="User Management",
 *     description="Endpoints for managing user profiles and account settings"
 * )
 * 
 * @OA\PathItem(
 *     path="/api/v1/user",
 * )
 */
class UserController extends Controller
{
    protected $userService;
    protected $duoService;

    public function __construct(UserService $userService, DuoService $duoService)
    {
        $this->userService = $userService;
        $this->duoService = $duoService;
    }
	
	public function profile(): JsonResponse
    {
        try {
            $user = Auth::user();
            
			$membership = DB::table('user_memberships')->leftjoin('membership_plans','user_memberships.membership_plan_id','membership_plans.id')->where('user_memberships.user_id',$user->id)->select('membership_plans.level')->first();
		
		if($membership != null){
			$is_premium = $membership->level;
		} else {
			$is_premium = '';
		}
			
            return response()->json([
                'status' => 'success',
				'is_premium' => $is_premium,
                'data' => [
                    'user' => new UserResource($user)
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/user/profile",
     *     operationId="getUserProfile",
     *     tags={"User Management"},
     *     summary="Get authenticated user's profile",
     *     description="Retrieves the authenticated user's profile information",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function getuser($id): JsonResponse
{
    try {
        $user = DB::table('users')
            ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->select([
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.email_verified_at',
                'users.password',
                'users.status',
                'users.remember_token',
                'users.login_type',
                'users.device_type',
                'users.device_token',
                'users.last_seen',
                'users.points',
                'users.membership_level',
                'users.total_points',
                'users.reported_count',
                'users.report_count',
                'users.weekly_points',
                'users.monthly_points',
                'users.daily_points',
                'users.last_points_reset_at',
                'users.created_at as user_created_at',
                'users.updated_at as user_updated_at',
                'users.deleted_at as user_deleted_at',
                'user_profiles.id as profile_id',
                'user_profiles.user_id as profile_user_id',
				'user_profiles.name as profile_name',
				'user_profiles.couple_name',

				/* Partner 1 */
				'user_profiles.partner1_name',
				'user_profiles.partner1_mobile',
				'user_profiles.partner1_email',
				'user_profiles.partner1_gender',
				'user_profiles.partner1_dob',
				'user_profiles.partner1_location',
				'user_profiles.partner1_interest',
				'user_profiles.partner1_hobby',
				'user_profiles.partner1_bio',
				'user_profiles.partner2_name',
				'user_profiles.partner2_mobile',
				'user_profiles.partner2_email',
				'user_profiles.partner2_gender',
				'user_profiles.partner2_dob',
				'user_profiles.partner2_location',
				'user_profiles.partner2_interest',
				'user_profiles.partner2_hobby',
				'user_profiles.partner2_bio',
				'user_profiles.registration_type',
				'user_profiles.is_couple',
				'user_profiles.mobile',
				'user_profiles.bio',
				'user_profiles.dob',
				'user_profiles.gender',
				'user_profiles.location',
				'user_profiles.interest',
				'user_profiles.hobby',
				'user_profiles.occupation',
				'user_profiles.languages',
				'user_profiles.relationship_status',
                'user_profiles.created_at as profile_created_at',
                'user_profiles.updated_at as profile_updated_at',
				

                // Use default image if null
                DB::raw("IFNULL(CONCAT('https://duos.webvibeinfotech.in/storage/app/public/avatars/', users.avatar), '') as avatar"),
                DB::raw("IFNULL(CONCAT('https://duos.webvibeinfotech.in/storage/app/public/partner_photos/', user_profiles.partner1_photo), '') as partner1_photo"),
                DB::raw("IFNULL(CONCAT('https://duos.webvibeinfotech.in/storage/app/public/partner_photos/', user_profiles.partner2_photo), '') as partner2_photo"),

                // Gallery images (fixed with full URLs after decoding JSON)
                'user_profiles.gallery_images'
            ])
            ->where('users.id', $id)
            ->first();
			
		
		$membership = DB::table('user_memberships')->leftjoin('membership_plans','user_memberships.membership_plan_id','membership_plans.id')->where('user_memberships.user_id',$id)->select('membership_plans.level')->first();
		
		if($membership != null){
			$is_premium = $membership->level;
		} else {
			$is_premium = '';
		}
		
		
        // Check if gallery_images is not null and is a valid stringified JSON
        if (!empty($user->gallery_images)) {
            $galleryImages = json_decode($user->gallery_images, true); // Decode JSON into an array

            // Check if json_decode succeeded
            if (json_last_error() === JSON_ERROR_NONE && is_array($galleryImages)) {
                // If successful, map full URLs
                $fullGalleryImages = array_map(function ($image) {
                    return "https://duos.webvibeinfotech.in/storage/app/public/gallery_images/{$image}";
                }, $galleryImages);

                // Assign the full URLs back to the user object or output as needed
                $user->gallery_images = $fullGalleryImages;
            } else {
                // Handle case where gallery_images is invalid or empty
                $user->gallery_images = [];
            }
        } else {
            // If gallery_images is empty or null, assign an empty array
            $user->gallery_images = [];
        }
		
		
		$token = DB::table('fcm_tokens')
				->where('user_id', $id)
				->orderBy('id', 'desc') // Order by the id field in descending order (if it's auto-incremented)
				->select('token')
				->first();

		if($token != null){
			$fcm_token = $token->token;
		} else {
			$fcm_token = '';
		}
		
        return response()->json([
            'status' => 'success',
			'is_premium' => $is_premium,
            'data' => [
				'fcm_token' => $fcm_token,
                'user' => $user
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch profile: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * @OA\Put(
     *     path="/api/profile/update",
     *     operationId="updateSingleProfile",
     *     tags={"User"},
     *     summary="Update single user profile",
     *     description="Update the authenticated user's single profile information",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateSingleProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            $validated = $request->validated();
			
            // Update user's basic info if provided
            if (isset($validated['name'])) {
                $user->name = $validated['name'];
                $user->save();
            }

            // Get or create user profile
            $profile = $user->profile ?? $user->profile()->create(['name' => $user->name]);
            
            // Prepare profile data
            $profileData = [];
            $profileFields = [
                'bio',
                'interest',
                'hobby',
                'relationship_status',
                'occupation',
                'languages',
                'location',
				'latitude',
				'longitude',
                'dob',
                'gender',
				'gallery_images',
				'looking_for',
				'ethnicity',
				'address',
            ];

            // Only update the fields that were provided in the request
            foreach ($profileFields as $field) {
                if (array_key_exists($field, $validated)) {
                    $profileData[$field] = $validated[$field];
                }
            }

            // Handle array fields
            if (isset($validated['interest']) && is_array($validated['interest'])) {
                $profileData['interest'] = $validated['interest'];
            }
            
            if (isset($validated['hobby']) && is_array($validated['hobby'])) {
                $profileData['hobby'] = $validated['hobby'];
            }

            // Ensure registration type is set to single
            $profileData['registration_type'] = 'single';
            $profileData['is_couple'] = false;


				// ==============================
				// UPLOAD ONLY NEW AVATAR
				// ==============================
				if ($request->hasFile('avatar')) {

					// 1. Delete old avatar if exists
					if (!empty($user->avatar) && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
						Storage::disk('public')->delete('avatars/' . $user->avatar);
					}

					// 2. Upload new avatar
					$file = $request->file('avatar');
					$avatarName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
					$file->storeAs('avatars', $avatarName, 'public');
					
					// 3. Save in DB
					$user->avatar = $avatarName;
					$user->save();
				}
       		
			// FETCH OLD IMAGES SAFELY
			$oldImages = is_string($profile->gallery_images)
				? json_decode($profile->gallery_images, true)
				: ($profile->gallery_images ?? []);

			if (!is_array($oldImages)) {
				$oldImages = [];
			}

			// DELETE OLD IMAGES
			foreach ($oldImages as $oldImage) {
				$fullPath = "gallery_images/" . $oldImage;
				if (Storage::disk('public')->exists($fullPath)) {
					Storage::disk('public')->delete($fullPath);
				}
			}

			// UPLOAD NEW IMAGES
			$newGallery = [];
			if ($request->hasFile('gallery_images')) {
				
				foreach ($request->file('gallery_images') as $file) {
					if ($file->isValid()) {
						$imageName = time() . '_' . $file->getClientOriginalName();
						$file->storeAs('gallery_images', $imageName, 'public');
						$newGallery[] = $imageName;
					}
				}
				
			}
			$profileData['gallery_images'] = $newGallery;
			// SAVE NEW IMAGES (NO JSON ENCODE)
			
			$languages = [];
            if (!empty($validated['languages'])) {
                if (is_string($validated['languages'])) {
                    $languages = json_decode($validated['languages'], true) 
                                 ?? array_map('trim', explode(',', $validated['languages']));
                } elseif (is_array($validated['languages'])) {
                    $languages = $validated['languages'];
                }
                $languages = array_values(array_unique(array_filter(array_map('trim', $languages))));
            }
			
			$profileData['languages'] = $languages;
            // Update the profile
            $profile->update($profileData);
            
			
if (!empty($profileData)) {

    try {
        Log::info('Attempting to create notification', [
            'user_id' => $user->id,
            'updated_fields' => $profileData
        ]);

        // 1️⃣ Create DB Notification
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => Notification::TYPE_SYSTEM,
            'message' => 'Your profile was updated successfully on ' . now()->format('M d, Y \a\t h:i A'),
            'data' => [
                'updated_fields' => $profileData,
                'updated_at' => now()->toDateTimeString(),
                'notification_type' => 'profile_updated'
            ]
        ]);

        Log::info('Notification created successfully', [
            'notification_id' => $notification->id,
            'user_id' => $user->id
        ]);

        // 2️⃣ Send Push Notification (CLEAN METHOD)
        $this->sendDirectFcmNotification($user, $notification);

    } catch (\Exception $e) {

        Log::error('Failed to create profile update notification', [
            'user_id' => $user->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

			
			
            DB::commit();

            // Refresh the user to get updated relationships
            $user->refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => new UserResource($user)
            ]);

        } catch (\Exception $e) {

			DB::rollBack();

			return response()->json([
				'status' => 'error',
				'message' => $e->getMessage(),
				'line' => $e->getLine(),
				'file' => $e->getFile(),
			], 500);
		}
    }

	
protected function sendDirectFcmNotification(User $user, Notification $notification)
{
    try {

        $fcmToken = $user->fcm_token ?? $user->fcmTokens()->latest()->first()?->token;

        if (!$fcmToken) {
            Log::info('No FCM token found for profile update notification', [
                'user_id' => $user->id
            ]);
            return;
        }

        $fcmService = app(\App\Services\FcmService::class);

        // -----------------------------
        //  FIXED FCM v1 PAYLOAD
        // -----------------------------
        $payload = [
            "message" => [
                "token" => $fcmToken,

                "notification" => [
                    "title" => "Profile Updated",
                    "body"  => "Your profile has been updated successfully!"
                ],

                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "sound" => "default"
                    ],
                ],

                "apns" => [
                    "headers" => [
                        "apns-priority" => "10"
                    ],
                    "payload" => [
                        "aps" => [
                            "sound" => "default"
                        ]
                    ]
                ],

                "data" => [
                    "type" => "profile_updated",
                    "notification_id" => (string)$notification->id,
                    "updated_fields" => json_encode($notification->data['updated_fields'] ?? []),
                    "updated_at" => $notification->created_at->toDateTimeString(),
                    "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                ]
            ]
        ];
		
        // Correct V1 call
        $fcmService->sendNotification($fcmToken, "Profile Updated", "Your profile has been updated!", $payload["message"]["data"]);
		
        Log::info('Direct FCM sent for profile update', [
            'user_id' => $user->id,
            'fcm_token' => substr($fcmToken, 0, 12) . '...',
            'notification_id' => $notification->id
        ]);

    } catch (\Exception $e) {
        Log::error('Direct FCM failed', [
            'user_id' => $user->id,
            'error' => $e->getMessage()
        ]);
    }
}



	
	
    /**
     * @OA\Put(
     *     path="/api/duo-profile/update",
     *     operationId="updateDuoProfile",
     *     tags={"User"},
     *     summary="Update duo user profile",
     *     description="Update the authenticated user's duo profile information",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateDuoProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Duo profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Duo profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateDuoProfile(Request $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            $data = $request->all();
           
            // Log the raw input data
			
			// ==============================
				// UPLOAD ONLY NEW AVATAR
				// ==============================
			if ($request->hasFile('avatar')) {
				// Delete old avatar if exists
				if (!empty($user->avatar) && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
					Storage::disk('public')->delete('avatars/' . $user->avatar);
				}

				// Upload new avatar
				$file = $request->file('avatar');
				$avatarName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
				$file->storeAs('avatars', $avatarName, 'public');

				// Save in users table
				$user->avatar = $avatarName;
				$user->save();
			}
           
            // Handle file uploads for gallery images
			// ------------------------
			// REPLACE GALLERY IMAGES
			// ------------------------
			
			$newGallery = [];
			if ($request->hasFile('gallery_images')) {
				
				$galleryDir = 'gallery_images'; // Directly in storage/app/public/gallery_images

				// Create folder if not exists
				if (!Storage::disk('public')->exists($galleryDir)) {
					Storage::disk('public')->makeDirectory($galleryDir, 0755, true);
				}

				// Delete old images from storage
				$oldImages = $user->profile->gallery_images ?? [];

				if (is_string($oldImages)) {
					$oldImages = json_decode($oldImages, true) ?? [];
				}

				foreach ($oldImages as $old) {
					$oldPath = $galleryDir . '/' . $old;

					if (Storage::disk('public')->exists($oldPath)) {
						Storage::disk('public')->delete($oldPath);
					}
				}

				// Upload new images
				$files = $request->file('gallery_images');
				if (!is_array($files)) $files = [$files];

				foreach ($files as $file) {
					if ($file->isValid()) {
						$filename = uniqid() . '.' . $file->getClientOriginalExtension();
						$file->storeAs($galleryDir, $filename, 'public');

						$newGallery[] = $filename; // store only filename
					}
				}
				
			}
			
			// Save only new gallery images in DB
			$data['gallery_images'] = $newGallery;

            
            
            // Handle nested partner data if present
            if (isset($data['partner1']) && is_array($data['partner1'])) {
                foreach ($data['partner1'] as $key => $value) {
                    $data['partner1_' . $key] = $value;
                }
                unset($data['partner1']);
            }
            
            if (isset($data['partner2']) && is_array($data['partner2'])) {
                foreach ($data['partner2'] as $key => $value) {
                    $data['partner2_' . $key] = $value;
                }
                unset($data['partner2']);
            }
            
            // Update user's name if provided
            if (isset($data['couple_name'])) {
                $user->update(['name' => $data['couple_name']]);
                \Log::info('Updated user name to: ' . $user->name);
            }
            
			 // Handle partner1 photo upload
            if ($request->hasFile('partner1_photo')) {
                $partner1Photo = $request->file('partner1_photo');
                if ($partner1Photo->isValid()) {
                    // Delete old photo if exists
                    if ($data['partner1_photo'] && Storage::disk('public')->exists($data['partner1_photo'])) {
                        Storage::disk('public')->delete($profile->partner1_photo);
                    }
                    // Store new photo
                    $partner1PhotoPath = $partner1Photo->store('partner_photos', 'public');
                    $profileData['partner1_photo'] = $partner1PhotoPath;
                }
            }

            // Handle partner2 photo upload
            if ($request->hasFile('partner2_photo')) {
                $partner2Photo = $request->file('partner2_photo');
                if ($partner2Photo->isValid()) {
                    // Delete old photo if exists
                    if ($data['partner2_photo'] && Storage::disk('public')->exists($data['partner2_photo'])) {
                        Storage::disk('public')->delete($data->partner2_photo);
                    }
                    // Store new photo
                    $partner2PhotoPath = $partner2Photo->store('partner_photos', 'public');
                    $profileData['partner2_photo'] = $partner2PhotoPath;
                }
            }
			
			
            // Prepare profile data
            $profileData = [
                'is_couple' => true,
                'registration_type' => 'duo',
				'avatar' => $data['avatar'] ?? null,
                // 'couple_name' => $data['couple_name'] ?? $user->name,
                // 'bio' => $data['bio'] ?? null,
                'relationship_status' => $data['relationship_status'] ?? null,
                'languages' => $data['languages'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                // 'location' => $data['location'] ?? null,
                // 'interest' => $data['interest'] ?? [],
                // 'hobby' => $data['hobby'] ?? [],
                'gallery_images' => $data['gallery_images'] ?? ($user->profile->gallery_images ?? []),
                
                // Partner 1 information
                'partner1_name' => $data['partner1_name'] ?? null,
                'partner1_email' => $data['partner1_email'] ?? null,
                'partner1_gender' => $data['partner1_gender'] ?? null,
                'partner1_dob' => $data['partner1_dob'] ?? null,
                'partner1_location' => $data['partner1_location'] ?? null,
                'partner1_interest' => $data['partner1_interest'] ?? [],
                'partner1_hobby' => $data['partner1_hobby'] ?? [],
                'partner1_bio' => $data['partner1_bio'] ?? null,
                'partner1_mobile' => $data['partner1_mobile'] ?? null,
                //'partner1_photo' => $data['partner1_photo'] ?? null,
                
                // Partner 2 information
                'partner2_name' => $data['partner2_name'] ?? null,
                'partner2_email' => $data['partner2_email'] ?? null,
                'partner2_gender' => $data['partner2_gender'] ?? null,
                'partner2_dob' => $data['partner2_dob'] ?? null,
                'partner2_location' => $data['partner2_location'] ?? null,
                'partner2_interest' => $data['partner2_interest'] ?? [],
                'partner2_hobby' => $data['partner2_hobby'] ?? [],
                'partner2_bio' => $data['partner2_bio'] ?? null,
                'partner2_mobile' => $data['partner2_mobile'] ?? null,
                //'partner2_photo' => $data['partner2_photo'] ?? null,
                'occupation' => $data['occupation'] ?? null,
                'relationship_status' => $data['relationship_status'] ?? null,
                'relationship_type' => $data['relationship_type'] ?? null,
				'looking_for' => $data['looking_for'] ?? null,
				'ethnicity' => $data['ethnicity'] ?? null,
				'address' => $data['address'] ?? null,
				'latitude' => $data['latitude'] ?? null,
				'longitude' => $data['longitude'] ?? null,
            ];
            
            // Log the profile data before saving
            \Log::info('=== PROFILE DATA BEFORE SAVING ===');
            \Log::info($profileData);
            
            // Update or create the profile
            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );
            
            DB::commit();
            
            // Refresh the user to get the latest data
            $user->refresh()->load('profile');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Duo profile updated successfully',
                'data' => new UserResource($user)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating duo profile: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update duo profile. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/user/avatar",
     *     operationId="uploadUserAvatar",
     *     tags={"User Management"},
     *     summary="Upload user avatar",
     *     description="Uploads a new avatar image for the authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar"},
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="Image file (jpeg, png, jpg, gif, max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avatar uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Avatar uploaded successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid file or upload failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
public function uploadAvatar(Request $request): JsonResponse
{
    $request->validate([
        'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    try {
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = $this->userService->uploadAvatar($authUser, $request->file('avatar'));

        return response()->json([
            'status' => 'success',
            'message' => 'Avatar uploaded successfully',
            'data' => [
                'user' => new UserResource($user)
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Avatar Upload Failed: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong while uploading avatar'
        ], 500);
    }
}

    /**
     * @OA\Delete(
     *     path="/user/account",
     *     operationId="deleteUserAccount",
     *     tags={"User Management"},
     *     summary="Delete user account",
     *     description="Permanently deletes the authenticated user's account and all associated data",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "confirmation"},
     *             @OA\Property(property="password", type="string", format="password", example="current_password_123"),
     *             @OA\Property(property="confirmation", type="string", example="DELETE_MY_ACCOUNT")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Account deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid password or operation failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
public function deleteAccount(Request $request): JsonResponse
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        DB::transaction(function () use ($user) {

            // 🔥 Delete Sanctum / Passport tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // 🔥 Delete profile (if exists)
            if ($user->profile) {
                $user->profile()->delete();
            }

            // 🔥 Example: other related tables (optional)
            // $user->wallet()->delete();
            // $user->quizParticipants()->delete();
            // $user->competitionParticipants()->delete();

            // 🔥 HARD DELETE USER
            $user->forceDelete(); // 💀 permanently gone
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Account permanently deleted'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

    /**
     * @OA\Post(
     *     path="/user/disable-profile",
     *     operationId="disableUserProfile",
     *     tags={"User Management"},
     *     summary="Disable user profile",
     *     description="Disables the user's profile by setting status to 'disabled'. User data is preserved and can be reactivated.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password"},
     *             @OA\Property(property="password", type="string", format="password", example="current_password_123"),
     *             @OA\Property(property="reason", type="string", maxLength=500, example="Taking a break from the platform")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile disabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile disabled successfully. You can reactivate it by logging in again."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="disabled_at", type="string", format="date-time"),
     *                 @OA\Property(property="reason", type="string", nullable=true),
     *                 @OA\Property(property="status", type="string", example="disabled"),
     *                 @OA\Property(property="tokens_preserved", type="boolean", example=true),
     *                 @OA\Property(property="can_reactivate", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid password or operation failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
public function deleteProfile(Request $request): JsonResponse
{
    try {
        $user = Auth::user();

        // User check
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Not Found'
            ], 400);
        }

        // Status disabled
        $user->status = 'disabled';
        $user->save();

        // Soft delete (deleted_at auto set)
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted successfully',
            'data' => [
                'deleted_at' => $user->deleted_at,
                'status' => 'disabled',
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

	

    /**
     * @OA\Post(
     *     path="/user/reactivate-profile",
     *     operationId="reactivateUserProfile",
     *     tags={"User Management"},
     *     summary="Reactivate disabled profile",
     *     description="Reactivates a previously disabled user profile",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="user_password_123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile reactivated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Your profile has been reactivated"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid credentials or account not disabled",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found or not disabled",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
   public function reactivateProfile(Request $request): JsonResponse
{
    $request->validate([
        'user' => 'required|string'
    ]);

    try {
        $username = $request->user;

        // Find disabled user by email OR phone OR username
        $user = User::onlyTrashed()
				->where(function ($query) use ($username) {
					$query->where('email', $username)
						  ->orWhereHas('profile', function ($q) use ($username) {
							  $q->where('mobile', $username);
						  });
				})
				->first();


        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Disabled user not found'
            ], 404);
        }

       
        // Reactivate user
        $user->update([
            'status' => 'active',
			'deleted_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile reactivated successfully',
            'data' => [
                'user' => new UserResource($user),
                'reactivated_at' => now()->toISOString(),
                'status' => 'active',
                'tokens_preserved' => true
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}

    /**
     * Get duo partner information
     */
    public function getPartner(): JsonResponse
    {
        try {
            $partner = $this->duoService->getPartner(Auth::user());
            
            if (!$partner) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No partner found',
                    'data' => [
                        'partner' => null,
                        'has_partner' => false
                    ]
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'partner' => new UserResource($partner),
                    'has_partner' => true
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invite partner for duo account
     */
    public function invitePartner(Request $request): JsonResponse
    {
        $request->validate([
            'partner_mobile' => 'required|string',
            'partner_name' => 'required|string|max:255'
        ]);

        try {
            $result = $this->duoService->invitePartner(
                Auth::user(),
                $request->partner_mobile,
                $request->partner_name
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Partner invitation sent successfully',
                'data' => [
                    'invitation_id' => $result['invitation_id'],
                    'partner_mobile' => $result['masked_mobile']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Accept partner invitation
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        $request->validate([
            'invitation_code' => 'required|string'
        ]);

        try {
            $result = $this->duoService->acceptInvitation(Auth::user(), $request->invitation_code);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Partner invitation accepted successfully',
                'data' => [
                    'partner' => new UserResource($result['partner']),
                    'duo_status' => 'active'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove partner from duo account
     */
    public function removePartner(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
            'confirmation' => 'required|string|in:REMOVE_PARTNER'
        ]);

        try {
            $this->duoService->removePartner(Auth::user(), $request->password);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Partner removed successfully',
                'data' => [
                    'duo_status' => 'single'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/profile",
     *     operationId="getProfile",
     *     tags={"User"},
     *     summary="Get authenticated user's profile",
     *     description="Returns the authenticated user's profile information",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profile retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user()->load(['profile' => function($query) {
                $query->withTrashed() // Include soft-deleted profiles if any
                    ->select([
                        'id', 'user_id', 'name', 'mobile', 'bio', 'dob', 'gender', 'location', 
                        'interest', 'hobby', 'gallery_images', 'couple_name', 'partner1_name',
                        'partner1_email', 'partner1_photo', 'partner2_name', 'partner2_email',
                        'partner2_photo', 'registration_type', 'is_couple', 'created_at', 'updated_at'
                    ]);
            }]);
            
            // Ensure profile exists, if not create a default one
            if (!$user->profile) {
                $user->profile()->create([
                    'bio' => null,
                    'interest' => null,
                    'hobby' => null,
                    'relationship_status' => null,
                    'occupation' => null,
                    'languages' => null,
                    'location' => null,
                    'dob' => null,
                    'gender' => null,
                    'registration_type' => 'single',
                    'is_couple' => false
                ]);
                $user->refresh();
            }

            return response()->json([
                'status' => 'success',
                'data' => new UserResource($user)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getProfile: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch profile',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/profile/update",
     *     operationId="updateProfile",
     *     tags={"User"},
     *     summary="Update user profile",
     *     description="Update the authenticated user's profile information",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    /**
     * @deprecated Use updateSingleProfile or updateDuoProfile instead
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->updateSingleProfile($request);
    }
}