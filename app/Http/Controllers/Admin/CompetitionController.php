<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DB;

class CompetitionController extends Controller
{
    /**
     * Display a listing of the competitions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
		$competitions = Competition::latest()
			->with(['participants' => function ($query) {
				// Join users table to get users' data
				$query->select('users.id', 'users.name', 'users.avatar')
					  ->join('users', 'users.id', '=', 'competition_participants.user_id')  // Ensure proper join
					  ->whereNull('competition_participants.deleted_at')  // Optional, in case you want to filter soft deleted rows
					  ->latest('competition_participants.created_at')
					  ->take(3);  // Limit to 3 participants
			}])
			->withCount('participants')  // Get total count of participants
			->paginate(15);  // Paginate the competitions (15 per page)

            
        return view('admin.competitions.index', compact('competitions'));
    }
    
    /**
     * Show the form for creating a new competition.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $competition = new Competition([
            'status' => 'upcoming',
            'type' => 'solo',
            'min_team_size' => 1,
            'max_team_size' => 5,
            'entry_fee' => 0,
            'currency' => 'USD',
            'is_featured' => false,
            'registration_start' => now(),
            'registration_end' => now()->addDays(7),
            'competition_start' => now()->addDays(8),
            'competition_end' => now()->addDays(15),
            'timezone' => 'UTC',
            'sort_order' => 0,
            'tags' => json_encode([])
        ]);
        
        return view('admin.competitions.create', compact('competition'));
    }
    
    /**
     * Store a newly created competition in storage.
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
            'banner_image' => 'nullable|image|max:5120',
            'type' => 'required|in:solo,team,tournament,league', // This is for participant type, not competition
            'registration_start' => 'required|date',
            'registration_end' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if (strtotime($value) <= strtotime($request->registration_start)) {
                        $fail('The registration end must be a date after registration start.');
                    }
                },
            ],
            'competition_start' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    // No validation against registration_end
                },
            ],
            'competition_end' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    if (strtotime($value) <= strtotime($request->competition_start)) {
                        $fail('The competition end must be a date after the competition start.');
                    }
                },
            ],
            'max_participants' => 'nullable|integer|min:1',
            'min_team_size' => 'nullable|integer|min:1|required_if:type,team,tournament,league',
            'max_team_size' => 'nullable|integer|min:1|required_if:type,team,tournament,league|gte:min_team_size',
            'entry_fee' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'prizes' => 'nullable|array',
            'rules' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'timezone' => 'nullable|timezone',
            'tags' => 'nullable|string',
        ]);
        
        // Store the type for participant creation
        $competitionType = $validated['type'];
        
        // Remove type from validated data to prevent it from being saved to competitions table
        unset($validated['type']);
        
        // Map form field names to database column names
        $validated['start_date'] = $validated['competition_start'];
        $validated['end_date'] = $validated['competition_end'];
        
        // Remove the form field names to avoid mass assignment issues
        unset($validated['competition_start'], $validated['competition_end']);
        
        // Process prizes array if present
        if ($request->has('prizes') && is_array($request->prizes)) {
            $validated['prizes'] = json_encode($request->prizes);
        } else {
            $validated['prizes'] = json_encode([
                ['name' => '1st Place', 'value' => null, 'description' => null],
                ['name' => '2nd Place', 'value' => null, 'description' => null],
                ['name' => '3rd Place', 'value' => null, 'description' => null],
            ]);
        }
        
        // Set default values if not provided
        $validated['entry_fee'] = $validated['entry_fee'] ?? 0;
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['is_featured'] = $validated['is_featured'] ?? false;
        $validated['timezone'] = $validated['timezone'] ?? config('app.timezone');
        
        // Handle file uploads
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('competitions/images', 'public');
            $validated['image'] = $path;
        } else {
            // Set default image path if no image is uploaded
            $validated['image'] = 'competitions/images/default-competition.jpg';
        }
        
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('competitions/banners', 'public');
            $validated['banner_image'] = $bannerPath;
        } else {
            // Set default banner path if no banner is uploaded
            $validated['banner_image'] = 'competitions/banners/default-banner.jpg';
        }
        
        // Make sure the storage directory exists
        if (!Storage::disk('public')->exists('competitions/images')) {
            Storage::disk('public')->makeDirectory('competitions/images');
        }
        if (!Storage::disk('public')->exists('competitions/banners')) {
            Storage::disk('public')->makeDirectory('competitions/banners');
        }
        
        // Create the competition and participant in a transaction
        return \DB::transaction(function () use ($validated, $competitionType) {
            try {
                // Create the competition
                $competition = Competition::create($validated);
                
                // Only proceed if user is authenticated
                if (!auth()->check()) {
                    return redirect()->route('admin.competitions.index')
                        ->with('success', 'Competition created successfully!');
                }
                
                $user = auth()->user();
                $now = now();
                
                // Prepare participant data
                $participantData = [
                    'competition_id' => $competition->id,
                    'user_id' => $user->id,
                    'score' => 0,
                    'rank' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                // Insert participant directly to avoid model events
                \DB::table('competition_participants')->insert($participantData);
                
                // Handle team creation if needed
                if (in_array($competitionType, ['team', 'tournament', 'league'])) {
                    // Check if teams table exists
                    if (!\Schema::hasTable('teams')) {
                        throw new \Exception('Teams functionality is not properly set up. Please run the database migrations.');
                    }
                    
                    // Create team with the current user as leader
                    $team = new Team([
                        'competition_id' => $competition->id,
                        'name' => $user->name . "'s Team",
                        'leader_id' => $user->id,
                        'status' => 'active',
                        'size' => $validated['max_team_size'] ?? 1,
                        'score' => 0,
                        'metadata' => [
                            'created_by' => $user->id,
                            'created_at' => now()->toDateTimeString(),
                        ]
                    ]);
                    
                    $team->save();
                    
                    // Add the user to the team
                    $team->members()->attach($user->id, [
                        'role' => 'leader',
                        'status' => 'accepted',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    // Update the participant record with team_id if needed
                    \DB::table('competition_participants')
                        ->where('competition_id', $competition->id)
                        ->where('user_id', $user->id)
                        ->update(['team_id' => $team->id]);
                }
                
                return redirect()->route('admin.competitions.show', $competition->id)
                    ->with('success', 'Competition created successfully!');
                    
            } catch (\Exception $e) {
                \Log::error('Error creating competition: ' . $e->getMessage());
                \Log::error($e->getTraceAsString());
                
                return back()
                    ->withInput()
                    ->with('error', 'Failed to create competition. ' . $e->getMessage());
            }
        });
    }
    
    /**
     * Display the specified competition.
     *
     * @param  \App\Models\Competition  $competition
     * @return \Illuminate\View\View
     */
    public function show(Competition $competition)
    {
        $participants = DB::table('competition_participants')
						->leftjoin('users', 'users.id', '=', 'competition_participants.user_id')
						->leftjoin('competitions', 'competitions.id', '=', 'competition_participants.competition_id')
						->where('competitions.id', $competition->id)
						->select(
							'users.id as user_id',
							'users.name',
							'users.email',
							'competition_participants.status',
							'competition_participants.created_at',
							'competitions.title'
						)
						->orderBy('competition_participants.created_at', 'desc')
						->get();
		//dd($competition);
        
        return view('admin.competitions.show', compact('competition', 'participants'));
    }
    
    /**
     * Show the form for editing the specified competition.
     *
     * @param  \App\Models\Competition  $competition
     * @return \Illuminate\View\View
     */
    public function edit(Competition $competition)
    {
        // Load the competition with necessary relationships
        $competition->load(['participants' => function($query) {
            $query->with(['user:id,name,avatar'])
                ->latest('competition_participants.created_at')
                ->limit(3);
        }]);
        
        // Ensure prizes is always an array
        if (empty($competition->prizes) || $competition->prizes === 'null') {
            $competition->prizes = [
                ['name' => '1st Place', 'value' => null, 'description' => null],
                ['name' => '2nd Place', 'value' => null, 'description' => null],
                ['name' => '3rd Place', 'value' => null, 'description' => null],
            ];
        }
        
        // Ensure tags is always an array
        if (empty($competition->tags) || $competition->tags === 'null') {
            $competition->tags = [];
        }
        
        return view('admin.competitions.edit', compact('competition'));
    }
    
    /**
     * Update the specified competition in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Competition  $competition
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner_image' => 'nullable|image|max:5120',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'competition_start' => 'required|date|after:registration_end',
            'competition_end' => 'required|date|after:competition_start',
            'status' => 'required|in:draft,upcoming,active,completed,cancelled',
            'type' => 'required|in:solo,team,tournament,league',
            'max_participants' => 'nullable|integer|min:1',
            'min_participants' => 'required|integer|min:1',
            'entry_fee' => 'required|numeric|min:0',
            'prizes' => 'nullable|string',
            'rules' => 'nullable|string',
            'judging_criteria' => 'nullable|string',
            'is_featured' => 'boolean',
            'timezone' => 'required|string|timezone',
        ]);
        
        // Handle file uploads
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($competition->image) {
                Storage::disk('public')->delete($competition->image);
            }
            $path = $request->file('image')->store('competitions', 'public');
            $validated['image'] = $path;
        }
        
        if ($request->hasFile('banner_image')) {
            // Delete old banner if exists
            if ($competition->banner_image) {
                Storage::disk('public')->delete($competition->banner_image);
            }
            $path = $request->file('banner_image')->store('competitions/banners', 'public');
            $validated['banner_image'] = $path;
        }
        
        // Update the competition
        $competition->update($validated);
        
        return redirect()->route('admin.competitions.index')
            ->with('success', 'Competition updated successfully.');
    }
    
    /**
     * Remove the specified competition from storage.
     *
     * @param  \App\Models\Competition  $competition
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Competition $competition)
    {
        // Delete images if they exist
        if ($competition->image) {
            Storage::disk('public')->delete($competition->image);
        }
        
        if ($competition->banner_image) {
            Storage::disk('public')->delete($competition->banner_image);
        }
        
        $competition->delete();
        
        return redirect()->route('admin.competitions.index')
            ->with('success', 'Competition deleted successfully.');
    }
}
