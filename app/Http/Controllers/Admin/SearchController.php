<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Add other models as needed

class SearchController extends Controller
{
    /**
     * Handle search request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $results = [];

        // Search users
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'title' => $user->name,
                    'description' => $user->email,
                    'url' => route('admin.users.edit', $user->id),
                    'icon' => 'bx bx-user'
                ];
            });

        $results = array_merge($results, $users->toArray());

        // Add more search sources here as needed
        // Example:
        // $posts = Post::search($query)->take(3)->get();
        // $results = array_merge($results, $posts->toArray());

        return response()->json($results);
    }
}
