<?php

namespace App\Http\Controllers\V1\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\SendOtpMail;
use App\Models\{Otp, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


class OtpAuthController extends Controller
{
    // 1) Send OTP to Email
public function sendOtp(Request $request)
{
    $request->validate(['email' => 'required|email']);

    // Create user if not exists
    $user = User::firstOrCreate(
        ['email' => $request->email],
        ['name' => 'User '.rand(1000,9999),
        'password' => bcrypt('otp-login')
        ]
    );

    // Generate OTP
    $otp = rand(100000, 999999);

    Otp::updateOrCreate(
        ['email' => $request->email],
        ['otp' => $otp,
         'expires_at' => now()->addMinutes(5)
        ]
    );

    Mail::to($request->email)->send(new SendOtpMail($otp));

    return response()->json([
        'message' => 'OTP sent successfully.',
        'email' => $request->email
    ]);
}

    // 2) Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $otpData = Otp::where('email', $request->email)
                      ->where('otp', $request->otp)
                      ->where('expires_at', '>', Carbon::now())
                      ->first();

        if (!$otpData) {
            return response()->json([
                'message' => 'Invalid or expired OTP.'
            ], 422);
        }

        // OTP is valid → login user
        $user = User::where('email', $request->email)->first();

        // Create Sanctum token
        $token = $user->createToken('API Token')->plainTextToken;

        // Delete OTP
        $otpData->delete();

        return response()->json([
            'message' => 'OTP verified successfully.',
            'token' => $token,
            'user'  => $user,
        ]);
    }
}
