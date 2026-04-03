<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChallengeController extends Controller
{
    /**
     * Display a listing of the challenges.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $challenges = Challenge::latest()
            ->withCount('participants')
            ->paginate(15);

        return view('admin.challenges.index', compact('challenges'));
    }

    /**
     * Show the form for creating a new challenge.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $challenge = new Challenge([
            'status' => 'draft',
            'type' => 'one_time',
            'target_count' => 1,
            'reward_points' => 100,
            'is_featured' => false,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);

        return view('admin.challenges.create', compact('challenge'));
    }

    /**
     * Store a newly created challenge in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,completed,cancelled',
            'type' => 'required|in:daily,weekly,monthly,one_time',
            'target_count' => 'required|integer|min:1',
            'reward_points' => 'required|integer|min:0',
            'rules' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('challenges', 'public');
            $validated['image'] = $path;
        }

        // Create the challenge
        $challenge = Challenge::create($validated);

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge created successfully.');
    }

    /**
     * Display the specified challenge.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\View\View
     */
    public function show(Challenge $challenge)
    {
        $challenge->loadCount('participants');
        return view('admin.challenges.show', compact('challenge'));
    }

    /**
     * Show the form for editing the specified challenge.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\View\View
     */
    public function edit(Challenge $challenge)
    {
        return view('admin.challenges.edit', compact('challenge'));
    }

    /**
     * Update the specified challenge in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Challenge $challenge)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:draft,active,completed,cancelled',
            'type' => 'required|in:daily,weekly,monthly,one_time',
            'target_count' => 'required|integer|min:1',
            'reward_points' => 'required|integer|min:0',
            'rules' => 'nullable|string',
            'is_featured' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($challenge->image) {
                Storage::disk('public')->delete($challenge->image);
            }
            $path = $request->file('image')->store('challenges', 'public');
            $validated['image'] = $path;
        }

        // Update the challenge
        $challenge->update($validated);

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge updated successfully.');
    }

    /**
     * Remove the specified challenge from storage.
     *
     * @param  \App\Models\Challenge  $challenge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Challenge $challenge)
    {
        // Delete image if exists
        if ($challenge->image) {
            Storage::disk('public')->delete($challenge->image);
        }

        $challenge->delete();

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge deleted successfully.');
    }

    /**
     * Cancel the specified challenge.
     */
    public function cancel(Challenge $challenge)
    {
        if ($challenge->status === 'completed' || $challenge->status === 'cancelled') {
            return redirect()->back()->with('error', 'Only active or upcoming challenges can be cancelled.');
        }

        $challenge->update(['status' => 'cancelled']);

        return redirect()->route('admin.challenges.index')
            ->with('success', 'Challenge cancelled successfully.');
    }
}
