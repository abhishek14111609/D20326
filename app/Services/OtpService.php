<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use App\Models\Token;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OtpService
{
    /**
     * Send OTP to mobile number with optional data
     * 
     * @param string $mobile The mobile number to send OTP to
     * @param string $type The type of OTP (login, registration, etc.)
     * @param array $data Optional data to store with the OTP (e.g., registration data)
     * @return array
     */
    public function sendOtp(string $mobile, string $type = 'login', array $data = []): array
    {
        // Check recent attempts (max 5 per hour)
        $recentAttempts = Otp::where('mobile', $mobile)
                            ->where('type', $type)
                            ->where('created_at', '>', now()->subHour())
                            ->count();
        
        if ($recentAttempts >= 5) {
            throw new \Exception('Too many OTP requests. Please try again later.');
        }
        
        // Mark any existing pending OTPs as expired
        Otp::where('mobile', $mobile)
           ->where('type', $type)
           ->where('status', 'pending')
           ->update(['status' => 'expired']);
        
        // Generate 4-digit OTP - use fixed OTP for development testing
        $otpCode = config('app.env') === 'local' ? '1234' : sprintf('%04d', mt_rand(1000, 9999));
        
        // Log the data being stored with OTP
        \Log::debug('Storing OTP with data', [
            'mobile' => $mobile,
            'type' => $type,
            'data_keys' => array_keys($data),
            'has_password' => !empty($data['password'])
        ]);
        
        // Prepare data for storage
        $otpData = [
            'mobile' => $mobile,
            'otp_code' => $otpCode,
            'type' => $type,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(5),
            'ip_address' => request()->ip(),
        ];
        
        // Only include data if not empty and is an array
        if (!empty($data) && is_array($data)) {
            // Ensure sensitive data is not logged
            $loggableData = $data;
            if (isset($loggableData['password'])) {
                $loggableData['password'] = '***';
            }
            
            // Log the data being stored
            \Log::debug('Storing data with OTP', [
                'data_keys' => array_keys($data),
                'has_mobile' => !empty($data['mobile']),
                'has_password' => !empty($data['password'])
            ]);
            
            // Store the data as JSON
            $otpData['data'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        // Create the OTP record
        $otp = Otp::create($otpData);
        
        // Verify the data was stored correctly
        if (!empty($data) && empty($otp->data)) {
            \Log::warning('Failed to store data with OTP', [
                'otp_id' => $otp->id,
                'data_size' => strlen(json_encode($data))
            ]);
            
            // Try to update the record directly as a fallback
            $otp->data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $otp->save();
        }
        
        // In production, integrate with SMS service like Twilio, AWS SNS, etc.
        // For development, we'll log the OTP
        \Log::info("OTP for {$mobile}: {$otpCode} (Type: {$type}) - ID: {$otp->id}");
        
        // Mask mobile number for response (e.g., 123****890)
        $maskedMobile = substr($mobile, 0, 3) . str_repeat('*', strlen($mobile) - 5) . substr($mobile, -2);
        
        return [
            'otp_id' => $otp->id,
            'mobile' => $mobile,
            'masked_mobile' => $maskedMobile,
            'expires_in' => 300, // 5 minutes in seconds
            'type' => $type
        ];
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(string $mobile, string $otpCode, string $type = 'login'): array
    {
        try {
            // Log the verification attempt with more context
            \Log::debug('Starting OTP verification', [
                'mobile' => $mobile,
                'type' => $type,
                'otp_code' => $otpCode,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // Get the most recent OTP for this mobile and type
            $otp = Otp::where('mobile', $mobile)
                     ->where('type', $type)
                     ->where('otp_code', $otpCode)
                     ->orderBy('created_at', 'desc')
                     ->first();
            
            if (!$otp) {
                // Log detailed error for debugging
                $recentOtps = Otp::where('mobile', $mobile)
                               ->where('type', $type)
                               ->orderBy('created_at', 'desc')
                               ->take(3)
                               ->get()
                               ->map(function($item) {
                                   return [
                                       'id' => $item->id,
                                       'status' => $item->status,
                                       'otp_code' => $item->otp_code,
                                       'expires_at' => $item->expires_at,
                                       'created_at' => $item->created_at,
                                       'has_data' => !empty($item->data)
                                   ];
                               });
                
                \Log::error('No matching OTP found', [
                    'mobile' => $mobile,
                    'type' => $type,
                    'otp_code' => $otpCode,
                    'recent_otps' => $recentOtps,
                    'current_time' => now()
                ]);
                
                return [
                    'valid' => false,
                    'message' => 'Invalid OTP code. Please check and try again.'
                ];
            }
            
            // Check if OTP is already verified
            if ($otp->status === 'verified') {
                // Check if verification is still within the valid time window (5 minutes)
                if ($otp->verified_at && $otp->verified_at->diffInMinutes(now()) <= 5) {
                    return [
                        'valid' => true,
                        'message' => 'OTP already verified',
                        'otp' => $otp
                    ];
                }
                
                return [
                    'valid' => false,
                    'message' => 'OTP verification expired. Please request a new one.'
                ];
            }
            
            // Check if OTP is blocked
            if ($otp->status === 'blocked') {
                return [
                    'valid' => false,
                    'message' => 'This OTP has been blocked due to too many failed attempts. Please request a new one.'
                ];
            }
            
            // Check if OTP has expired
            if ($otp->expires_at && $otp->expires_at->isPast()) {
                $otp->update(['status' => 'expired']);
                return [
                    'valid' => false,
                    'message' => 'OTP has expired. Please request a new one.'
                ];
            }
            
            // Check if max attempts reached
            if ($otp->attempts >= 3) {
                $otp->update(['status' => 'blocked']);
                return [
                    'valid' => false,
                    'message' => 'Too many failed attempts. This OTP has been blocked. Please request a new one.'
                ];
            }
            
            // Mark OTP as verified
            $otp->update([
                'status' => 'verified',
                'verified_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'attempts' => $otp->attempts + 1
            ]);
            
            return [
                'valid' => true,
                'message' => 'OTP verified successfully',
                'otp' => $otp
            ];
            
            // For registration, return the stored data
            if ($type === 'register') {
                // Decode the stored data with better error handling
                $data = [];
                $rawData = $otp->data;
                
                // Log the raw data for debugging
                \Log::debug('Raw OTP data', [
                    'otp_id' => $otp->id,
                    'raw_data_type' => gettype($rawData),
                    'raw_data' => $rawData
                ]);
                
                // Handle different data formats
                if (is_string($rawData)) {
                    $data = json_decode($rawData, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Try to fix common JSON issues
                        $fixedJson = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rawData);
                        $data = json_decode($fixedJson, true);
                        
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            \Log::warning('Failed to decode JSON data from OTP', [
                                'otp_id' => $otp->id,
                                'error' => json_last_error_msg(),
                                'raw_data' => $rawData
                            ]);
                            $data = [];
                        }
                    }
                } elseif (is_array($rawData) || is_object($rawData)) {
                    $data = (array)$rawData;
                }
                
                // Ensure mobile is included in the registration data
                if (!empty($mobile) && (empty($data['mobile']) || $data['mobile'] !== $mobile)) {
                    $data['mobile'] = $mobile;
                    \Log::debug('Added mobile to registration data', ['mobile' => $mobile]);
                }
                
                // Log the processed data for debugging
                \Log::debug('Processed registration data from OTP', [
                    'otp_id' => $otp->id,
                    'data_type' => gettype($data),
                    'data_keys' => is_array($data) ? array_keys($data) : 'Not an array',
                    'has_mobile' => !empty($data['mobile']),
                    'has_email' => !empty($data['email']),
                    'registration_type' => $data['registration_type'] ?? 'single'
                ]);
                
                // Ensure mobile is included in the registration data
                if (empty($data['mobile']) && !empty($mobile)) {
                    $data['mobile'] = $mobile;
                }
                
                return [
                    'success' => true,
                    'otp_id' => $otp->id,
                    'data' => $data,
                    'type' => 'register',
                    'mobile' => $mobile,
                    'timestamp' => now()->toDateTimeString()
                ];
            }
        
        // For login, find or create user and generate a token
        $user = User::firstOrCreate(
            ['mobile' => $mobile],
            ['status' => 'active'] // Set default status for new users
        );
        
        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;
        
        // Log successful login
        \Log::info('User logged in with OTP', [
            'user_id' => $user->id,
            'mobile' => $mobile,
            'token_id' => $user->currentAccessToken()->id ?? null
        ]);
        
        // Get token details
        $tokenDetails = [
            'token_type' => 'Bearer',
            'expires_at' => now()->addMinutes(config('sanctum.expiration', 1440))->toDateTimeString()
        ];
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_details' => $tokenDetails,
            'type' => 'login'
        ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Resend OTP
     */
    public function resendOtp(string $mobile, string $type = 'login'): array
    {
        try {
            // For registration, we don't need to check if user exists
            if ($type !== 'register') {
                // Check if user with this mobile exists in user_profiles
                $profileExists = \App\Models\UserProfile::where('mobile', $mobile)->exists();
                if (!$profileExists) {
                    throw new \Exception('No user found with this mobile number.');
                }
            }

            // Check recent attempts (max 5 per hour)
            $recentAttempts = Otp::where('mobile', $mobile)
                                ->where('type', $type)
                                ->where('created_at', '>', now()->subHour())
                                ->count();
            
            if ($recentAttempts >= 5) {
                throw new \Exception('Too many OTP requests. Please try again later.');
            }
            
            // Mark any existing pending OTPs as expired
            Otp::where('mobile', $mobile)
               ->where('type', $type)
               ->where('status', 'pending')
               ->update(['status' => 'expired']);
            
            // Generate new OTP
            return $this->sendOtp($mobile, $type);
            
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP', [
                'mobile' => $mobile,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Send forgot password OTP
     */
    public function sendForgotPasswordOtp(string $mobile): array
    {
        $profile = \App\Models\UserProfile::where('mobile', $mobile)->first();
        
        if (!$profile) {
            throw new \Exception('Mobile number not registered');
        }
        
        return $this->sendOtp($mobile, 'forgot_password');
    }

    /**
     * Get OTP data by mobile and type
     * 
     * @param string $mobile
     * @param string $type
     * @param bool $checkVerifiedOnly If true, only checks verified OTPs. If false, also checks pending OTPs.
     * @return array|null
     */
    public function getOtpData(string $mobile, string $type, bool $checkVerifiedOnly = false): ?array
    {
        try {
            $query = Otp::where('mobile', $mobile)
                       ->where('type', $type);
            
            if ($checkVerifiedOnly) {
                $query->where('status', 'verified');
            } else {
                $query->whereIn('status', ['pending', 'verified']);
            }
            
            $otp = $query->orderBy('created_at', 'desc')
                        ->first();
            
            // Log the query results for debugging
            \Log::debug('getOtpData query', [
                'mobile' => $mobile,
                'type' => $type,
                'found_otp' => $otp ? true : false,
                'has_data' => $otp && !empty($otp->data)
            ]);
            
            if (!$otp) {
                return null;
            }
            
            $data = [];
            $rawData = $otp->data;
            
            // If we found a pending OTP, mark it as verified
            if ($otp->status === 'pending') {
                $otp->update([
                    'status' => 'verified',
                    'verified_at' => now()
                ]);
            }
            
            // Handle different data formats
            if (is_string($rawData)) {
                $data = json_decode($rawData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Try to fix common JSON issues
                    $fixedJson = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rawData);
                    $data = json_decode($fixedJson, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        \Log::warning('Failed to decode JSON data in getOtpData', [
                            'otp_id' => $otp->id,
                            'error' => json_last_error_msg(),
                            'raw_data' => $rawData
                        ]);
                        return null;
                    }
                }
            } elseif (is_array($rawData) || is_object($rawData)) {
                $data = (array)$rawData;
            }
            
            // Ensure mobile is included in the returned data
            if (!empty($mobile) && (empty($data['mobile']) || $data['mobile'] !== $mobile)) {
                $data['mobile'] = $mobile;
            }
            
            // Log the processed data for debugging
            \Log::debug('Processed OTP data', [
                'otp_id' => $otp->id,
                'data_type' => gettype($data),
                'data_keys' => is_array($data) ? array_keys($data) : [],
                'has_mobile' => !empty($data['mobile'])
            ]);
            
            return $data;
            
        } catch (\Exception $e) {
            \Log::error('Error in getOtpData', [
                'mobile' => $mobile,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Get active OTP for mobile and type
     */
    private function getActiveOtp(string $mobile, string $type): ?Otp
    {
        $query = Otp::where('mobile', $mobile)
                   ->where('type', $type)
                   ->where('expires_at', '>', now())
                   ->whereIn('status', ['pending', 'verified'])
                   ->orderBy('created_at', 'desc');
        
        // Get the most recent OTP
        $otp = $query->first();
        
        // Log the query results for debugging
        \Log::debug('getActiveOtp Query', [
            'mobile' => $mobile,
            'type' => $type,
            'found_otp' => $otp ? [
                'id' => $otp->id,
                'status' => $otp->status,
                'expires_at' => $otp->expires_at,
                'created_at' => $otp->created_at,
                'has_data' => !empty($otp->data),
                'now' => now()
            ] : null,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);
        
        return $otp;
    }

    /**
     * Mask mobile number for privacy
     */
    private function maskMobile(string $mobile): string
    {
        $length = strlen($mobile);
        if ($length <= 4) {
            return str_repeat('*', $length);
        }
        
        return substr($mobile, 0, 2) . str_repeat('*', $length - 4) . substr($mobile, -2);
    }

    /**
     * Send SMS (integrate with SMS service)
     */
    private function sendSms(string $mobile, string $message): bool
    {
        // TODO: Integrate with SMS service
        // Example integrations:
        // - Twilio
        // - AWS SNS
        // - Firebase Cloud Messaging
        // - Local SMS gateway
        
        return true;
    }
}
