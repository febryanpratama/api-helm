<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Session;

class AuthorizationController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect()->back();
        }

        return view('management.auth.index');
    }

    public function signin()
    {
        // Initialize
        $email   = request('email');
        $otpCode = rand(1111, 9999);
        
        // Check User By Email
        $userProfile = User::where('email', $email)->first();

        // Signin Logic
        if ($userProfile && $userProfile->role_id == 2) {
            // Initialize
            $userProfile->password      = bcrypt($otpCode);
            $userProfile->save();
            $userProfile->otp           = $otpCode;

            // Send Email OTP Code
            \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));

            // Response
            return response()->json([
                'status'    => true,
                'message'   => 'Lengkapi Kode OTP Untuk melanjutkan'
            ]);
        }

        // Response
        return response()->json([
            'status'    => false,
            'message'   => 'User tidak terdaftar!'
        ]);
    }
}
