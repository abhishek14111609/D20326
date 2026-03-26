<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SocialLoginRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Social Authentication",
 *     description="Social media authentication endpoints"
 * )
 * 
 * @OA\PathItem(
 *     path="/api/v1/auth/social",
 * )
 */
class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    /**
     * @OA\Post(
     *     path="/auth/social/google",
     *     operationId="googleLogin",
     *     tags={"Social Authentication"},
     *     summary="Login or register with Google",
     *     description="Authenticate a user using Google OAuth",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"access_token"},
     *             @OA\Property(property="access_token", type="string", example="ya29.a0ARrdaM...", description="Google OAuth access token"),
     *             @OA\Property(property="device_type", type="string", enum={"ios", "android", "web"}, example="ios"),
     *             @OA\Property(property="device_token", type="string", example="device_push_token_123", description="Device push notification token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="is_new_user", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or authentication failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function google(SocialLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->socialAuthService->authenticateWithGoogle($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => $result['is_new_user'] ? 'Account created successfully' : 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => 'Bearer',
                    'is_new_user' => $result['is_new_user']
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
     *     path="/auth/social/facebook",
     *     operationId="facebookLogin",
     *     tags={"Social Authentication"},
     *     summary="Login or register with Facebook",
     *     description="Authenticate a user using Facebook OAuth",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"access_token"},
     *             @OA\Property(property="access_token", type="string", example="EAA...", description="Facebook OAuth access token"),
     *             @OA\Property(property="device_type", type="string", enum={"ios", "android", "web"}, example="android"),
     *             @OA\Property(property="device_token", type="string", example="device_push_token_123", description="Device push notification token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Account created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="is_new_user", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or authentication failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function facebook(SocialLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->socialAuthService->authenticateWithFacebook($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => $result['is_new_user'] ? 'Account created successfully' : 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => 'Bearer',
                    'is_new_user' => $result['is_new_user']
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
     *     path="/auth/social/apple",
     *     operationId="appleLogin",
     *     tags={"Social Authentication"},
     *     summary="Login or register with Apple",
     *     description="Authenticate a user using Apple Sign In",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"identity_token"},
     *             @OA\Property(property="identity_token", type="string", example="eyJraWQiOiJ...", description="Apple ID token"),
     *             @OA\Property(property="device_type", type="string", enum={"ios", "android", "web"}, example="ios"),
     *             @OA\Property(property="device_token", type="string", example="device_push_token_123", description="Device push notification token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="is_new_user", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or authentication failed",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function apple(SocialLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->socialAuthService->authenticateWithApple($request->validated());
            
            return response()->json([
                'status' => 'success',
                'message' => $result['is_new_user'] ? 'Account created successfully' : 'Login successful',
                'data' => [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token'],
                    'token_type' => 'Bearer',
                    'is_new_user' => $result['is_new_user']
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
