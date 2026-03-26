<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class UserResource extends JsonResource
{
    private $googleMapsApiKey;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->googleMapsApiKey = env('GOOGLE_MAPS_API_KEY');
    }

    public function toArray(Request $request): array
    {
        $nullToDash = fn($value) => $value ?? '-';
        $registrationType = $this->profile?->registration_type ?? 'single';
        $isDuo = ($this->profile?->is_couple ?? false) && $registrationType === 'duo';
        $isProfileComplete = $this->isProfileComplete();

        // Get device info from both user model and current token
        $token = $this->currentAccessToken();
        $deviceInfo = [
            'device_token' => $this->device_token ?? $token?->device_token ?? null,
            'device_type' => $this->device_type ?? $token?->device_type ?? null,
            'login_type' => $this->login_type ?? $token?->login_type ?? null,
            'last_login_ip' => $this->last_login_ip,
            'last_login_at' => $this->last_login_at,
        ];

        // Helper: convert comma-separated string to array
        $toArray = function ($value) {
            if (empty($value)) return [];
            if (is_array($value)) return $value;
            return array_map('trim', explode(',', $value));
        };

        if ($isDuo == true) {
            $response = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'status' => $this->status,
				'is_match' => $this->is_match ?? 0,
                'age' => $this->profile?->age ?? '-',
                'gender' => $this->profile?->gender ?? '-',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $this->deleted_at,
                'profile' => [
                    'registration_type' => $registrationType,
                    'profile_complete' => $isProfileComplete,
                ],
                ...$deviceInfo,
            ];
        } else {
            $response = [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'mobile' => $this->profile->mobile ?? '-',
                'status' => $this->status,
				'is_match' => $this->is_match ?? 0,
                'age' => $this->profile?->age ?? '-',
                'gender' => $this->profile?->gender ?? '-',
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'deleted_at' => $this->deleted_at ?? '',
                'profile' => [
                    'registration_type' => $registrationType,
                    'profile_complete' => $isProfileComplete,
                ],
                ...$deviceInfo,
            ];
        }

        if (isset($this->distance)) {
            $response['distance'] = $this->distance . ' km';
        }

        // Handle avatar/gallery based on user type
        if ($isDuo) {
            // For duo users, show gallery
            // $response['gallery'] = $this->getGallery();
            $response['avatar'] = $this->getAvatarUrl(); // No avatar for duo users
        } else {
            // For single users, show avatar
            $response['avatar'] = $this->getAvatarUrl();
            $response['gallery'] = $this->getGallery(); // No gallery for single users
        }

        // Add profile data if exists
        if ($this->profile) {
            $profileData = [
                'is_couple' => $isDuo,
                'couple_name' => $this->profile->couple_name ?? '',
                'relationship_status' => $this->profile->relationship_status ?? null,
                'languages' => $this->profile->languages ?? [],
                'occupation' => $this->profile->occupation ?? null,
                'bio' => $this->profile->bio ?? null,
                'interests' => $toArray($this->profile->interest ?? ''),
                'hobbies' => $toArray($this->profile->hobby ?? ''),
                'location' => $this->profile->location ?? null,
				'looking_for' => $this->profile->looking_for,
				'ethnicity' => $this->profile->ethnicity,
				'address' => $this->profile->address,
				'latitude' => $this->profile->latitude ?? '',
				'longitude' => $this->profile->longitude ?? '',
            ];

            if ($isDuo) {
                $profileData = array_merge($profileData, $this->getDuoProfileData($toArray));
            }

            $response['profile'] = array_merge($response['profile'], $profileData);
        }

        return $response;
    }

    /**
     * Get the avatar URL for the user
     */
    protected function getAvatarUrl(): ?string
	{
		// No avatar → return default
		if (empty($this->avatar)) {
			return 'https://duos.webvibeinfotech.in/public/assets/img/avatars/default-avatar.png';
		}

		// Already full URL? return as it is
		if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
			return $this->avatar;
		}

		// Always return correct storage URL
		return 'https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $this->avatar;
	}


    /**
     * Get the gallery images for the user
     */
protected function getGallery(): array
{
    $gallery = [];

    // Single users gallery
    if (empty($this->profile->is_couple) && !empty($this->profile->gallery_images)) {
        $images = $this->profile->gallery_images;

        // Decode if JSON string
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        // Ensure array
        if (!is_array($images)) {
            $images = [];
        }

        foreach ($images as $image) {
            if (!empty($image)) {
                // Build full URL
				$cleanPath = str_replace('\\', '/', $image);
				$cleanPath = ltrim($cleanPath, '/');
                $gallery[] = 'https://duos.webvibeinfotech.in/storage/app/public/gallery_images/' . $cleanPath;
            }
        }

        return $gallery;
    }

    // Duo users
    if (!empty($this->profile->partner1_photo)) {
        $gallery[] = asset('storage/' . ltrim($this->profile->partner1_photo, '/'));
    }
    if (!empty($this->profile->partner2_photo)) {
        $gallery[] = asset('storage/' . ltrim($this->profile->partner2_photo, '/'));
    }

    return $gallery;
}


    /**
     * Get profile data specific to duo users
     */
    protected function getDuoProfileData(callable $toArray): array
    {
        $partner1Age = $this->profile?->partner1_dob ? Carbon::parse($this->profile->partner1_dob)->age : null;
        $partner2Age = $this->profile?->partner2_dob ? Carbon::parse($this->profile->partner2_dob)->age : null;

        // Get gallery images with full URL
        $gallery = [];
        if (!empty($this->profile?->gallery_images)) {
            $images = $this->profile->gallery_images;
            
            // Handle both JSON string and array cases
            if (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }
            
            // Ensure we have an array
            $images = is_array($images) ? $images : [];
            
            // Process each image path
            foreach ($images as $image) {
                if (!empty($image)) {
                    // Handle different path formats
                    $cleanPath = str_replace('\\', '/', $image);
                    $cleanPath = ltrim($cleanPath, '/');
                    $gallery[] = 'https://duos.webvibeinfotech.in/storage/app/public/gallery_images/' . $cleanPath;
                }
            }
        }

        return [
            'gallery' => $gallery,
			'avatar' => $this->user?->avatar ?: '-',
			'looking_for' => $this->profile->looking_for,
			'ethnicity' => $this->profile->ethnicity,
			'address' => $this->profile->address,
            'partner1' => [
                'name' => $this->profile?->partner1_name ?: '-',
                'email' => $this->profile?->partner1_email ?: '-',
                'gender' => $this->profile?->partner1_gender ?: '-',
                'dob' => $this->profile?->partner1_dob,
                'age' => $partner1Age,
                'location' => $this->profile?->partner1_location ?: '-',
                'interest' => $toArray($this->profile?->partner1_interest ?? ''),
                'hobby' => $toArray($this->profile?->partner1_hobby ?? ''),
                'photo' => $this->profile?->partner1_photo 
    ? 'https://duos.webvibeinfotech.in/storage/app/public/partner_photos/' . $this->profile->partner1_photo
    : '',
                'bio' => $this->profile?->partner1_about ?? '',
                'mobile' => $this->profile?->partner1_phone ?? '',
            ],
            'partner2' => [
                'name' => $this->profile?->partner2_name ?: '-',
                'email' => $this->profile?->partner2_email ?: '-',
                'gender' => $this->profile?->partner2_gender ?: '-',
                'dob' => $this->profile?->partner2_dob,
                'age' => $partner2Age,
                'location' => $this->profile?->partner2_location ?: '-',
                'interest' => $toArray($this->profile?->partner2_interest ?? ''),
                'hobby' => $toArray($this->profile?->partner2_hobby ?? ''),
               'photo' => $this->profile?->partner2_photo 
    ? 'https://duos.webvibeinfotech.in/storage/app/public/partner_photos/' . $this->profile->partner2_photo
    : '',
                'bio' => $this->profile?->partner2_about ?? '',
                'mobile' => $this->profile?->partner2_phone ?? '',
            ]
        ];
    }

    /**
     * Check if the user's profile is complete
     */
    protected function isProfileComplete(): bool
    {
        // For single users, check required fields
        if (($this->profile?->registration_type ?? 'single') === 'single') {
            return !empty($this->profile?->name) && 
                   !empty($this->profile?->mobile) && 
                   !empty($this->profile?->gender) &&
                   !empty($this->profile?->dob);
        }
        
        // For duo profiles, check required fields for both partners
        return !empty($this->profile?->couple_name) &&
               !empty($this->profile?->partner1_name) &&
               !empty($this->profile?->partner1_mobile) &&
               !empty($this->profile?->partner1_gender) &&
               !empty($this->profile?->partner1_dob) &&
               !empty($this->profile?->partner2_name) &&
               !empty($this->profile?->partner2_mobile) &&
               !empty($this->profile?->partner2_gender) &&
               !empty($this->profile?->partner2_dob);
    }

    /**
     * Geocode address to lat/lng
     */
    protected function geocodeLocation(string $address): ?array
    {
        if (empty($address)) {
            return null;
        }

        try {
            $formattedAddress = $address . ', India';

            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $formattedAddress,
                'key' => $this->googleMapsApiKey,
                'region' => 'in',
                'components' => 'country:IN',
            ]);

            $data = $response->json();

            if ($response->successful() && $data['status'] === 'OK' && !empty($data['results'][0]['geometry']['location'])) {
                return $data['results'][0]['geometry']['location'];
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Geocoding failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate distance (km) using Haversine
     */
    protected function calculateDistance($origin, $destination): ?float
    {
        try {
            $originCoords = $this->extractCoordinates($origin);
            $destCoords = $this->extractCoordinates($destination);

            if (!$originCoords || !$destCoords) {
                return null;
            }

            $lat1 = $originCoords['lat'];
            $lon1 = $originCoords['lng'];
            $lat2 = $destCoords['lat'];
            $lon2 = $destCoords['lng'];

            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;

            return round(($miles * 1.609344), 2);
        } catch (\Exception $e) {
            \Log::error('Distance calculation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract coordinates
     */
    protected function extractCoordinates($location): ?array
    {
        if (is_array($location)) {
            if (isset($location['lat']) && isset($location['lng'])) {
                return ['lat' => $location['lat'], 'lng' => $location['lng']];
            }
            if (isset($location['address'])) {
                return $this->geocodeLocation($location['address']);
            }
        } elseif (is_object($location)) {
            if (isset($location->lat) && isset($location->lng)) {
                return ['lat' => $location->lat, 'lng' => $location->lng];
            }
            if (isset($location->address)) {
                return $this->geocodeLocation($location->address);
            }
        } elseif (is_string($location)) {
            return $this->geocodeLocation($location);
        }

        return null;
    }
}
