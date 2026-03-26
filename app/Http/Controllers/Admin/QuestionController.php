<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Display a listing of the quiz questions.
     */
    public function index(Competition $competition, Quiz $quiz)
    {
        $questions = $quiz->questions()->orderBy('order')->get();
        return view('admin.quizzes.show', compact('competition', 'quiz', 'questions'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(Competition $competition, Quiz $quiz)
    {
        return view('admin.quizzes.questions.create', compact('competition', 'quiz'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request, $competitionId, $quizId)
    {
        \Log::info('Question submit', $request->all());

        $rules = [
            'question' => 'required|string|max:1000',
            'type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'points' => 'required|integer|min:1',
            'time_limit' => 'nullable|integer|min:0',
			'explanation' => 'nullable|string',
        ];

        if (in_array($request->type, ['multiple_choice', 'true_false'])) {
            $rules['options'] = 'required|array|min:2';
            $rules['options.*'] = 'required|string|max:255';
            $rules['correct_answers'] = 'required|array|min:1';
            $rules['correct_answers.*'] = 'integer';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();

        try {
            $order = DB::table('quiz_questions')
                ->where('quiz_id', $quizId)
                ->count() + 1;

            // =========================
            // OPTIONS + CORRECT ANSWERS
            // =========================
            $options = [];
            $correctAnswer = [];

            if ($validated['type'] === 'multiple_choice') {
                foreach ($validated['options'] as $index => $text) {
                    $options[] = $text;
                    if (in_array($index, $validated['correct_answers'])) {
                        $correctAnswer[] = $index;
                    }
                }
            }

            if ($validated['type'] === 'true_false') {
                $options = ['True', 'False'];
                $correctAnswer = $validated['correct_answers'];
            }

            // =========================
            // FINAL INSERT (TABLE MATCH)
            // =========================
            DB::table('quiz_questions')->insert([
                'quiz_id' => $quizId,
                'question' => $validated['question'],
                'question_type' => $validated['type'], // ✅ MATCHED
                'options' => json_encode($options),
                'correct_answer' => json_encode($correctAnswer),
                'points' => $validated['points'],
                'time_limit' => $validated['time_limit'] ?? 0,
                'order' => $order,
				'explanation' => $validated['explanation'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.competitions.quizzes.show', [$competitionId, $quizId])
                ->with('success', 'Question added successfully');

        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage()); // agar future ma fail thay to exact error
        }
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Competition $competition, Quiz $quiz, QuizQuestion $question)
    {
        return view('admin.quizzes.questions.edit', compact('competition', 'quiz', 'question'));
    }

    /**
     * Update the specified question in storage.
     */
 public function update(Request $request, $competitionId, $quizId, $questionId)
    {
        try {
            $rules = [
                'question' => 'required|string|max:1000',
                'type' => 'required|in:multiple_choice,true_false,short_answer,essay',
                'points' => 'required|integer|min:1',
                'time_limit' => 'nullable|integer|min:0',
				'explanation' => 'nullable|string',
            ];

            if (in_array($request->type, ['multiple_choice', 'true_false'])) {
                $rules['options'] = 'required|array|min:2';
                $rules['options.*'] = 'required|string|max:255';
                $rules['correct_answers'] = 'required|array|min:1';
                $rules['correct_answers.*'] = 'integer';
            }

            $validated = $request->validate($rules);

            // Prepare update data
            $updateData = [
                'question' => $validated['question'],
                'type' => $validated['type'],
                'points' => $validated['points'],
                'time_limit' => $validated['time_limit'] ?? 0,
				'explanation' => $validated['explanation'],
                'updated_at' => now(),
            ];

            // Handle options and correct answers
            if (in_array($validated['type'], ['multiple_choice', 'true_false'])) {
                $options = [];
                $correctAnswers = [];
                
                foreach ($validated['options'] as $index => $option) {
                    $options[] = $option;
                    if (in_array($index, $validated['correct_answers'] ?? [])) {
                        $correctAnswers[] = $index;
                    }
                }
                
                $updateData['options'] = json_encode($options);
                $updateData['correct_answer'] = json_encode($correctAnswers);
            } else {
                $updateData['options'] = null;
                $updateData['correct_answer'] = null;
            }

            // Update the question
            $updated = DB::table('quiz_questions')
                ->where('id', $questionId)
                ->where('quiz_id', $quizId)
                ->update($updateData);

            if ($updated) {
                return redirect()
                    ->route('admin.competitions.quizzes.show', [$competitionId, $quizId])
                    ->with('success', 'Question updated successfully');
            }

            return back()->with('error', 'Failed to update question');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error updating question: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy($competitionId, $quizId, $questionId)
    {
        try {
            $deleted = DB::table('quiz_questions')
                ->where('id', $questionId)
                ->where('quiz_id', $quizId)
                ->delete();

            if ($deleted) {
                return redirect()
                    ->route('admin.competitions.quizzes.show', [$competitionId, $quizId])
                    ->with('success', 'Question deleted successfully');
            }

            return back()->with('error', 'Question not found or already deleted');

        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting question: ' . $e->getMessage());
        }
    }

    /**
     * Reorder questions.
     */
    public function reorder(Request $request, Competition $competition, Quiz $quiz)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*' => 'exists:quiz_questions,id',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->questions as $order => $questionId) {
                $quiz->questions()
                    ->where('id', $questionId)
                    ->update(['order' => $order + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully!',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder questions: ' . $e->getMessage(),
            ], 500);
        }
    }
}
