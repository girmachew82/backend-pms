<?php

namespace App\Http\Controllers\V1\Api;

use App\Events\UserActivityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Log, Mail};
use App\Exports\UsersExport;
use App\Http\Controllers\Controller;
use App\Models\{Otp, User};
use App\Mail\SendOtpMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;


class UserController extends Controller
{

    public function index(Request $request)
    {
        // 1. Start building the query
        $query = User::query();

        // 2. Handle Searching (Filtering)
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search){
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Handle Sorting
        if ($sortBy = $request->get('sort_by', 'id')) {
            // Default direction to 'asc' if not specified
            $sortDir = $request->get('sort_dir', 'desc');

            // Basic security check to prevent SQL injection by ensuring column exists
            if (in_array($sortBy, ['id', 'name', 'email', 'created_at'])) {
                $query->orderBy($sortBy, $sortDir);
                if($sortBy !=='id'){
                    $query->orderBy('id','desc');
                }
            }else{
                $query->orderBy('id','desc');
            }
        }else{
            $query->orderBy('id','desc');
        }

        // 4. Handle Pagination
        // Use the 'limit' parameter from the client (e.g., 10, 25, 50)
        $limit = $request->get('limit', 5);

        // Final execution of the query with pagination
        $users = $query->paginate($limit);

        // 5. Return the JSON response
        return response()->json([
            'data' => $users->items(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),

        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email'
        ]);

        $password = Hash::make('password');

       $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $password
        ]);
        Log::info("User created: ".json_encode($user));
       event(new UserActivityEvent($user, 'created'));
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
           // Log::info("User updated: ".json_encode($user));
            event(new UserActivityEvent($user, 'update'));

            // Generate OTP
            // $otp = rand(100000, 999999);

            // Otp::updateOrCreate(
            //     ['email' => $request->email],
            //     [
            //         'otp' => $otp,
            //         'expires_at' => now()->addMinutes(5)
            //     ]
            // );

            // Mail::to($request->email)->send(new SendOtpMail($otp));
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

    public function destroy($id)
    {

        $user = User::findOrFail($id);
        if($user){

           $user->delete();
            event(new UserActivityEvent($user, 'deleted'));
            return response()->json([
                'message' =>'Deleted successfully'
            ]);
        }
        else{
            return response()->json([
                'message'=>'User not found'
            ]);
        }

    }



public function export(Request $request)
{
    $format = $request->input('format');
    $query = User::select('id','name','email','created_at');

    // Apply filtering logic
    if($request->filled('search')){
        $query->where('name','like',"%{$request->search}%");
    }

    try {
        if($format === 'xlsx' || $format === 'csv'){
            return Excel::download(new UsersExport($query), 'users.'.$format);
        }

        if($format === 'pdf'){
            $users = $query->get();

            $pdf = Pdf::loadView('exports.users_pdf', compact('users'));
            return $pdf->download('users.pdf');
        }

        // If format is not handled
        return response()->json(['error' => 'Invalid format specified. Must be xlsx, csv, or pdf.'], 400);

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        // Handle validation errors specific to Laravel Excel
        return response()->json(['error' => 'Export failed due to data validation errors.', 'messages' => $e->getMessage()], 422);

    } catch (\Exception $e) {
        // Handle all other general exceptions (I/O, memory, PDF rendering issues)
        \Log::error("User Export Failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

        // Return a generic server error
        return response()->json([
            'error' => 'An unexpected error occurred during file generation.',
            // Only include message in debug environments
            // 'message' => $e->getMessage()
        ], 500);
    }
}
}
