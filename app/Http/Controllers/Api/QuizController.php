<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    Competition,
    CompetitionParticipant,
    Quiz,
    QuizParticipant,
    QuizQuestion,
    QuizAnswer
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /* =====================================================
     | JOIN COMPETITION & AUTO START QUIZ
     ===================================================== */

    public function index(Request $request)
    {
        $now = now();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        if (!$request->competitionId) {
            return response()->json([
                'status' => 'error',
                'message' => 'competition_id is required'
            ], 400);
        }

        $quizzes = Quiz::where('competition_id', $request->competitionId)
            ->where('is_active', 1)
            ->orderBy('start_time', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $quizzes->getCollection()->map(function ($quiz) use ($now) {
            return [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'description' => $quiz->description,
                'competition_id' => $quiz->competition_id,
                'start_time' => $quiz->start_time ?? '',
                'end_time' => $quiz->end_time ?? '',
                'duration_minutes' => $quiz->duration_minutes ?? '',
                'total_questions' => $quiz->questions()->count(),
                'max_score' => $quiz->questions()->sum('points'),
                'is_active' => $quiz->isActive(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'pagination' => [
                'total' => $quizzes->total(),
                'per_page' => $quizzes->perPage(),
                'current_page' => $quizzes->currentPage(),
                'last_page' => $quizzes->lastPage(),
            ]
        ]);
    }


    public function joinCompetition(Request $request, $competitionId)
    {
        $user = Auth::user();
        $now = now();

        $competition = Competition::findOrFail($competitionId);

        if ($now->lt($competition->registration_start) || $now->gt($competition->registration_end)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Competition not open'
            ], 400);
        }

        $participant = CompetitionParticipant::firstOrCreate(
            [
                'competition_id' => $competitionId,
                'user_id' => $user->id
            ],
            [
                'status' => 'registered',
                'metadata' => [
                    'joined_at' => $now,
                    'ip' => $request->ip()
                ]
            ]
        );

        $quiz = Quiz::where('competition_id', $competitionId)
            ->where('is_active', 1)
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->first();

        return response()->json([
            'status' => 'success',
            'competition' => $competition,
            'has_active_quiz' => (bool) $quiz,
            'quiz' => $quiz
        ]);
    }

    /* =====================================================
     | START QUIZ
     ===================================================== */
    public function startQuiz(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::with('questions')->findOrFail($quizId);

        if (!$quiz->isActive()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz not active'
            ], 400);
        }

        $competitionParticipant = CompetitionParticipant::where(
            'competition_id',
            $quiz->competition_id
        )->where('user_id', $user->id)->first();

        if (!$competitionParticipant) {
            return response()->json([
                'status' => 'error',
                'message' => 'Join competition first'
            ], 403);
        }

        $participant = QuizParticipant::firstOrCreate(
            [
                'quiz_id' => $quizId,
                'user_id' => $user->id
            ],
            [
                'competition_participant_id' => $competitionParticipant->id,
                'started_at' => now(),
                'status' => 'started',
                'total_questions' => $quiz->questions->count(),
                'score' => 0,
                'correct_answers' => 0,
                'time_taken' => 0
            ]
        );

        if ($participant->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Quiz already completed'
            ], 400);
        }

        return $this->quizState($quiz, $participant);
    }

    /* =====================================================
     | GET QUIZ STATE (REAL TIME)
     ===================================================== */
    public function getQuizState($quizId)
    {
        $user = Auth::user();

        $quiz = Quiz::findOrFail($quizId);

        $participant = QuizParticipant::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->first();

        if (!$participant) {
            return response()->json([
                'status' => 'error',
                'message' => 'User has not joined this quiz yet'
            ], 404);
        }

        return $this->quizState($quiz, $participant);
    }


    protected function quizState(Quiz $quiz, QuizParticipant $participant)
    {
        $elapsed = now()->diffInSeconds($participant->started_at);
        $limit = $quiz->duration_minutes * 60;
        $remain = max(0, $limit - $elapsed);

        if ($elapsed >= $limit && $participant->status === 'started') {
            return $this->completeQuizInternal($quiz, $participant);
        }

        $answeredIds = $participant->answers()->pluck('question_id');

        $question = QuizQuestion::where('quiz_id', $quiz->id)
            ->whereNotIn('id', $answeredIds)
            ->orderBy('order')
            ->first();

        return response()->json([
            'status' => 'success',
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'duration_minutes' => $quiz->duration_minutes ?? '',
                'time_remaining' => $this->formatDuration($remain),
                'status' => $participant->status
            ],
            'progress' => [
                'answered' => $answeredIds->count(),
                'total' => $participant->total_questions,
                'score' => $participant->score,
                'correct_answers' => $participant->correct_answers
            ],
            'current_question' => $question ? [
                'id' => $question->id,
                'question' => $question->question,
                'type' => $question->question_type,
                'options' => $question->options,
                'points' => $question->points,
                'time_limit' => $question->time_limit
            ] : null
        ]);
    }

    protected function formatDuration($seconds)
    {
        if ($seconds <= 0) {
            return '0 minutes';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '') . " {$minutes} minute" . ($minutes > 1 ? 's' : '');
        }

        if ($hours > 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '');
        }

        return "{$minutes} minute" . ($minutes > 1 ? 's' : '');
    }


    /* =====================================================
     | SUBMIT ANSWER (REAL TIME)
     ===================================================== */
    public function submitAllAnswers(Request $request, $quizId)
    {
        $user = Auth::user();
        $quiz = Quiz::findOrFail($quizId);

        $participant = QuizParticipant::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->where(function($query) {
                $query->where('status', 'started')
                      ->orWhere('status', 'completed');
            })
            ->firstOrFail();

        // ✅ normalize input (string OR array from Postman)
        $questionIds = array_map(
            'intval',
            $this->normalizeCommaInput($request->question_id ?? [])
        );

        $answers = $this->normalizeCommaInput($request->answer ?? []);

        $times = array_map(
            'intval',
            $this->normalizeCommaInput($request->time_taken ?? [])
        );

        $submittedAnswers = [];

        DB::transaction(function () use ($questionIds, $answers, $times, $participant, $quiz, &$submittedAnswers) {
            foreach ($questionIds as $index => $questionId) {

                // already answered skip
                if ($participant->answers()
                    ->where('question_id', $questionId)
                    ->exists()) {
                    continue;
                }

                $question = QuizQuestion::where('quiz_id', $quiz->id)
                    ->find($questionId);

                if (!$question) {
                    continue;
                }

                $userAnswer = $answers[$index] ?? null;
                $timeTaken = $times[$index] ?? 0;

                // sacho answer
                $correctAnswerIndexes = $question->correct_answer; // [0]
                $options = $question->options; // ["hfggf","fgfg",...]

                $correctAnswerText = [];
                if (is_array($correctAnswerIndexes) && is_array($options)) {
                    foreach ($correctAnswerIndexes as $idx) {
                        if (isset($options[$idx])) {
                            $correctAnswerText[] = $options[$idx];
                        }
                    }
                }

                // ✅ compare user answer with correctAnswerText
                $isCorrect = false;

                if ($userAnswer !== null) {
                    if (count($correctAnswerText) === 1) {
                        // single correct
                        $isCorrect = ($userAnswer == $correctAnswerText[0]);
                    } else {
                        // multi correct (userAnswer comma separated)
                        $userAnswers = is_array($userAnswer) ? $userAnswer : explode(',', $userAnswer);
                        sort($userAnswers);
                        $tmp = $correctAnswerText;
                        sort($tmp);
                        $isCorrect = ($userAnswers == $tmp);
                    }
                }


                //$isCorrect = $question->isAnswerCorrect([$userAnswer]);

                QuizAnswer::create([
                    'quiz_participant_id' => $participant->id,
                    'question_id' => $questionId,
                    'answer' => [$userAnswer],
                    'is_correct' => $isCorrect,
                    'time_taken' => $timeTaken,
                    'points_earned' => $isCorrect ? $question->points : 0
                ]);

                if ($isCorrect) {
                    $participant->increment('score', $question->points);
                    $participant->increment('correct_answers');
                }

                // response mate
                $submittedAnswers[] = [
                    'question_id' => $questionId,
                    'user_answer' => $userAnswer,
                    'correct_answer' => $correctAnswerText, // 🔥 TEXT now
                    'is_correct' => $isCorrect
                ];
            }
        });

        // quiz complete check
        if ($participant->answers()->count() >= $participant->total_questions) {
            $participant->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz submitted successfully',
            'result' => $this->buildResult($participant->fresh(), $quiz),
            'submitted_answers' => $submittedAnswers
        ]);
    }

    // =========================
    // 🔹 Helper: normalize input
    // =========================
    private function normalizeCommaInput($input): array
    {
        // Postman field[] ⇒ array
        if (is_array($input)) {
            $input = implode(',', $input);
        }

        if (is_null($input) || $input === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $input)
        )));
    }

    // =========================
    // 🔹 Result builder
    // =========================
    private function buildResult(QuizParticipant $participant, Quiz $quiz): array
    {
        $totalQuestions = $participant->total_questions;
        $attempted = $participant->answers()->count();
        $correct = $participant->answers()->where('is_correct', true)->count(); // dynamic
        $wrong = $attempted - $correct;
        $score = $participant->answers()->sum('points_earned'); // dynamic
        $maxScore = $quiz->questions()->sum('points');

        $percentage = $maxScore > 0
            ? round(($score / $maxScore) * 100, 2)
            : 0;

        return [
            'total_questions' => $totalQuestions,
            'attempted' => $attempted,
            'correct' => $correct,
            'wrong' => $wrong,
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => $percentage,
            'status' => $participant->status,
            'passed' => $percentage >= ($quiz->pass_percentage ?? 0),
        ];
    }



    public function completeQuiz(Request $request, $quizId)
    {
        $user = Auth::user();

        $quiz = Quiz::findOrFail($quizId);

        $participant = QuizParticipant::where('quiz_id', $quizId)
            ->where('user_id', $user->id)
            ->where('status', 'started')
            ->first();

        if (!$participant) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active quiz session found'
            ], 404);
        }

        return $this->completeQuizInternal($quiz, $participant);
    }


    /* =====================================================
     | COMPLETE QUIZ (SINGLE SOURCE)
     ===================================================== */
    protected function completeQuizInternal(Quiz $quiz, QuizParticipant $participant)
    {
        DB::transaction(function () use ($participant) {
            $participant->update([
                'status' => 'completed',
                'completed_at' => now(),
                'time_taken' => now()->diffInSeconds($participant->started_at)
            ]);
        });

        $rank = QuizParticipant::where('quiz_id', $quiz->id)
            ->where('score', '>', $participant->score)
            ->count() + 1;

        $percentage = $participant->total_questions
            ? round(($participant->correct_answers / $participant->total_questions) * 100)
            : 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz completed',
            'result' => [
                'score' => $participant->score,
                'correct_answers' => $participant->correct_answers,
                'total_questions' => $participant->total_questions,
                'time_taken' => $participant->time_taken,
                'percentage' => $percentage,
                'rank' => $rank
            ]
        ]);
    }
}
