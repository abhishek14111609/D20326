<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\DuoRegisterRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and account management"
 * )
 * 
 * @OA\PathItem(
 *     path="/api/v1/auth",
 * )
 */
class AuthController extends Controller
{
    protected $authService;
    protected $otpService;

    public function __construct(AuthService $authService, OtpService $otpService)
    {
        $this->authService = $authService;
        $this->otpService = $otpService;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     operationId="registerUser",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Register a new user with email, password and mobile number. An OTP will be sent to verify the mobile number.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterData")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent to your mobile number. Please verify to complete registration."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="requires_otp_verification", type="boolean", example=true),
     *                 @OA\Property(property="otp_sent_to", type="string", example="+91******1234"),
     *                 @OA\Property(property="registration_type", type="string", example="single")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
		
        try {
			
			$request->validate([
                'fcm_token' => 'required|string'
            ]);
            
			
            $registrationData = $request->validated();
            $registrationData['registration_type'] = 'single';
            
            // -------- Avatar --------
			$avatarPath = null;
			if ($request->hasFile('avatar')) {
				$avatarFile = $request->file('avatar');
				$avatarName = time() . '_' . $avatarFile->getClientOriginalName();
				$avatarFile->storeAs('avatars', $avatarName, 'public'); // folder ma save
				$avatarPath = '' . $avatarName; // DB ma save
			}

			// -------- Gallery --------
			$galleryImageNames = [];

			if ($request->hasFile('gallery_images')) {
				foreach ($request->file('gallery_images') as $image) {

					$originalName = $image->getClientOriginalName();

					// space before ( remove
					$cleanName = preg_replace('/\s+\(/', '(', $originalName);

					// unique filename
					$galleryName = time() . '_' . uniqid() . '_' . $cleanName;

					$image->storeAs('gallery_images', $galleryName, 'public');

					$galleryImageNames[] = $galleryName;
				}
			}

            // Handle languages and occupation
            $languages = [];
            if (!empty($registrationData['languages'])) {
                if (is_string($registrationData['languages'])) {
                    $languages = json_decode($registrationData['languages'], true) 
                                 ?? array_map('trim', explode(',', $registrationData['languages']));
                } elseif (is_array($registrationData['languages'])) {
                    $languages = $registrationData['languages'];
                }
                $languages = array_values(array_unique(array_filter(array_map('trim', $languages))));
            }
    
            $occupation = $registrationData['occupation'] ?? null;
            if (is_array($occupation)) {
                $occupation = !empty($occupation) ? $occupation[0] : null;
            }
            $occupation = trim((string) $occupation) ?: null;
    
            // Complete registration
            $user = $this->authService->completeRegistration([
                'name' => $registrationData['name'],
                'email' => $registrationData['email'] ?? null,
                'mobile' => $registrationData['mobile'],  // Ensure mobile is included
                'registration_type' => 'single',
                'gender' => $registrationData['gender'] ?? 'other',
                'bio' => $registrationData['bio'] ?? null,
                'dob' => $registrationData['dob'] ?? null,
                'location' => $registrationData['location'] ?? null,
                'latitude' => $registrationData['latitude'] ?? null,
                'longitude' => $registrationData['longitude'] ?? null,
                'interest' => $registrationData['interest'] ?? [],
                'hobby' => $registrationData['hobby'] ?? [],
                'languages' => $languages,
                'occupation' => $occupation,
                'avatar' => $avatarPath,
				'gallery_images' => json_encode($galleryImageNames),
				'looking_for' => $registrationData['looking_for'] ?? null,
				'ethnicity' => $registrationData['ethnicity'] ?? null,
				'address' => $registrationData['address'] ?? null,
                'status' => 'active',
                'device_token' => $registrationData['device_token'] ?? null,
                'device_type' => $registrationData['device_type'] ?? null,
                'login_type' => 'email',
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
				
                // Add these fields to ensure they're saved to the profile
                ]);
    		
            // Generate token
            $deviceName = $registrationData['device_name'] ?? 'default_device_' . uniqid();
            $tokenData = [
                'login_type' => 'email',
                'device_name' => $deviceName,
                'ip_address' => $request->ip(),
            ];
            if (!empty($registrationData['device_token'])) $tokenData['device_token'] = $registrationData['device_token'];
            if (!empty($registrationData['device_type'])) $tokenData['device_type'] = $registrationData['device_type'];
    
            $token = $user->createToken($deviceName, $tokenData)->plainTextToken;
			
    		$fcmToken = $request->fcm_token;

            // Check if FCM token is provided in request
            $fcm_token = \App\Models\FcmToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => $fcmToken]
            );
			
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Welcome, ' . $registrationData['name'] . '! Your account is ready to explore',
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    

    /**
     * @OA\Post(
     *     path="/auth/duo-register",
     *     operationId="registerDuo",
     *     tags={"Authentication"},
     *     summary="Register a new couple (duo registration)",
     *     description="Register a new couple with partner details. An OTP will be sent to verify the mobile number.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/DuoRegisterData")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP sent to your mobile number. Please verify to complete duo registration."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="requires_otp_verification", type="boolean", example=true),
     *                 @OA\Property(property="otp_sent_to", type="string", example="+91******1234"),
     *                 @OA\Property(property="registration_type", type="string", example="duo"),
     *                 @OA\Property(property="couple_name", type="string", example="John & Jane")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or error during registration",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function duoRegister(DuoRegisterRequest $request): JsonResponse
{
    try {
		$request->validate([
			'fcm_token' => 'required|string'
		]);
		
		
        $registrationData = $request->validated();

        // Handle gallery images
        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->getClientOriginalName(): null;
    
            // Handle gallery images
            $galleryImageNames = [];

			if ($request->hasFile('gallery_images')) {
				foreach ($request->file('gallery_images') as $image) {

					$originalName = $image->getClientOriginalName();

					// space before ( remove
					$cleanName = preg_replace('/\s+\(/', '(', $originalName);

					// unique filename
					$galleryName = time() . '_' . uniqid() . '_' . $cleanName;

					$image->storeAs('gallery_images', $galleryName, 'public');

					$galleryImageNames[] = $galleryName;
				}
			}

        // Handle occupation
        $occupation = $registrationData['occupation'] ?? null;
        if (is_array($occupation)) $occupation = !empty($occupation) ? $occupation[0] : null;
        $occupation = trim((string) $occupation) ?: null;

        // Handle languages
        $languages = [];
        if (!empty($registrationData['languages'])) {
            if (is_string($registrationData['languages'])) {
                $languages = json_decode($registrationData['languages'], true) 
                             ?? array_map('trim', explode(',', $registrationData['languages']));
            } elseif (is_array($registrationData['languages'])) {
                $languages = $registrationData['languages'];
            }
            $languages = array_values(array_unique(array_filter(array_map('trim', $languages))));
        }

        // Main user (partner1) data
        $userData = [
            'name' => $registrationData['partner1_name'],
            'email' => $registrationData['partner1_email'],
            'mobile' => $registrationData['partner1_mobile'],
            'gender' => $registrationData['partner1_gender'],
            'registration_type' => 'duo',
            'is_couple' => true,
            'status' => 'active',
            'avatar' => $avatarPath,
            'gallery_images' => $galleryImageNames,
            'device_token' => $registrationData['device_token'] ?? null,
            'device_type' => $registrationData['device_type'] ?? null,
            'login_type' => 'email',
            'last_login_ip' => $request->ip(),
            'last_login_at' => now(),
            'languages' => $languages,
            'occupation' => $occupation,

            // Profile fields
            'bio' => $registrationData['partner1_bio'] ?? null,
            'dob' => $registrationData['partner1_dob'],
            'location' => $registrationData['partner1_location'] ?? null,
			'latitude' => $registrationData['latitude'] ?? null,
			'longitude' => $registrationData['longitude'] ?? null,
            'interest' => $registrationData['partner1_interest'] ?? [],
            'hobby' => $registrationData['partner1_hobby'] ?? [],

            // Partner1 details
            'partner1_name' => $registrationData['partner1_name'],
            'partner1_email' => $registrationData['partner1_email'],
            'partner1_mobile' => $registrationData['partner1_mobile'],
            'partner1_gender' => $registrationData['partner1_gender'],
            'partner1_dob' => $registrationData['partner1_dob'],
            'partner1_bio' => $registrationData['partner1_bio'] ?? null,
            'partner1_location' => $registrationData['partner1_location'] ?? null,
            'partner1_interest' => $registrationData['partner1_interest'] ?? [],
            'partner1_hobby' => $registrationData['partner1_hobby'] ?? [],
            'partner1_photo' => $request->hasFile('partner1_photo') ? $request->file('partner1_photo') : null,

            // Partner2 details
            'couple_name' => $registrationData['couple_name'],
            'partner2_name' => $registrationData['partner2_name'],
            'partner2_email' => $registrationData['partner2_email'],
            'partner2_gender' => $registrationData['partner2_gender'],
            'partner2_mobile' => $registrationData['partner2_mobile'],
            'partner2_dob' => $registrationData['partner2_dob'],
            'partner2_bio' => $registrationData['partner2_bio'] ?? null,
            'partner2_location' => $registrationData['partner2_location'] ?? null,
            'partner2_interest' => $registrationData['partner2_interest'] ?? [],
            'partner2_hobby' => $registrationData['partner2_hobby'] ?? [],
            'partner2_photo' => $request->hasFile('partner2_photo') ? $request->file('partner2_photo') : null,
			'looking_for' => $registrationData['looking_for'] ?? null,
			'ethnicity' => $registrationData['ethnicity'] ?? null,
			'address' => $registrationData['address'] ?? null,
        ];
		
        // Complete registration
        $user = $this->authService->completeRegistration($userData);
		$fcmToken = $request->fcm_token;

		// Check if FCM token is provided in request
		$fcm_token = \App\Models\FcmToken::updateOrCreate(
			['user_id' => $user->id],
			['token' => $fcmToken]
		);
        // Check if user was created successfully
        if (!$user) {
            throw new \Exception('Failed to create user account. Please try again.');
        }

        // Generate token
        $deviceName = $registrationData['device_name'] ?? 'default_device_' . uniqid();
        $tokenData = [
            'login_type' => 'email',
            'device_name' => $deviceName,
            'ip_address' => $request->ip(),
        ];
        if (!empty($registrationData['device_token'])) $tokenData['device_token'] = $registrationData['device_token'];
        if (!empty($registrationData['device_type'])) $tokenData['device_type'] = $registrationData['device_type'];

        try {
            $token = $user->createToken($deviceName, $tokenData)->plainTextToken;
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate authentication token: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Duo registration successful',
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}


    /**
     * @OA\Post(
     *     path="/auth/login",
     *     operationId="loginUser",
     *     tags={"Authentication"},
     *     summary="Login user with email/mobile and password",
     *     description="Authenticate a user using email or mobile number and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user", "password", "device_name"},
     *             @OA\Property(property="user", type="string", example="user@example.com OR +1234567890"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="device_name", type="string", example="iPhone 12"),
     *             @OA\Property(property="device_token", type="string", example="device_token_here"),
     *             @OA\Property(property="device_type", type="string", enum={"android", "ios", "web"}, example="ios")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginInput = $request->input('user');

            // Determine if input is email or mobile
            $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

            $user = null;
            $loginType = 'email'; // Default login type

            if ($field === 'email') {
				// Email thi user search
				$user = \App\Models\User::withTrashed()   // 🔥 Soft deleted users pan fetch thase
					->where('email', $loginInput)
					->first();
			} else {
				// Mobile thi user search
				$user = \App\Models\User::withTrashed()   // 🔥 Soft deleted users pan fetch karo
					->whereHas('profile', function ($q) use ($loginInput) {
						$q->where('mobile', $loginInput);
					})
					->first();
			}

            // 🔍 CASE 1: User found but deleted
			// User found but soft-deleted
			if ($user && $user->deleted_at !== null) {
				return response()->json([
					'status' => 'error',
					'message' => 'This user deleted. Re-register yourself.'
				], 403);
			}

			// 🔍 CASE 2: User not found
			if (!$user) {
				return response()->json([
					'status' => 'error',
					'message' => 'User not found with provided credentials'
				], 404);
			}

			// 🔍 CASE 3: User found but inactive
			// if ($user->status !== 'active') {//
				//return response()->json([
					//'status' => 'error',
				//	'message' => 'Your account is inactive'
				//], 403);
			//} //

            // Prepare device info
            $deviceName = $request->device_name ?? 'web';
            $tokenData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_used_at' => now(),
            ];
            if ($request->has('device_token')) $tokenData['device_token'] = $request->device_token;
            if ($request->has('device_type')) $tokenData['device_type'] = $request->device_type;

            // Create token
            $token = $user->createToken($deviceName, $tokenData)->plainTextToken;

            // Update last login info and login type
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'login_type' => $loginType,
            ]);
			

            // If login type is mobile, also update the device token and type if provided
            if ($loginType === 'mobile') {
                $updateData = [];
                if ($request->has('device_token')) $updateData['device_token'] = $request->device_token;
                if ($request->has('device_type')) $updateData['device_type'] = $request->device_type;
                
                if (!empty($updateData)) {
                    $user->update($updateData);
                }
            }
			
			$request->validate([
                'fcm_token' => 'required|string'
            ]);
            $fcmToken = $request->fcm_token;

            // Check if FCM token is provided in request
            $fcm_token = \App\Models\FcmToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => $fcmToken]
            );
	
			$membership = DB::table('user_memberships')->leftjoin('membership_plans','user_memberships.membership_plan_id','membership_plans.id')->where('user_memberships.user_id',$user->id)->select('membership_plans.level')->first();
		
		if($membership != null){
			$is_premium = $membership->level;
		} else {
			$is_premium = '';
		}

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
				'is_premium' => $is_premium,
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(), // Shows real error for debugging
            ], 500);
        }
    }


    /**
     * @OA\Post(
     *     path="/auth/verify-otp",
     *     operationId="verifyOtp",
     *     tags={"Authentication"},
     *     summary="Verify OTP",
     *     description="Verify OTP sent to user's mobile number for registration or password reset",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "otp", "type"},
     *             @OA\Property(property="mobile", type="string", example="+1234567890"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="type", type="string", enum={"register", "reset"}, example="register")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=31536000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid OTP or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $type = $request->type ?? 'login';
            $mobile = $request->mobile;
            
            // Log the incoming request with more context
            \Log::debug('OTP Verification Request', [
                'mobile' => $mobile,
                'type' => $type,
                'otp' => $request->otp,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // First verify the OTP
            try {
                $otpResult = $this->otpService->verifyOtp(
                    $mobile,
                    $request->otp,
                    $type
                );
                
                // Log the OTP verification result
                \Log::debug('OTP Verification Result', [
                    'mobile' => $mobile,
                    'type' => $type,
                    'success' => $otpResult['success'] ?? false,
                    'otp_id' => $otpResult['otp_id'] ?? null,
                    'has_data' => !empty($otpResult['data']),
                    'data_keys' => !empty($otpResult['data']) && is_array($otpResult['data']) 
                        ? array_keys($otpResult['data']) 
                        : []
                ]);
                
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $errorCode = 400;
                
                // Provide more specific error messages
                if (str_contains(strtolower($errorMessage), 'invalid') || 
                    str_contains(strtolower($errorMessage), 'expired')) {
                    $errorCode = 401;
                } elseif (str_contains(strtolower($errorMessage), 'too many')) {
                    $errorCode = 429; // Too Many Requests
                }
                
                \Log::error('OTP Verification Failed', [
                    'mobile' => $mobile,
                    'type' => $type,
                    'error' => $errorMessage,
                    'code' => $errorCode,
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage
                ], $errorCode);
            }
            
            // After OTP verification, handle the response based on type
            if ($type === 'register') {
                // Get the registration data from the OTP result
                $registrationData = $otpResult['data'] ?? [];
                
                // Log the initial registration data from OTP result
                \Log::debug('Initial registration data from OTP result', [
                    'has_data' => !empty($registrationData),
                    'data_keys' => is_array($registrationData) ? array_keys($registrationData) : [],
                    'data_type' => gettype($registrationData)
                ]);
                
                // If no data in result, try to get it from the OTP record
                if (empty($registrationData)) {
                    $registrationData = $this->otpService->getOtpData($mobile, 'register') ?? [];
                    \Log::debug('Fetched registration data from OTP record', [
                        'has_data' => !empty($registrationData),
                        'data_keys' => is_array($registrationData) ? array_keys($registrationData) : []
                    ]);
                }
                
                // Ensure we have the minimum required data
                if (empty($registrationData) || !is_array($registrationData)) {
                    $registrationData = [];
                }
                
                // Always ensure mobile is set
                if (empty($registrationData['mobile']) && !empty($mobile)) {
                    $registrationData['mobile'] = $mobile;
                }
                
                // Ensure mobile number is included in registration data
                if (!isset($registrationData['mobile'])) {
                    $registrationData['mobile'] = $request->mobile;
                }
                
                // Log the registration data for debugging
                \Log::debug('Registration data found', [
                    'mobile' => $request->mobile,
                    'data_keys' => array_keys($registrationData),
                    'has_password' => !empty($registrationData['password'])
                ]);
                
                try {
                    // Log the registration data being passed to completeRegistration
                    \Log::debug('Calling completeRegistration with data', [
                        'mobile' => $registrationData['mobile'] ?? null,
                        'has_email' => !empty($registrationData['email']),
                        'registration_type' => $registrationData['registration_type'] ?? 'single'
                    ]);
                    
                    // Complete the registration process
                    $user = $this->authService->completeRegistration($registrationData);
                    
                    if (!$user) {
                        throw new \Exception('Failed to complete registration. User not created.');
                    }
                    
                    // Log successful user creation before token generation
                    \Log::info('User created successfully, generating token', [
                        'user_id' => $user->id,
                        'mobile' => $request->mobile
                    ]);
                    
                    // Generate token for the new user using Sanctum
                    $tokenResult = $user->createToken('auth-token');
                    $token = $tokenResult->plainTextToken;
                    $accessToken = $tokenResult->accessToken;
                    
                    if (empty($token) || !$accessToken) {
                        throw new \Exception('Failed to generate authentication token');
                    }
                    
                    // Store token in our custom tokens table
                    try {
                        // Start a database transaction
                        \DB::beginTransaction();
                        
                        // Log the token data we're about to store
                        \Log::debug('Preparing to store token in custom tokens table', [
                            'user_id' => $user->id,
                            'token_exists' => !empty($token),
                            'access_token_exists' => !empty($accessToken)
                        ]);

                        $deviceType = $request->header('User-Agent');
                        $deviceId = $request->header('X-Device-Id');
                        
                        // Prepare token data with a default expiration
                        $expiresAt = $accessToken->expires_at ?? now()->addDays(30);
                        
                        // Ensure expires_at is a valid datetime
                        if (empty($expiresAt)) {
                            $expiresAt = now()->addDays(30);
                            \Log::debug('Using default expiration for token', ['expires_at' => $expiresAt]);
                        } elseif (is_string($expiresAt)) {
                            try {
                                $expiresAt = new \DateTime($expiresAt);
                                \Log::debug('Converted string expiration to DateTime', ['expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
                            } catch (\Exception $e) {
                                $expiresAt = now()->addDays(30);
                                \Log::warning('Failed to parse expiration date, using default', [
                                    'original' => $accessToken->expires_at ?? 'null',
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        // Format the expiration date for database
                        $formattedExpiresAt = $expiresAt;
                        if ($expiresAt instanceof \DateTime) {
                            $formattedExpiresAt = $expiresAt->format('Y-m-d H:i:s');
                        }
                        
                        $tokenData = [
                            'user_id' => $user->id, // Explicitly set user_id
                            'token' => hash('sha256', $token),
                            'expires_at' => $formattedExpiresAt,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                        
                        // Add device info if available
                        if ($deviceType) {
                            $tokenData['device_type'] = (strlen($deviceType) > 50) ? substr($deviceType, 0, 47) . '...' : $deviceType;
                            \Log::debug('Added device type to token data', ['device_type' => $tokenData['device_type']]);
                        }
                        
                        if ($deviceId) {
                            $tokenData['device_id'] = $deviceId;
                            \Log::debug('Added device ID to token data', ['device_id_exists' => !empty($deviceId)]);
                        }
                        
                        // Log the token data before insertion
                        \Log::debug('Token data prepared for insertion', [
                            'user_id' => $tokenData['user_id'],
                            'token_hash' => substr($tokenData['token'], 0, 10) . '...',
                            'expires_at' => $formattedExpiresAt,
                            'fields' => array_keys($tokenData)
                        ]);
                        
                        // Check database connection
                        try {
                            $dbCheck = \DB::select('SELECT 1 as test');
                            \Log::debug('Database connection check', ['status' => 'connected', 'test' => $dbCheck]);
                        } catch (\Exception $e) {
                            \Log::error('Database connection error', ['error' => $e->getMessage()]);
                            throw $e;
                        }
                        
                        // Create the token using DB facade for more control
                        $inserted = \DB::table('tokens')->insert($tokenData);
                        
                        if ($inserted) {
                            $tokenId = \DB::getPdo()->lastInsertId();
                            \Log::info('Token stored in custom tokens table', [
                                'token_id' => $tokenId,
                                'user_id' => $user->id,
                                'expires_at' => $formattedExpiresAt,
                                'has_device_info' => !empty($deviceType) || !empty($deviceId)
                            ]);
                            
                            // Commit the transaction
                            \DB::commit();
                        } else {
                            $errorInfo = \DB::connection()->getPdo()->errorInfo();
                            \Log::error('Failed to insert token into tokens table', [
                                'user_id' => $user->id,
                                'error' => $errorInfo[2] ?? 'Unknown database error',
                                'error_code' => $errorInfo[1] ?? 0,
                                'sql_state' => $errorInfo[0] ?? 'HY000'
                            ]);
                            \DB::rollBack();
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to store token in custom tokens table', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'token_data' => isset($tokenData) ? [
                                'user_id' => $tokenData['user_id'] ?? null,
                                'token_length' => isset($tokenData['token']) ? strlen($tokenData['token']) : 0,
                                'expires_at' => $tokenData['expires_at'] ?? null,
                            ] : null
                        ]);
                        // Don't fail the request if custom token storage fails
                    }
                    
                    // Log successful registration
                    \Log::info('User registration completed successfully', [
                        'user_id' => $user->id,
                        'mobile' => $request->mobile,
                        'token_generated' => !empty($token)
                    ]);
                    
                    // Prepare response data
                    $responseData = [
                        'status' => 'success',
                        'message' => 'Registration completed successfully',
                        'data' => [
                            'user' => new UserResource($user),
                            'token' => $token,
                            'token_type' => 'Bearer',
                            'session_info' => [
                                'verified_at' => now()->toISOString(),
                                'login_method' => 'mobile_otp_registration'
                            ]
                        ]
                    ];
                    
                    // Log the response data (without sensitive information)
                    \Log::debug('Sending successful registration response', [
                        'user_id' => $user->id,
                        'has_token' => !empty($token),
                        'response_keys' => array_keys($responseData['data'])
                    ]);
                    
                    return response()->json($responseData, 201);
                    
                } catch (\Exception $e) {
                    // Log the error
                    \Log::error('Registration completion failed', [
                        'mobile' => $request->mobile,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    throw new \Exception('Failed to complete registration: ' . $e->getMessage());
                }
            }
            
            // For login OTP verification
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => [
                    'user' => new UserResource($otpResult['user']),
                    'token' => $otpResult['token'],
                    'token_type' => 'Bearer',
                    'token_details' => $otpResult['token_details'] ?? null,
                    'session_info' => [
                        'verified_at' => now()->toISOString(),
                        'device_registered' => !empty($otpResult['token_details']['device_type']),
                        'login_method' => 'mobile_otp_login'
                    ]
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
     *     path="/auth/resend-otp",
     *     operationId="resendOtp",
     *     tags={"Authentication"},
     *     summary="Resend OTP",
     *     description="Resend OTP to user's mobile number",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "type"},
     *             @OA\Property(property="mobile", type="string", example="+1234567890"),
     *             @OA\Property(property="type", type="string", enum={"register", "reset"}, example="register")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP resent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OTP has been resent to your mobile number"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="otp_sent_to", type="string", example="+91******7890"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'type' => 'required|in:login,register,forgot_password'
        ]);

        try {
            // Check if user with this mobile exists in user_profiles
            if ($request->type !== 'register') {
                $profileExists = \App\Models\UserProfile::where('mobile', $request->mobile)->exists();
                if (!$profileExists) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No user found with this mobile number.'
                    ], 404);
                }
            }

            $result = $this->otpService->resendOtp($request->mobile, $request->type);
            
            return response()->json([
                'status' => 'success',
                'message' => 'OTP resent successfully',
                'data' => [
                    'otp_sent_to' => $result['masked_mobile']
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
     *     path="/auth/forgot-password",
     *     operationId="forgotPassword",
     *     tags={"Authentication"},
     *     summary="Request password reset",
     *     description="Send password reset OTP to user's mobile number",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile"},
     *             @OA\Property(property="mobile", type="string", example="+1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset OTP has been sent to your mobile number"),
     *             @OA\Property(property="data", type="object", @OA\Property(property="otp_sent_to", type="string", example="+91******7890"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string'
        ]);

        try {
            // Check if user with this mobile exists in user_profiles
            $profile = \App\Models\UserProfile::where('mobile', $request->mobile)->first();
            
            if (!$profile) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No user found with this mobile number.'
                ], 404);
            }

            $result = $this->otpService->sendForgotPasswordOtp($request->mobile);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset OTP sent to your mobile number',
                'data' => [
                    'otp_sent_to' => $result['masked_mobile']
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
     *     path="/auth/reset-password",
     *     operationId="resetPassword",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     description="Reset user password using OTP verification",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"mobile", "otp", "password", "password_confirmation"},
     *             @OA\Property(property="mobile", type="string", example="+1234567890"),
     *             @OA\Property(property="otp", type="string", example="123456"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid OTP or validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed'
        ]);

        try {
            $result = $this->authService->resetPassword(
                $request->mobile,
                $request->otp,
                $request->password
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => 'Bearer'
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
     *     path="/auth/me",
     *     operationId="getCurrentUser",
     *     tags={"Authentication"},
     *     summary="Get current user",
     *     description="Get the currently authenticated user's information",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function me(Request $request): JsonResponse
    {
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
                'user' => new UserResource(Auth::user())
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/logout",
     *     operationId="logoutUser",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     description="Revoke the user's access token",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh-token",
     *     operationId="refreshToken",
     *     tags={"Authentication"},
     *     summary="Refresh authentication token",
     *     description="Refresh the current access token",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="new.eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=31536000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            
            $token = $user->createToken('auth-token')->plainTextToken;
            
            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token refresh failed'
            ], 500);
        }
    }
}
