<?php

namespace App\Services;

use App\Models\Swipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class SwipeService
{
    /**
     * Get profiles for the user to swipe on
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
public function getProfilesForUser($userId, $page = 1, $perPage = 10)
{
    $currentUser = User::with('profile')->find($userId);

    if (!$currentUser) {
        // 🔥 EMPTY paginator return kar
        return User::whereRaw('1=0')->paginate($perPage);
    }

    $loginLat = null;
    $loginLng = null;

    if (
        $currentUser->profile &&
        !empty($currentUser->profile->latitude) &&
        !empty($currentUser->profile->longitude)
    ) {
        $loginLat = (float) $currentUser->profile->latitude;
        $loginLng = (float) $currentUser->profile->longitude;
    }

    $excludedUserIds = Swipe::where('swiper_id', $userId)
        ->pluck('swiped_id')
        ->toArray();

    $profiles = User::with('profile')
        ->whereNotIn('id', array_merge($excludedUserIds, [$userId]))
        ->where('status', 'active')
        ->orderBy('id', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);

    $profiles->getCollection()->each(function ($user) use ($loginLat, $loginLng) {
        $user->distance = 'N/A';

        if (
            !$loginLat ||
            !$loginLng ||
            !$user->profile ||
            empty($user->profile->latitude) ||
            empty($user->profile->longitude)
        ) {
            return;
        }

        $user->distance = round(
            $this->calculateDistanceKm(
                $loginLat,
                $loginLng,
                (float) $user->profile->latitude,
                (float) $user->profile->longitude
            ),
            2
        ) . ' km';
    });

    // ✅ ALWAYS paginator
    return $profiles;
}




    /**
     * Calculate distance between two points using Haversine formula
     */
/**
 * Calculate distance between two locations using Haversine formula
 * Works with: 
 * - "mavadi chowk" (address)
 * - '{"lat": 22.3039, "lng": 70.8022}' (json)
 * - ['lat' => 22.3039, 'lng' => 70.8022] (array)
 */
private function cleanCoordinate($value)
{
    if ($value === null) return null;
    return (float) trim($value); // now DB ma "8.4658" che, so simple
}

private function calculateDistanceKm($lat1, $lon1, $lat2, $lon2)
{
    $earthRadius = 6371; // KM

    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    $latDiff = $lat2 - $lat1;
    $lonDiff = $lon2 - $lon1;

    $a = sin($latDiff / 2) ** 2 +
         cos($lat1) * cos($lat2) *
         sin($lonDiff / 2) ** 2;

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return round($earthRadius * $c, 2);
}

    /**
     * Like a user's profile
     *
     * @param int $swiperId
     * @param int $swipedId
     * @return array
     */
    public function likeUser($swiperId, $swipedId)
    {
        return DB::transaction(function () use ($swiperId, $swipedId) {
            // Check if the other user has already liked the current user
            $isMatch = false;
            $existingSwipe = Swipe::where('swiper_id', $swipedId)
                ->where('swiped_id', $swiperId)
                ->where('type', 'like')
                ->first();

            if ($existingSwipe) {
                $isMatch = true;
                // Update the existing swipe to mark it as a match
                $existingSwipe->update(['matched' => true]);
            }

            // Record the swipe
            Swipe::updateOrCreate(
                [
                    'swiper_id' => $swiperId,
                    'swiped_id' => $swipedId
                ],
                [
                    'type' => 'like',
                    'matched' => $isMatch
                ]
            );

            return ['is_match' => $isMatch];
        });
    }

    /**
     * Dislike a user's profile
     *
     * @param int $swiperId
     * @param int $swipedId
     * @return void
     */
    public function dislikeUser($swiperId, $swipedId)
    {
        // Simply record the dislike
        Swipe::updateOrCreate(
            [
                'swiper_id' => $swiperId,
                'swiped_id' => $swipedId
            ],
            [
                'type' => 'dislike',
                'matched' => false
            ]
        );
    }

    /**
     * Super like a user's profile
     *
     * @param int $swiperId
     * @param int $swipedId
     * @return array
     */
    public function superLikeUser($swiperId, $swipedId)
    {
        return DB::transaction(function () use ($swiperId, $swipedId) {
            // Check if the other user has already liked the current user
            $isMatch = false;
            $existingSwipe = Swipe::where('swiper_id', $swipedId)
                ->where('swiped_id', $swiperId)
                ->whereIn('type', ['like', 'superlike'])
                ->first();

            if ($existingSwipe) {
                $isMatch = true;
                // Update the existing swipe to mark it as a match
                $existingSwipe->update(['matched' => true]);
            }

            // Record the super like
            Swipe::updateOrCreate(
                [
                    'swiper_id' => $swiperId,
                    'swiped_id' => $swipedId
                ],
                [
                    'type' => 'superlike',
                    'matched' => $isMatch
                ]
            );

            return ['is_match' => $isMatch];
        });
    }

    /**
     * Get all matches for a user
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserMatches($userId, $page = 1, $perPage = 10)
    {
        // Get IDs of users that the current user has liked
        $likedUserIds = Swipe::where('swiper_id', $userId)
            ->where('type', 'like')
            ->where('matched', 1) // Only matched swipes
            ->pluck('swiped_id');
    
        // Get users who have also liked the current user (mutual match) and are marked matched = 1
        return User::whereIn('id', function ($query) use ($userId) {
                $query->select('swiper_id')
                    ->from('swipes')
                    ->where('swiped_id', $userId)
                    ->where('type', 'like')
                    ->where('matched', 1) // Only matched ones
                    ->whereIn('swiper_id', function ($q) use ($userId) {
                        $q->select('swiped_id')
                            ->from('swipes')
                            ->where('swiper_id', $userId)
                            ->where('type', 'like')
                            ->where('matched', 1); // Matched only
                    });
            })
            ->with('profile')
            ->orderBy('created_at', 'desc')
            ->paginate(
                $perPage,
                ['*'],
                'page',
                $page
            );
    }
    

    /**
     * Get users who have liked the current user
     *
     * @param int $userId
     * @param int $page
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
public function getLikesForUser($userId, $page = 1, $perPage = 10)
{
    // Get IDs of users who have liked the current user
    $likedByUserIds = Swipe::where('swiped_id', $userId)
        ->where('type', 'like')
		->where('matched', 0)
        ->pluck('swiper_id');

    return User::whereIn('id', $likedByUserIds)
        ->select('users.*')
        ->selectRaw("
            (
                SELECT matched 
                FROM swipes 
                WHERE swiper_id = users.id 
                  AND swiped_id = $userId
                  AND type = 'like'
                LIMIT 1
            ) AS is_match
        ")
        ->with('profile')
        ->orderBy('created_at', 'desc')
        ->paginate(
            $perPage,
            ['*'],
            'page',
            $page
        );
}


    /**
     * Check if there's a match between two users
     *
     * @param int $userId1
     * @param int $userId2
     * @return bool
     */
    public function checkIfMatchExists($userId1, $userId2): bool
    {
        // Check if user1 has liked user2
        $user1LikedUser2 = Swipe::where('swiper_id', $userId1)
            ->where('swiped_id', $userId2)
            ->whereIn('type', ['like', 'superlike'])
            ->exists();

        // Check if user2 has liked user1
        $user2LikedUser1 = Swipe::where('swiper_id', $userId2)
            ->where('swiped_id', $userId1)
            ->whereIn('type', ['like', 'superlike'])
            ->exists();

        return $user1LikedUser2 && $user2LikedUser1;
    }
}

function deg2rad($deg) {
    return $deg * pi() / 180;
}

function rad2deg($rad) {
    return $rad * 180 / pi();
}
