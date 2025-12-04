<?php

namespace App\Http\Controllers\V1\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Mail};
use App\Http\Controllers\Controller;
use App\Models\{Otp, User};
use App\Mail\SendOtpMail;
use Carbon\Carbon;

class UserController extends Controller
{

    public function index(Request $request)
    {
        // 1. Start building the query
        $query = User::latest();

        // 2. Handle Searching (Filtering)
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            // Add other columns you want to search
        }

        // 3. Handle Sorting
        if ($sortBy = $request->get('sort_by')) {
            // Default direction to 'asc' if not specified
            $sortDir = $request->get('sort_dir', 'asc');

            // Basic security check to prevent SQL injection by ensuring column exists
            if (in_array($sortBy, ['id', 'name', 'email', 'created_at'])) {
                $query->orderBy($sortBy, $sortDir);
            }
        }

        // 4. Handle Pagination
        // Use the 'limit' parameter from the client (e.g., 10, 25, 50)
        $limit = $request->get('limit', 5);

        // Final execution of the query with pagination
        $users = $query->paginate($limit);

        // 5. Return the JSON response
        return response()->json([
            // Laravel's paginator already returns structured data,
            // but we can pass it directly.
            'data' => $users->items(),        // The current page's user list
            'total' => $users->total(),      // Total records in the database (for pagination UI)
            'per_page' => $users->perPage(), // Items per page
            'current_page' => $users->currentPage(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        $password = Hash::make('password');

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $password
        ]);
        // Generate OTP
        $otp = rand(100000, 999999);

        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(5)
            ]
        );

        Mail::to($request->email)->send(new SendOtpMail($otp));
        return response()->json([
            'message' => 'User registered'
        ], 201);
    }
    public function update(Request $request, $id)
    {

        $user = User::findOrFail($id);
        if ($user) {
            $validated = $request->validate([
                'name' => 'required|string|min:2',
                'email' => 'required|email'
            ]);

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // Generate OTP
            $otp = rand(100000, 999999);

            Otp::updateOrCreate(
                ['email' => $request->email],
                [
                    'otp' => $otp,
                    'expires_at' => now()->addMinutes(5)
                ]
            );

            Mail::to($request->email)->send(new SendOtpMail($otp));
            return response()->json([
                'message' => 'User data Updated'
            ], 201);
        } else {
            return response()->json([
                "message" => "No user found in such ID"
            ]);
        }
    }
    public function show($id)
    {

        $user = User::findOrFail($id);
        if ($user) {


            return response()->json([
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                "message" => "No user found in such ID"
            ]);
        }
    }
}
