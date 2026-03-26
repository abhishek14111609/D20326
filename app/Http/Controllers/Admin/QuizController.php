<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Competition $competition)
    {
        $quizzes = $competition->quizzes()->latest()->paginate(10);
        return view('admin.quizzes.index', compact('competition', 'quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Competition $competition)
    {
        return view('admin.quizzes.create', compact('competition'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $competition->quizzes()->create($validated);

        return redirect()
            ->route('admin.competitions.quizzes.index', $competition)
            ->with('success', 'Quiz created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Competition $competition, Quiz $quiz)
    {
        $quiz->load('questions');
        return view('admin.quizzes.show', compact('competition', 'quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Competition $competition, Quiz $quiz)
    {
        return view('admin.quizzes.edit', compact('competition', 'quiz'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Competition $competition, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $quiz->update($validated);

        return redirect()
            ->route('admin.competitions.quizzes.index', $competition)
            ->with('success', 'Quiz updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Competition $competition, Quiz $quiz)
    {
        $quiz->delete();
        return redirect()
            ->route('admin.competitions.quizzes.index', $competition)
            ->with('success', 'Quiz deleted successfully');
    }

    /**
     * Toggle quiz active status
     */
    public function toggleStatus(Competition $competition, Quiz $quiz)
    {
        $quiz->update(['is_active' => !$quiz->is_active]);
        return back()->with('success', 'Quiz status updated');
    }

    /**
     * Show quiz statistics
     */
    public function statistics(Competition $competition, Quiz $quiz)
    {
		$participants = $quiz->participants();

        $total = $quiz->participants()->count();
		$completed = $quiz->participants()
				->whereNotNull('completed_at')
				->count();
			$passed = $participants
				->where('score', '>=', $quiz->passing_score) // adjust field name
				->count();

			$averageTimeTaken = $participants
				->whereNotNull('started_at')
				->whereNotNull('completed_at')
				->select(
				DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
			)
				->value('avg_time') ?? 0;
		
		$distribution = $quiz->participants()
						->selectRaw('
							FLOOR(score / 10) * 10 as score_range,
							COUNT(*) as total
						')
						->groupBy('score_range')
						->orderBy('score_range')
						->pluck('total', 'score_range')
						->toArray();

					// Ensure all ranges exist (0–100)
					$scoreDistribution = [];
					for ($i = 0; $i <= 100; $i += 10) {
						$scoreDistribution[$i . '-' . ($i + 9)] = $distribution[$i] ?? 0;
					}

		
		$statistics = [
			'total_participants' => $total,
			'average_score' => $quiz->participants()->avg('score') ?? 0,
			'completion_rate' => $total ? round(($completed / $total) * 100, 2) : 0,
			'passing_rate' => $total ? round(($passed / $total) * 100, 2) : 0,
			'average_time_taken' => round($averageTimeTaken / 60, 2), // minutes
			'questions' => $quiz->questions()->withCount('answers')->get(),
			 'score_distribution' => $scoreDistribution,
			'top_performers' => $quiz->participants()
				->with('user')
				->orderByDesc('score')
				->take(10)
				->get()
		];
        return view('admin.quizzes.statistics', compact('competition', 'quiz', 'statistics'));
    }
}
