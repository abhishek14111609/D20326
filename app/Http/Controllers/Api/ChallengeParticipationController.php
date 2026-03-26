<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChallengeParticipationController extends Controller
{
    /**
     * Join a challenge
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $challengeId
     * @return \Illuminate\Http\Response
     */
    public function joinChallenge(Request $request, $challengeId)
    {
        $user = Auth::user();
        $challenge = Challenge::findOrFail($challengeId);

        // Check if user is already a participant
        if ($challenge->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already joined this challenge.'
            ], 400);
        }

        // Check if challenge is active
        if ($challenge->status !== 'active') {
			return response()->json([
				'status' => 'error',
				'message' => 'This challenge is not currently active.'
			], 400);
		}

        // Add user as participant
        $challenge->participants()->create([
			'user_id'   => $user->id,
			'status'    => 'joined',
			'joined_at' => now(),
			'notes'     => $request->notes
		]);
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully joined the challenge!',
            'data' => [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'status' => 'joined'
            ]
        ], 201);
    }

    /**
     * Get challenge participants
     *
     * @param  int  $challengeId
     * @return \Illuminate\Http\Response
     */
    public function getParticipants($challengeId)
    {
        $challenge = Challenge::findOrFail($challengeId);
        
        $participants = $challenge->participants()
					->leftjoin('users', 'users.id', '=', 'challenge_participants.user_id')
					->select([
						'users.id',
						'users.name',
						'users.email',
						'challenge_participants.status',
						'challenge_participants.joined_at',
						'challenge_participants.completed_at',
						'challenge_participants.notes'
					])
					->orderBy('challenge_participants.joined_at', 'desc')
					->paginate(10);


        return response()->json([
            'status' => 'success',
            'data' => $participants,
			
        ]);
    }

    /**
     * Get user's challenges
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserChallenges()
    {
        $user = Auth::user();
        
        $challenges = $user->challenges()
            ->select([
                'challenges.*',
                'challenge_participants.status as participation_status',
                'challenge_participants.joined_at',
                'challenge_participants.completed_at',
                'challenge_participants.notes as participation_notes'
            ])
            ->orderBy('challenge_participants.joined_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $challenges
        ]);
    }

    /**
     * Update participation status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $challengeId
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $challengeId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,completed,abandoned',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $challenge = Challenge::findOrFail($challengeId);

        $participation = $challenge->participants()
            ->where('user_id', $user->id)
            ->firstOrFail();

        $updateData = [
            'status' => $request->status,
            'notes' => $request->notes
        ];

        if ($request->status === 'completed') {
            $updateData['completed_at'] = now();
        }

        $participation->pivot->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Participation status updated successfully',
            'data' => [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'status' => $request->status
            ]
        ]);
    }

    /**
     * Leave a challenge
     *
     * @param  int  $challengeId
     * @return \Illuminate\Http\Response
     */
    public function leaveChallenge($challengeId)
    {
        $user = Auth::user();
        $challenge = Challenge::findOrFail($challengeId);

        // Check if user is a participant
        $participation = $challenge->participants()
            ->where('user_id', $user->id)
            ->first();

        if (!$participation) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant in this challenge.'
            ], 400);
        }

        // Remove user from participants
        $challenge->participants()->detach($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully left the challenge.',
            'data' => [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id
            ]
        ]);
    }
}
