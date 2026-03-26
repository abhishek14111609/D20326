<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingRequest;
use App\Http\Resources\SettingCollection;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Settings",
 *     description="Application settings management"
 * )
 * 
 * @OA\PathItem(
 *     path="/api/v1/settings",
 * )
 */
class SettingController extends Controller
{
    /**
     * The SettingService instance.
     *
     * @var SettingService
     */
    protected $settingService;

    /**
     * Create a new controller instance.
     *
     * @param SettingService $settingService
     * @return void
     */
    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
        // Make index, publicSettings, and getByGroup methods public
        $this->middleware('auth:api')->except(['index', 'publicSettings', 'getByGroup']);
    }

    /**
     * Display a listing of the settings.
     *
     * @OA\Get(
     *     path="/settings",
     *     operationId="getAllSettings",
     *     tags={"Settings"},
     *     summary="Get all settings (Admin only)",
     *     description="Retrieves all application settings. Requires admin privileges.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Setting")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get all settings directly from the model to ensure we have all fields
            $settings = Setting::query()
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get();
            
            // Format the settings for the response
            $formattedSettings = $settings->map(function($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'display_name' => $setting->display_name,
                    'description' => $setting->description,
                    'is_public' => (bool) $setting->is_public,
                    'options' => $setting->options ?? [],
                    'sort_order' => $setting->sort_order ?? 0,
                    'created_at' => $setting->created_at ? $setting->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'updated_at' => $setting->updated_at ? $setting->updated_at->toDateTimeString() : now()->toDateTimeString(),
                ];
            });
            
            // Return the response without using the successResponse method
            return response()->json([
                'success' => true,
                'data' => $formattedSettings->values()->all(),
                'message' => 'Settings retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the public settings.
     *
     * @OA\Get(
     *     path="/settings/public",
     *     operationId="getPublicSettings",
     *     tags={"Settings"},
     *     summary="Get public settings",
     *     description="Retrieves all public application settings. No authentication required.",
     *     @OA\Response(
     *         response=200,
     *         description="Public settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Setting")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function publicSettings(Request $request)
    {
        try {
            // Get only public settings directly from the model
            $settings = Setting::query()
                ->where('is_public', true)
                ->orderBy('group')
                ->orderBy('sort_order')
                ->get();
            
            // Format the settings for the response
            $formattedSettings = $settings->map(function($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'display_name' => $setting->display_name,
                    'description' => $setting->description,
                    'is_public' => true, // Always true for public settings
                    'options' => $setting->options ?? [],
                    'sort_order' => $setting->sort_order ?? 0,
                    'created_at' => $setting->created_at ? $setting->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'updated_at' => $setting->updated_at ? $setting->updated_at->toDateTimeString() : now()->toDateTimeString(),
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedSettings->values()->all(),
                'message' => 'Public settings retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve public settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display settings by group.
     *
     * @OA\Get(
     *     path="/settings/group/{group}",
     *     operationId="getSettingsByGroup",
     *     tags={"Settings"},
     *     summary="Get settings by group",
     *     description="Retrieves settings by group name. Returns only public settings for non-admin users.",
     *     @OA\Parameter(
     *         name="group",
     *         in="path",
     *         required=true,
     *         description="The group name of the settings to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Setting")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No settings found for the specified group",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param Request $request
     * @param string $group
     * @return JsonResponse
     */
    public function getByGroup(Request $request, string $group)
    {
        try {
            $isAdmin = $request->user() && $request->user()->hasRole('admin');
            $settings = $this->settingService->getSettingsByGroup($group, $isAdmin);
            
            if (empty($settings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No settings found for the specified group',
                ], 404);
            }
            
            // Convert the settings to a properly formatted array
            $formattedSettings = [];
            
            foreach ($settings as $key => $setting) {
                if (is_array($setting)) {
                    $formattedSettings[] = [
                        'id' => $setting['key'] ?? $key,
                        'key' => $setting['key'] ?? $key,
                        'value' => $setting['value'] ?? null,
                        'type' => $setting['type'] ?? 'string',
                        'group' => $setting['group'] ?? $group,
                        'display_name' => $setting['display_name'] ?? $setting['key'] ?? $key,
                        'description' => $setting['description'] ?? '',
                        'is_public' => $setting['is_public'] ?? false,
                        'options' => $setting['options'] ?? [],
                        'sort_order' => $setting['sort_order'] ?? 0,
                        'created_at' => $setting['created_at'] ?? now(),
                        'updated_at' => $setting['updated_at'] ?? now(),
                    ];
                } else {
                    // Handle case where $setting is a simple key-value pair
                    $formattedSettings[] = [
                        'id' => $key,
                        'key' => $key,
                        'value' => $setting,
                        'type' => 'string',
                        'group' => $group,
                        'display_name' => $key,
                        'description' => '',
                        'is_public' => true,
                        'options' => [],
                        'sort_order' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $formattedSettings,
                'message' => 'Settings retrieved successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified setting.
     *
     * @OA\Get(
     *     path="/settings/{key}",
     *     operationId="getSettingByKey",
     *     tags={"Settings"},
     *     summary="Get a specific setting by key",
     *     description="Retrieves a specific setting by its key. Non-public settings require admin access.",
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         required=true,
     *         description="The key of the setting to retrieve",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Setting retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Setting")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized to access this setting",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Setting not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param string $key
     * @return JsonResponse
     */
    public function show(string $key)
    {
        $setting = $this->settingService->getSettingByKey($key);
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }
        
        // Check if the setting is public or user is admin
        if (!$setting->is_public && !auth()->user()->hasRole('admin')) {
            return $this->errorResponse('Unauthorized to access this setting', 403);
        }
        
        return $this->successResponse(new SettingResource($setting));
    }

    /**
     * Store a newly created setting in storage.
     *
     * @OA\Post(
     *     path="/settings",
     *     operationId="createSetting",
     *     tags={"Settings"},
     *     summary="Create a new setting (Admin only)",
     *     description="Creates a new application setting. Requires admin privileges.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key", "value", "type", "group", "display_name"},
     *             @OA\Property(property="key", type="string", example="app.name"),
     *             @OA\Property(property="value", type="string", example="My Application"),
     *             @OA\Property(property="type", type="string", enum={"string","text","number","boolean","url","timezone","json","array","select","radio","checkbox"}, example="string"),
     *             @OA\Property(property="group", type="string", example="general"),
     *             @OA\Property(property="display_name", type="string", example="Application Name"),
     *             @OA\Property(property="description", type="string", nullable=true, example="The name of the application"),
     *             @OA\Property(property="is_public", type="boolean", example=true),
     *             @OA\Property(property="options", type="object", nullable=true, 
     *                 @OA\Property(property="option1", type="string", example="Value 1"),
     *                 @OA\Property(property="option2", type="string", example="Value 2")
     *             ),
     *             @OA\Property(property="sort_order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Setting created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Setting"),
     *             @OA\Property(property="message", type="string", example="Setting created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
            'type' => 'required|string|in:string,text,number,boolean,url,timezone,json,array,select,radio,checkbox',
            'group' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
            'options' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $setting = $this->settingService->createSetting($validated);
        
        return $this->successResponse(
            new SettingResource($setting),
            'Setting created successfully',
            201
        );
    }

    /**
     * Update the specified setting in storage.
     *
     * @OA\Put(
     *     path="/settings/{key}",
     *     operationId="updateSetting",
     *     tags={"Settings"},
     *     summary="Update a setting (Admin only)",
     *     description="Updates an existing application setting. Requires admin privileges.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="key",
     *         in="path",
     *         required=true,
     *         description="The key of the setting to update",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value"},
     *             @OA\Property(property="value", type="string", example="Updated Application Name"),
     *             @OA\Property(property="type", type="string", enum={"string","text","number","boolean","url","timezone","json","array","select","radio","checkbox"}, example="string"),
     *             @OA\Property(property="group", type="string", example="general"),
     *             @OA\Property(property="display_name", type="string", example="Application Name"),
     *             @OA\Property(property="description", type="string", nullable=true, example="The name of the application"),
     *             @OA\Property(property="is_public", type="boolean", example=true),
     *             @OA\Property(property="options", type="object", nullable=true,
     *                 @OA\Property(property="option1", type="string", example="Value 1"),
     *                 @OA\Property(property="option2", type="string", example="Value 2")
     *             ),
     *             @OA\Property(property="sort_order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Setting updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Setting"),
     *             @OA\Property(property="message", type="string", example="Setting updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Admin access required",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Setting not found",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param UpdateSettingRequest $request
     * @param string $key
     * @return JsonResponse
     */
    public function update(UpdateSettingRequest $request, string $key)
    {
        $setting = $this->settingService->getSettingByKey($key);
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }
        
        $validated = $request->validated();
        $setting = $this->settingService->updateSetting($setting, $validated);
        
        return $this->successResponse(
            new SettingResource($setting),
            'Setting updated successfully'
        );
    }

    /**
     * Remove the specified setting (admin only).
     *
     * @param string $key
     * @return JsonResponse
     */
    public function destroy(string $key)
    {
        $setting = $this->settingService->getSettingByKey($key);
        
        if (!$setting) {
            return $this->errorResponse('Setting not found', 404);
        }
        
        // Prevent deletion of required settings
        if ($this->isRequiredSetting($key)) {
            return $this->errorResponse('This is a required setting and cannot be deleted', 403);
        }
        
        $this->settingService->deleteSetting($setting);
        
        return $this->successResponse(
            null,
            'Setting deleted successfully',
            204
        );
    }

    /**
     * Check if a setting is required by the system.
     *
     * @param string $key
     * @return bool
     */
    protected function isRequiredSetting(string $key): bool
    {
        $requiredSettings = [
            'app.name',
            'app.url',
            'mail.driver',
            'mail.host',
            'mail.port',
            'mail.username',
            'mail.password',
            'mail.encryption',
            'mail.from.address',
            'mail.from.name',
        ];
        
        return in_array($key, $requiredSettings);
    }

    /**
     * Return a successful JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = [];
        
        if (!is_null($data)) {
            $response['data'] = $data;
        }
        
        if (!empty($message)) {
            $response['message'] = $message;
        }
        
        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     *
     * @param string|array $message
     * @param int $status
     * @return JsonResponse
     */
    protected function errorResponse($message, int $status = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }
}
