<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LeaderboardService
{
    /**
     * Cache duration in seconds
     */
    protected $cacheDuration = 300; // 5 minutes

    /**
     * Get the leaderboard with pagination support
     * 
     * @param string $type Type of leaderboard (daily, weekly, monthly, all_time)
     * @param int $limit Number of items per page
     * @param int $offset Offset for pagination
     * @return \Illuminate\Support\Collection
     */
    public function getLeaderboard(string $type = 'weekly', int $limit = 10, int $offset = 0): Collection
    {
        $cacheKey = "leaderboard_{$type}_{$limit}_{$offset}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($type, $limit, $offset) {
            // First, get the base leaderboard query
            $results = DB::table('users')
                ->leftJoin('user_points', function ($join) use ($type) {
                    $join->on('users.id', '=', 'user_points.user_id')
                         ->whereRaw($this->getDateRangeCondition($type, 'user_points'));
                })
                ->leftJoin('competition_participants', 'users.id', '=', 'competition_participants.user_id')
                ->leftJoin('competitions', 'competition_participants.competition_id', '=', 'competitions.id')
                ->select([
                    'users.id as user_id',
                    'users.name',
                    'users.avatar',
                    DB::raw('COALESCE(SUM(user_points.points), 0) as points'),
                    // Competition statistics
                    DB::raw('COUNT(DISTINCT competition_participants.competition_id) as total_competitions'),
                    DB::raw('SUM(CASE WHEN competition_participants.rank = 1 THEN 1 ELSE 0 END) as total_wins'),
                    DB::raw('(SELECT COUNT(*) FROM competition_participants cp WHERE cp.user_id = users.id) as total_participations'),
                    DB::raw('(SELECT COUNT(*) FROM competition_participants cp 
                             WHERE cp.user_id = users.id AND cp.rank > 1) as total_losses')
                ])
                ->where('users.status', 'active')
                ->groupBy('users.id', 'users.name', 'users.avatar')
                ->orderByDesc('points')
                ->orderBy('name')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($user, $index) use ($offset) {
                    return (object)[
                        'rank' => $offset + $index + 1,
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'avatar' => $user->avatar
							? url('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $user->avatar)
							: "",
                        'points' => (int)$user->points,
                        'stats' => [
                            'total_competitions' => (int)$user->total_competitions,
                            'total_participations' => (int)$user->total_participations,
                            'total_wins' => (int)$user->total_wins,
                            'total_losses' => (int)$user->total_losses,
                            'win_rate' => $user->total_participations > 0 
                                ? round(($user->total_wins / $user->total_participations) * 100, 2) 
                                : 0
                        ]
                    ];
                });

            if ($results->isEmpty()) {
                $results = collect($this->getDemoLeaderboardData($limit))
                    ->map(fn($user, $index) => (object) array_merge($user, [
                        'position' => $offset + $index + 1,
                        'rank' => $offset + $index + 1
                    ]));
            }

            return $results;
        });
    }
	
	public function getUserLeaderboardData(int $userId, string $type = 'weekly')
{
    $query = DB::table('users')
        ->leftJoin('user_points', function ($join) use ($type) {
            $join->on('users.id', '=', 'user_points.user_id')
                 ->whereRaw($this->getDateRangeCondition($type, 'user_points'));
        })
        ->leftJoin('competition_participants', 'users.id', '=', 'competition_participants.user_id')
        ->select([
            'users.id as user_id',
            'users.name',
            'users.avatar',
            DB::raw('COALESCE(SUM(user_points.points), 0) as points'),
            DB::raw('COUNT(DISTINCT competition_participants.competition_id) as total_competitions'),
            DB::raw('SUM(CASE WHEN competition_participants.rank = 1 THEN 1 ELSE 0 END) as total_wins'),
            DB::raw('(SELECT COUNT(*) FROM competition_participants cp WHERE cp.user_id = users.id) as total_participations'),
            DB::raw('(SELECT COUNT(*) FROM competition_participants cp WHERE cp.user_id = users.id AND cp.rank > 1) as total_losses')
        ])
        ->where('users.id', $userId)
        ->groupBy('users.id', 'users.name', 'users.avatar')
        ->first();

    if (!$query) {
        return null;
    }

    // Calculate rank
    $rank = DB::table('users')
        ->leftJoin('user_points', function ($join) use ($type) {
            $join->on('users.id', '=', 'user_points.user_id')
                 ->whereRaw($this->getDateRangeCondition($type, 'user_points'));
        })
        ->select(DB::raw('users.id, COALESCE(SUM(user_points.points), 0) as total_points'))
        ->where('users.status', 'active')
        ->groupBy('users.id')
        ->orderByDesc('total_points')
        ->pluck('id')
        ->search($userId);

    return (object)[
        'rank' => $rank !== false ? $rank + 1 : null,
        'user_id' => $query->user_id,
        'name' => $query->name,
        'avatar' => $query->avatar
        ? url('https://duos.webvibeinfotech.in/storage/app/public/avatars/' . $query->avatar)
        : "",
        'points' => (int)$query->points,
        'stats' => [
            'total_competitions' => (int)$query->total_competitions,
            'total_participations' => (int)$query->total_participations,
            'total_wins' => (int)$query->total_wins,
            'total_losses' => (int)$query->total_losses,
            'win_rate' => $query->total_participations > 0
                ? round(($query->total_wins / $query->total_participations) * 100, 2)
                : 0
        ]
    ];
}


    /**
     * Generate demo leaderboard data
     */
    protected function getDemoLeaderboardData(int $limit = 10)
    {
        $demoUsers = [];
        $names = [
            'Alex & Taylor', 'Maria & Carlos', 'James & Sarah', 'John & Jane', 'David & Emily',
            'Emma & William', 'Michael & Sophia', 'Olivia & Noah', 'Ava & Liam', 'Isabella & Lucas'
        ];
        
        $avatars = [
            'https://randomuser.me/api/portraits/men/1.jpg',
            'https://randomuser.me/api/portraits/women/1.jpg',
            'https://randomuser.me/api/portraits/men/2.jpg',
            'https://randomuser.me/api/portraits/women/2.jpg',
            'https://randomuser.me/api/portraits/men/3.jpg',
            'https://randomuser.me/api/portraits/women/3.jpg',
            'https://randomuser.me/api/portraits/men/4.jpg',
            'https://randomuser.me/api/portraits/women/4.jpg',
            'https://randomuser.me/api/portraits/men/5.jpg',
            'https://randomuser.me/api/portraits/women/5.jpg',
        ];

        for ($i = 0; $i < $limit && $i < count($names); $i++) {
            $demoUsers[] = [
                'rank' => $i + 1,
                'position' => $i + 1,
                'user_id' => 1000 + $i,
                'name' => $names[$i],
                'photo' => $avatars[$i],
                'points' => rand(1000, 10000),
                'is_current_user' => false
            ];
        }

        return $demoUsers;
    }

    /**
     * Get user position
     */
    public function getUserPosition(int $userId, string $type = 'weekly')
    {
        $cacheKey = "leaderboard_position_{$type}_{$userId}";
        
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($userId, $type) {
            // Get user's score and rank
            $userRanking = DB::table('users')
                ->select([
                    'users.id',
                    'users.name',
                    'users.user_name',
                    'users.avatar',
                    'users.is_verified',
                    DB::raw('COALESCE(leaderboard.score, 0) as score'),
                    DB::raw('(SELECT COUNT(*) + 1 FROM users u2 LEFT JOIN user_points up2 ON u2.id = up2.user_id AND ' . $this->getDateRangeCondition($type, 'up2') . ' WHERE COALESCE(up2.points, 0) > COALESCE(leaderboard.score, 0)) as position')
                ])
                ->leftJoin(
                    DB::raw("(SELECT user_id, SUM(points) as score FROM user_points WHERE " . $this->getDateRangeCondition($type) . " GROUP BY user_id) as leaderboard"),
                    'users.id',
                    '=','leaderboard.user_id'
                )
                ->where('users.id', $userId)
                ->first();

            if (!$userRanking) {
                return null;
            }

            // Get total users for percentage calculation
            $totalUsers = User::where('is_active', true)->count();
            $topPercentage = $totalUsers > 0 ? round(($userRanking->position / $totalUsers) * 100, 2) : 0;

            return [
                'position' => (int) $userRanking->position,
                'score' => (float) $userRanking->score,
                'user' => [
                    'id' => $userRanking->id,
                    'name' => $userRanking->name,
                    'user_name' => $userRanking->user_name,
                    'avatar' => $userRanking->avatar,
                    'is_verified' => (bool) $userRanking->is_verified,
                ],
                'top_percentage' => min(100, max(0, 100 - $topPercentage)) // Invert to get top X%
            ];
        });
    }

    /**
     * Get leaderboard positions around the specified user.
     *
     * @param int $userId
     * @param string $type
     * @param int $limit Number of positions to return (should be odd to center on user)
     * @return array
     */
    public function getPositionsAroundUser(int $userId, string $type = 'weekly', int $limit = 5): array
    {
        // Ensure limit is odd to center on the user
        $limit = $limit % 2 === 0 ? $limit + 1 : $limit;
        $halfLimit = (int) floor($limit / 2);

        // Get user's position
        $userPosition = $this->getUserPosition($userId, $type);
        if (!$userPosition) {
            return [
                'user_position' => null,
                'entries' => []
            ];
        }

        $userRank = $userPosition['position'];
        
        // Calculate range of positions to fetch
        $startPos = max(1, $userRank - $halfLimit);
        $endPos = $startPos + $limit - 1;

        // Adjust if we're near the end of the leaderboard
        $totalUsers = User::where('is_active', true)->count();
        if ($endPos > $totalUsers) {
            $endPos = $totalUsers;
            $startPos = max(1, $endPos - $limit + 1);
        }

        // Get users in the calculated range
        $entries = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.user_name',
                'users.avatar',
                'users.is_verified',
                DB::raw('COALESCE(leaderboard.score, 0) as score'),
                DB::raw('ROW_NUMBER() OVER (ORDER BY COALESCE(leaderboard.score, 0) DESC) as position')
            ])
            ->leftJoin(
                DB::raw("(SELECT user_id, SUM(points) as score FROM user_points WHERE " . $this->getDateRangeCondition($type) . " GROUP BY user_id) as leaderboard"),
                'users.id',
                '=','leaderboard.user_id'
            )
            ->where('users.is_active', true)
            ->having('position', '>=', $startPos)
            ->having('position', '<=', $endPos)
            ->orderBy('position')
            ->get()
            ->map(function ($user) {
                return [
                    'position' => (int) $user->position,
                    'score' => (float) $user->score,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'user_name' => $user->user_name,
                        'avatar' => $user->avatar,
                        'is_verified' => (bool) $user->is_verified,
                    ]
                ];
            });

        return [
            'user_position' => $userRank,
            'entries' => $entries
        ];
    }

    /**
     * Get leaderboard rewards information.
     *
     * @param string $type
     * @return array
     */
    public function getLeaderboardRewards(string $type = 'weekly'): array
    {
        $rewards = [
            'daily' => [
                [
                    'position_start' => 1,
                    'position_end' => 1,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 1000],
                        ['type' => 'premium_badge', 'duration' => 24], // hours
                    ]
                ],
                [
                    'position_start' => 2,
                    'position_end' => 3,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 500],
                    ]
                ],
                [
                    'position_start' => 4,
                    'position_end' => 10,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 250],
                    ]
                ]
            ],
            'weekly' => [
                [
                    'position_start' => 1,
                    'position_end' => 1,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 5000],
                        ['type' => 'premium_badge', 'duration' => 7 * 24], // hours
                        ['type' => 'boosts', 'amount' => 5],
                    ]
                ],
                [
                    'position_start' => 2,
                    'position_end' => 3,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 2500],
                        ['type' => 'boosts', 'amount' => 3],
                    ]
                ],
                [
                    'position_start' => 4,
                    'position_end' => 10,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 1000],
                        ['type' => 'boosts', 'amount' => 1],
                    ]
                ]
            ],
            'monthly' => [
                [
                    'position_start' => 1,
                    'position_end' => 1,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 20000],
                        ['type' => 'premium_badge', 'duration' => 30 * 24], // hours
                        ['type' => 'boosts', 'amount' => 20],
                        ['type' => 'exclusive_badge', 'duration' => 'permanent'],
                    ]
                ],
                [
                    'position_start' => 2,
                    'position_end' => 3,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 10000],
                        ['type' => 'premium_badge', 'duration' => 15 * 24], // hours
                        ['type' => 'boosts', 'amount' => 10],
                    ]
                ],
                [
                    'position_start' => 4,
                    'position_end' => 10,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 5000],
                        ['type' => 'boosts', 'amount' => 5],
                    ]
                ]
            ],
            'all_time' => [
                [
                    'position_start' => 1,
                    'position_end' => 1,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 100000],
                        ['type' => 'premium_badge', 'duration' => 'permanent'],
                        ['type' => 'exclusive_badge', 'duration' => 'permanent'],
                        ['type' => 'custom_title', 'title' => 'Champion'],
                    ]
                ],
                [
                    'position_start' => 2,
                    'position_end' => 3,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 50000],
                        ['type' => 'premium_badge', 'duration' => 30 * 24], // hours
                        ['type' => 'custom_title', 'title' => 'Elite'],
                    ]
                ],
                [
                    'position_start' => 4,
                    'position_end' => 10,
                    'rewards' => [
                        ['type' => 'coins', 'amount' => 25000],
                        ['type' => 'premium_badge', 'duration' => 7 * 24], // hours
                    ]
                ]
            ]
        ];

        // Calculate time until end of current period
        $now = now();
        $endsIn = 0;
        
        switch ($type) {
            case 'daily':
                $endOfDay = $now->copy()->endOfDay();
                $endsIn = $now->diffInSeconds($endOfDay);
                break;
                
            case 'weekly':
                $endOfWeek = $now->copy()->endOfWeek();
                $endsIn = $now->diffInSeconds($endOfWeek);
                break;
                
            case 'monthly':
                $endOfMonth = $now->copy()->endOfMonth();
                $endsIn = $now->diffInSeconds($endOfMonth);
                break;
                
            case 'all_time':
                // For all_time, we can return a large number or handle it differently
                $endsIn = PHP_INT_MAX;
                break;
        }

        return [
            'type' => $type,
            'ends_in' => $endsIn,
            'rewards' => $rewards[$type] ?? []
        ];
    }

    /**
     * Get the SQL condition for the specified date range.
     *
     * @param string $type
     * @param string $tableAlias
     * @return string
     */
    protected function getDateRangeCondition(string $type, string $tableAlias = 'user_points'): string
    {
        $now = now();
        $tablePrefix = $tableAlias ? "{$tableAlias}." : '';
        
        switch ($type) {
            case 'daily':
                $startOfDay = $now->copy()->startOfDay()->toDateTimeString();
                return "{$tablePrefix}created_at >= '{$startOfDay}'";
                
            case 'weekly':
                $startOfWeek = $now->copy()->startOfWeek()->toDateTimeString();
                return "{$tablePrefix}created_at >= '{$startOfWeek}'";
                
            case 'monthly':
                $startOfMonth = $now->copy()->startOfMonth()->toDateTimeString();
                return "{$tablePrefix}created_at >= '{$startOfMonth}'";
                
            case 'all_time':
            default:
                return '1=1'; // All time
        }
    }
}
