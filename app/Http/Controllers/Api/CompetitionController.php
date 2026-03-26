<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Http\Resources\Api\CompetitionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Quiz;
class CompetitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            Log::info('Fetching all competitions');
            
            // Check if the competitions table exists
            if (!Schema::hasTable('competitions')) {
                Log::error('Competitions table does not exist');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competitions feature is not properly configured.'
                ], 500);
            }

            // Get pagination parameters
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);
            
            // Base query
             $query = Competition::query();
            
            // Apply filters if provided
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }
            
            if ($request->has('type')) {
                $query->where('competition_type', $request->input('type'));
            }

            // Get paginated results
            $competitions = $query->orderBy('start_date', 'desc')
                                ->paginate($perPage, ['*'], 'page', $page);

            // Manually load the participants count for each competition
            $competitions->getCollection()->transform(function ($competition) {
                $competition->participants_count = $competition->active_participants_count;
                return $competition;
            });
            // Return the paginated results using the resource
            return response()->json([
                'status' => 'success',
                'data' => CompetitionResource::collection($competitions),
                'pagination' => [
                'total' => $competitions->total(),
                'count' => $competitions->count(),
                'total_pages' => ceil($competitions->total() / $perPage),
                'current_page' => $competitions->currentPage(),
                'from' => $competitions->firstItem() ?? 0,
                'last_page' => $competitions->lastPage(),
                'per_page' => $competitions->perPage(),
                'to' => $competitions->lastItem() ?? 0,
            ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching competitions: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch competitions. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            Log::info('Fetching competition', ['competition_id' => $id]);
            
            // Check if the competitions table exists
            if (!Schema::hasTable('competitions')) {
                Log::error('Competitions table does not exist');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competitions feature is not properly configured.'
                ], 500);
            }

            // Find the competition with participants count
            $competition = Competition::withCount('participants')
                ->find($id);

            // If competition not found
            if (!$competition) {
                Log::warning('Competition not found', ['competition_id' => $id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competition not found.'
                ], 404);
            }

            Log::info('Competition found', ['competition_id' => $id]);

            return response()->json([
                'status' => 'success',
                'data' => new CompetitionResource($competition)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch competition: ' . $e->getMessage(), [
                'competition_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch competition. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Display a listing of active competitions.
     *
     * @return JsonResponse
     */
    public function active(Request $request): JsonResponse
    {
        try {
            Log::info('Fetching active competitions');
            
            // Check if the competitions table exists
            if (!Schema::hasTable('competitions')) {
                Log::error('Competitions table does not exist');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competitions feature is not properly configured.'
                ], 500);
            }

            // Get pagination parameters
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $page = $request->input('page', 1);
            
            $now = now();
            
            // Get active competitions with pagination
            $competitions = Competition::where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->withCount('participants')
                ->orderBy('start_date', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info('Found ' . $competitions->total() . ' active competitions in total');

            return response()->json([
                'status' => 'success',
                'data' => CompetitionResource::collection($competitions),
                'pagination' => [
                    'total' => $competitions->total(),
                    'count' => $competitions->count(),
                    'per_page' => $competitions->perPage(),
                    'current_page' => $competitions->currentPage(),
                    'total_pages' => $competitions->lastPage(),
                    'from' => $competitions->firstItem() ?? 0,
                    'last_page' => $competitions->lastPage(),
                    'per_page' => $competitions->perPage(),
                    'to' => $competitions->lastItem() ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch active competitions: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch active competitions. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Join a competition
     *
     * @param int $id Competition ID
     * @return JsonResponse
     */
    public function join($id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $user = Auth::user();
            Log::info('User attempting to join competition', [
                'user_id' => $user->id,
                'competition_id' => $id
            ]);

            // Check if competition exists
            $competition = Competition::find($id);
            if (!$competition) {
                Log::warning('Competition not found', ['competition_id' => $id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competition not found.'
                ], 404);
            }

            // Check if registration is open
            $now = now();
			
			$quizzes = Quiz::where('competition_id', $competition->id)
            ->where('is_active', 1)
            ->orWhere('start_time', '<=', $now)
            ->orWhere('end_time', '>=', $now)
            ->first();
			
			if (!$quizzes) {
				return response()->json([
					'status' => 'error',
					'message' => 'No active quiz available for this competition.'
				], 404);
			}
			
			$quiz = Quiz::with('questions')->findOrFail($quizzes->id);
			
			if (!$quiz) {
				return response()->json([
					'status' => 'error',
					'message' => 'No active quiz available for this competition.'
				], 404);
			}
			
            if ($competition->registration_start && $now->lt($competition->registration_start)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration for this competition has not started yet.'
                ], 400);
            }

            if ($competition->registration_end && $now->gt($competition->registration_end)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Registration for this competition has ended.'
                ], 400);
            }

            // Check if user is already a participant
            $existingParticipant = CompetitionParticipant::where('competition_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingParticipant) {
                // If participant exists, update their submission time
                $existingParticipant->update([
                    'submitted_at' => now(),
                    'score' => 0, // Reset score
                    'rank' => 0   // Reset rank
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully updated your competition entry!',
                    'data' => [
                        //'competition_id' => $competition->id,
                        //'title' => $competition->title,
                        //'joined_at' => now()->toDateTimeString()
						'competition' => $competition,
						'quiz' => $quiz
                    ]
                ]);
            }

            // Check if competition has available slots
            if ($competition->max_participants) {
                $currentParticipants = CompetitionParticipant::where('competition_id', $id)
                    ->count();
                
                if ($currentParticipants >= $competition->max_participants) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This competition has reached its maximum number of participants.'
                    ], 400);
                }
            }

            // Create new participant with required fields
            $now = now();
            $participant = new CompetitionParticipant([
                'competition_id' => $competition->id,
                'user_id' => $user->id,
                'score' => 0, // Default score
                'rank' => 0,  // Default rank
                'submitted_at' => $now,
            ]);

            $participant->save();
            
            DB::commit();

            Log::info('User successfully joined competition', [
                'user_id' => $user->id,
                'competition_id' => $id,
                'participant_id' => $participant->id
            ]);
			

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully joined the competition!',
                'data' => [
                    //'competition_id' => $competition->id,
                    //'title' => $competition->title,
                    //'joined_at' => $now->toDateTimeString()
					'competition' => $competition,
					'quiz' => $quiz
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to join competition: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'competition_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to join competition. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get competition leaderboard
     *
     * @param int $id Competition ID
     * @return JsonResponse
     */
    public function leaderboard($id, Request $request): JsonResponse
    {
        try {
           // Log::info('Fetching competition leaderboard', ['competition_id' => $id]);
            
            // Get pagination parameters
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $page = $request->input('page', 1);
            
            // Check if competition exists
            $competition = Competition::find($id);
            if (!$competition) {
               // Log::warning('Competition not found', ['competition_id' => $id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competition not found.'
                ], 404);
            }

            // Get participants with user details, ordered by score (descending) and created_at (ascending)
            $participants = CompetitionParticipant::with(['user' => function($query) {
                    $query->select('id', 'name');
                }])
                ->where('competition_id', $id)
                ->orderBy('score', 'desc')
                ->orderBy('created_at', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Add rank to each participant
            $rank = (($page - 1) * $perPage) + 1; // Calculate starting rank based on page
            $leaderboard = $participants->map(function($participant) use (&$rank) {
                $data = [
                    'rank' => $rank++,
                    'user_id' => $participant->user_id,
                    'name' => $participant->user->name ?? '',
                    'score' => (int)$participant->score,
                    'joined_at' => $participant->created_at->toDateTimeString()
                ];

                // Update participant's rank in the database
                if ($participant->rank !== $data['rank']) {
                    $participant->update(['rank' => $data['rank']]);
                }
                
                return $data;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'competition_id' => $competition->id,
                    'title' => $competition->title,
                    'leaderboard' => $leaderboard,
                    'total_participants' => $participants->total(),
                    'pagination' => [
                        'total' => $participants->total(),
                        'count' => $participants->count(),
                        'per_page' => $participants->perPage(),
                        'current_page' => $participants->currentPage(),
                        'total_pages' => $participants->lastPage(),
                        'from' => $participants->firstItem() ?? 0,
                        'last_page' => $participants->lastPage(),
                        'per_page' => $participants->perPage(),
                        'to' => $participants->lastItem() ?? 0,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch competition leaderboard: ' . $e->getMessage(), [
                'competition_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch competition leaderboard. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get the leaderboard for a specific competition
     *
     * @param int $id Competition ID
     * @return JsonResponse
     */
    public function getLeaderboard($id): JsonResponse
    {
        try {
            $competition = Competition::findOrFail($id);
            
            // Get participants ordered by score (descending) and joined_at (ascending)
            $leaderboard = $competition->participants()
                ->select([
                    'users.id',
                    'users.name',
                    'users.profile_image',
                    'competition_participants.score',
                    'competition_participants.joined_at',
                    DB::raw('RANK() OVER (ORDER BY competition_participants.score DESC, competition_participants.joined_at ASC) as rank')
                ])
                ->join('users', 'users.id', '=', 'competition_participants.user_id')
                ->where('competition_participants.status', 'active')
                ->orderBy('score', 'desc')
                ->orderBy('joined_at', 'asc')
                ->limit(100) // Limit to top 100
                ->get();

            // Add prize information if available
            $prizes = [];
            if ($competition->prize_distribution) {
                $prizes = json_decode($competition->prize_distribution, true);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'competition' => new CompetitionResource($competition),
                    'leaderboard' => $leaderboard->map(function ($entry) use ($prizes) {
                        $entryArray = $entry->toArray();
                        // Add prize information if this rank has a prize
                        if (isset($prizes[$entryArray['rank']])) {
                            $entryArray['prize'] = $prizes[$entryArray['rank']];
                        }
                        return $entryArray;
                    }),
                    'current_user_position' => $this->getUserRank($competition->id, Auth::id()),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch competition leaderboard.'
            ], 500);
        }
    }

    /**
     * Get user's rank in the competition
     *
     * @param int $competitionId
     * @param int $userId
     * @return array|null
     */
    private function getUserRank($competitionId, $userId)
    {
        $userRank = CompetitionParticipant::select([
                'user_id',
                'score',
                'joined_at',
                DB::raw('(SELECT COUNT(*) + 1 FROM competition_participants cp2 WHERE cp2.competition_id = competition_participants.competition_id AND (cp2.score > competition_participants.score OR (cp2.score = competition_participants.score AND cp2.joined_at < competition_participants.joined_at))) as rank')
            ])
            ->where('competition_id', $competitionId)
            ->where('user_id', $userId)
            ->first();

        return $userRank ? [
            'rank' => (int)$userRank->rank,
            'score' => $userRank->score,
            'total_participants' => CompetitionParticipant::where('competition_id', $competitionId)->count()
        ] : null;
    }

    /**
     * Get participants for a specific competition
     *
     * @param int $id Competition ID
     * @return JsonResponse
     */
    public function participants($id, Request $request): JsonResponse
    {
        try {
            Log::info('Fetching competition participants', ['competition_id' => $id]);
            
            // Get pagination parameters
            $perPage = $request->input('per_page', 10); // Default to 10 items per page
            $page = $request->input('page', 1);
            
            // Check if competition exists
            $competition = Competition::find($id);
            if (!$competition) {
                Log::warning('Competition not found', ['competition_id' => $id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Competition not found.'
                ], 404);
            }

            // Get participants with user details and pagination
            $participants = CompetitionParticipant::with(['user' => function($query) {
                    $query->select('id', 'name');
                }])
                ->where('competition_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            $participantsData = $participants->map(function($participant) {
                return [
                    'id' => $participant->id,
                    'user_id' => $participant->user_id,
                    'name' => $participant->user->name ?? '',
                    'score' => (int)$participant->score,
                    'rank' => $participant->rank ?? 0,
                    'joined_at' => $participant->created_at->toDateTimeString()
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'competition_id' => $competition->id,
                    'title' => $competition->title,
                    'participants' => $participantsData,
                    'total_participants' => $participants->total(),
                    'pagination' => [
                        'total' => $participants->total(),
                        'count' => $participants->count(),
                        'per_page' => $participants->perPage(),
                        'current_page' => $participants->currentPage(),
                        'total_pages' => $participants->lastPage(),
                        'from' => $participants->firstItem() ?? 0,
                        'last_page' => $participants->lastPage(),
                        'per_page' => $participants->perPage(),
                        'to' => $participants->lastItem() ?? 0,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch competition participants: ' . $e->getMessage(), [
                'competition_id' => $id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch competition participants. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
