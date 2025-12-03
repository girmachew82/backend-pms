<?php

namespace App\Http\Controllers\V1\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    // Register
    public function register(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|min:6"
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        return response()->json([
            "message" => "Registered successfully",
            "user" => $user
        ]);
    }

    // Login
    public function login(Request $request)
    {
        $user = User::where("email", $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(["error" => "Invalid credentials"], 401);
        }

        $token = $user->createToken('API Token')->accessToken;

        return response()->json([
            "message" => "Login successful",
            "token" => $token
        ]);
    }
}
