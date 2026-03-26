<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use DB;

class GiftController extends Controller
{
    /**
     * Display a listing of the gifts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $gifts = Gift::latest()->paginate(10);
        return view('admin.gifts.index', compact('gifts'));
    }

    /**
     * Show the form for creating a new gift.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.gifts._form');
    }

    /**
     * Store a newly created gift in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',  // Changed from image_path to image
            'is_active' => 'boolean',
        ]);
    
        $gift = new Gift();
        $gift->name = $validated['name'];
        $gift->description = $validated['description'] ?? null;
        $gift->price = $validated['price'];
        $gift->is_active = $request->has('is_active');
    
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('gifts', 'public');
            $gift->image_path = $path;
        }
    
        $gift->save();
    
        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift created successfully.');
    }

    /**
     * Display the specified gift.
     *
     * @param  \App\Models\Gift  $gift
     * @return \Illuminate\View\View
     */
    public function show(Gift $gift)
    {
		// Load gift with its relationships
        $gift->load([
            'category',
            'userGifts' => function ($query) {
                $query->with(['sender' => function($q) {
                        $q->with('media');
                    }, 'receiver' => function($q) {
                        $q->with('media');
                    }])
                    ->latest()
                    ->take(10); // Limit to 10 most recent gifts
            },
            'media'
        ]);

        // Calculate gift statistics
        $totalGiftsSent = $gift->userGifts()->count();
        $totalRevenue = $gift->userGifts()->sum('price');
        
        // Get top senders with proper grouping
        $topSenders = DB::table('user_gifts')
            ->select('users.id', 'users.name', 'users.email',
                    DB::raw('count(*) as gift_count'))
            ->join('users', 'user_gifts.sender_id', '=', 'users.id')
            ->where('user_gifts.gift_id', $gift->id)
            ->whereNull('users.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('gift_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $userModel = \App\Models\User::with('media')->find($user->id);
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $userModel->getFirstMediaUrl('profile_image') 
                        ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF',
                    'gift_count' => $user->gift_count
                ];
            });

        // Get top receivers with proper grouping
        $topReceivers = DB::table('user_gifts')
            ->select('users.id', 'users.name', 'users.email',
                    DB::raw('count(*) as received_count'))
            ->join('users', 'user_gifts.receiver_id', '=', 'users.id')
            ->where('user_gifts.gift_id', $gift->id)
            ->whereNull('users.deleted_at')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('received_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                $userModel = \App\Models\User::with('media')->find($user->id);
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo_url' => $userModel->getFirstMediaUrl('profile_image')
                        ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF',
                    'received_count' => $user->received_count
                ];
            });

        return view('admin.gifts.show', compact(
            'gift',
            'totalGiftsSent',
            'totalRevenue',
            'topSenders',
            'topReceivers'
        ));
        //return view('admin.gifts.show', compact('gift'));
    }

    /**
     * Show the form for editing the specified gift.
     *
     * @param  \App\Models\Gift  $gift
     * @return \Illuminate\View\View
     */
    public function edit(Gift $gift)
    {
        return view('admin.gifts.edit', compact('gift'));
    }

    /**
     * Update the specified gift in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gift  $gift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Gift $gift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
        ]);

        $gift->name = $validated['name'];
        $gift->description = $validated['description'] ?? null;
        $gift->price = $validated['price'];
        $gift->is_active = $request->has('is_active');

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($gift->image_path) {
                Storage::disk('public')->delete($gift->image_path);
            }
            
            $path = $request->file('image')->store('gifts', 'public');
            $gift->image_path = $path;
        }

        $gift->save();

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift updated successfully.');
    }

    /**
     * Remove the specified gift from storage.
     *
     * @param  \App\Models\Gift  $gift
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Gift $gift)
    {
        // Delete associated images
        if ($gift->image) {
            Storage::disk('public')->delete($gift->image);
        }
        if ($gift->thumbnail) {
            Storage::disk('public')->delete($gift->thumbnail);
        }

        $gift->delete();

        return redirect()->route('admin.gifts.index')
            ->with('success', 'Gift deleted successfully.');
    }
}
