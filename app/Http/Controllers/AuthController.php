<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\User;
use App\Wallet;
use App\Attendance;
use App\PendingCommission;
use App\LoginActivity;
use Str;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('company:home', Str::slug(auth()->user()->company->Name));
        }

        if (request('package-id')) {
            // Create Session For Select Package
            request()->session()->put([
                'package-id' => request('package-id')
            ]);
        }

        // Initialize
        $textMenu = 'Masuk';

        return view('auth.login', compact('textMenu'));
    }

    public function signin()
    {
        if (Auth::check()) {
            if (auth()->user()->role_id == 6) {
                return redirect()->route('profile.index');
            } else {
                return redirect(Str::slug(auth()->user()->company->Name).'/company/edit?company='.Str::slug(auth()->user()->company_id));
            }
        }

        return view('auth.signin');
    }

    public function siginVerify()
    {
        /* 
            Rules : 
            1. Check User Exists By Email
            2. If user exists use Signin logic, but user not exists use SiginUp logic
            3. If Referral Code Exists in SignUp Then exect code wallet.
        */
       
        // Initialize
        $email         = request('email');
        $config        = explode('|', request('is_instructor'));
        $name          = explode('@', $email);
        // $referralCode  = request('referral_code');
        $referralCode  = null;

        if ($email == 'mentordummy@gmail.com' || $email == 'muriddummy@gmail.com') {
            $otpCode = '1123';
        } else {
            $otpCode = rand(1111, 9999);
        }

        // Check Dummy Data
        if ($name[1] == 'test.com' || $name[1] == 'test.co.id' || $name[1] == 'test.test') {
            $isDummy = true;
        } else {
            $isDummy = false;
        }
       
        // Check User By Email
        $userProfile = User::where('email', $email)->first();

        // Signin Logic
        if ($userProfile) {
            // Check Suspend Account
            if ($userProfile->is_take_down == 1) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Akun anda di suspend oleh admin!'
                ]);
            }
            
            // Initialize
            $userProfile->password      = bcrypt($otpCode);
            $userProfile->role_id       = $config[1];
            $userProfile->is_instructor = $config[0];
            $userProfile->otp           = ($isDummy) ? $otpCode : null;
            $userProfile->save();
            $userProfile->otp           = $otpCode;

            // Send Email OTP Code
            \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));

            // Response
            return response()->json([
                'status'    => true,
                'message'   => 'Lengkapi Kode OTP Untuk melanjutkan'
            ]);

            die;
        }

        // Initialize
        $referralCodeGenerate = $this->generateRandomString(6);

        // Check Referral Code Exists
        $userReferral = User::where('referral_code', $referralCodeGenerate)->first();

        if ($userReferral) {
            for ($i= 0; $i < 100; $i++) { 
                // Initialize
                $referralCodeGenerate = $this->generateRandomString(6);
                $userReferral         = User::where('referral_code', $referralCodeGenerate)->first();

                if (!$userReferral) {
                    break;
                }
            }
        }

        // SignUp Logic
        $user = User::create([
            'email'         => $email,
            'name'          => ucfirst($name[0]),
            'role_id'       => $config[1],
            'password'      => bcrypt($otpCode),
            'otp'           => ($isDummy) ? $otpCode : null,
            'is_instructor' => $config[0],
            'is_active'     => ($config[0] == 0) ? 'y' : 'n',
            'referral_code' => $referralCodeGenerate,
            'imei'          => request('imei')
        ]);

        if ($user) {
            // Wallet For New Register
            // Wallet::create([
            //     'user_id'           => $user->id,
            //     'balance'           => 2000,
            //     'is_verified'       => 1,
            //     'balance_type'      => 'income',
            //     'apps_commission'   => 0,
            //     'original_balance'  => 2000,
            //     'details'           => 'New Register'
            // ]);

            // Check Referral Code
            if ($referralCode && $referralCode != '') {
                // Get Referral Info
                $check_user_referral = User::whereNotNull('referral_code')->where('referral_code', $referralCode)->first();

                if ($check_user_referral) {
                    $getAllByReferral = User::where('referral_id', $check_user_referral->id)->whereNotNull('referral_id')->get()->count();
                } else {
                    $getAllByReferral = 0;
                }

                if ($check_user_referral && $getAllByReferral <= 200) { // check if code referral user exists and max 200 data
                    $user->referral_id = $check_user_referral->id;
                    $user->save();

                    // For User New Create Course
                    // When the user (Downline) makes a course package, the upline will get a commission of 2000 rupiah per package
                    // PendingCommission::create([
                    //     'upline_id'     => $check_user_referral->id,
                    //     'downline_id'   => $user->id
                    // ]);

                    // Wallet For Referral
                    // Wallet::create([
                    //     'user_id'           => $check_user_referral->id,
                    //     'balance'           => 2000,
                    //     'is_verified'       => 1,
                    //     'balance_type'      => 'income',
                    //     'apps_commission'   => 0,
                    //     'original_balance'  => 2000,
                    //     'details'           => 'Referral'
                    // ]);
                }
            }

            // For Notification Email
            $user->otp = $otpCode;
            \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Selamat, Pendaftaran berhasil. <br> Silahkan Login untuk melanjutkan.'
        ]);
    }

    public function verifyOtp()
    {
        return view('auth.verify-otp');
    }

    public function validationOtp()
    {
        // Initialize
        $email    = request('email');
        $password = join(request('otp_code'));

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Track Login
            LoginActivity::create([
                'user_id'   => auth()->user()->id,
                'role_id'   => auth()->user()->role_id,
                'type'      => 'Desktop'
            ]);

            // Admin Role
            if (auth()->user()->role_id == 10) {
                $redirect = 'admin-panel/dashboard';
            }

            // Instructor Role
            if (auth()->user()->role_id == 1) {
                // Initialize
                $redirect = 'paket-kursus';

                // Check Company Exists
                if (!auth()->user()->company_id) {
                    $redirect = 'transaction/register';
                }

                // Check Complate Data
                if (auth()->user()->company && !auth()->user()->company->facebook) {
                    $redirect = Str::slug(auth()->user()->company->Name).'/company/edit?company='.auth()->user()->company->ID;
                }
            }

            // Member Role
            if (auth()->user()->role_id == 6) {
                if (request('redirect')) {
                    $redirect = 'student/course/show/member/'.request('redirect');
                } else {
                    $redirect = 'student/course?my-course=true';
                }
            }

            // Management Role
            if (auth()->user()->role_id == 2) {
                $redirect = 'management/dashboard';
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Berhasil Masuk',
                'redirect'  => $redirect
            ]);
        }

        return response()->json([
            'status'    => false,
            'message'   => 'Kode OTP salah'
        ]);
    }

    public function resendOtp()
    {
        // Initialize
        $email = request('email');

        if ($email == 'mentordummy@gmail.com' || $email == 'muriddummy@gmail.com') {
            $otpCode = '1123';
        } else {
            $otpCode = rand(1111, 9999);
        }

        // Check User By Email
        $userProfile = User::where('email', $email)->first();

        // Signin Logic
        if ($userProfile) {
            // Initialize
            $userProfile->password = bcrypt($otpCode);
            $userProfile->save();
            $userProfile->otp      = $otpCode;

            // Send Email OTP Code
            \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));

            // Response
            return response()->json([
                'status'    => true,
                'message'   => 'Lengkapi Kode OTP Untuk melanjutkan'
            ]);

            die;
        }
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('landing');
    }

    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
