<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\UserFcm;
use Auth;
use App\LoginActivity;
use App\Http\Resources\SigninResource;
use App\Http\Resources\OTPResource;
use Validator;

class AuthController extends Controller
{
    public function siginVerifyV2(Request $request)
    {
        /* 
            Rules : 
            1. Check User Exists By Email
            2. If user exists use Signin logic, but user not exists use SiginUp logic
            3. If Referral Code Exists in SignUp Then exect code wallet.
        */
       
        $request->validate([
            'email'         => 'required|max:191',
            'account_type'  => 'required',
            'login_type'    => 'nullable|in:1,2'
        ]);
       
        // Initialize
        $email         = request('email');
        $accountType   = request('account_type');
        $name          = explode('@', $email);
        // $referralCode  = request('referral_code');
        $referralCode  = null;

        if ($email == 'sellerdumy@gmail.com' || $email == 'developer-archiloka@gmail.com') {
            $otpCode = '1123';
        } else {
            $otpCode = '1234';
            // $otpCode = rand(1111, 9999);
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
                    'status'    => 'error',
                    'message'   => 'Akun anda di suspend oleh admin!'
                ]);
            }
            
            // Initialize
            // $userProfile->password      = bcrypt($otpCode);
            $userProfile->role_id       = $accountType;
            $userProfile->is_instructor = ($accountType == 1) ? 1 : 0;
            $userProfile->otp           = ($isDummy) ? $otpCode : null;
            $userProfile->save();
            $userProfile->otp           = $otpCode;
            $userProfile->save();

            // Send Email OTP Code
            \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));

            // Check FCM Token Exists
            if (request('fcm_token_apps')) {
                // Initialize
                $fcmToken = UserFcm::where('user_id', Auth()->user()->id)->first();

                if ($fcmToken) {
                    $fcmToken->delete();
                }

                UserFcm::create([
                    'user_id'   => Auth()->user()->id,
                    'fcm_id'    => request()->fcm_token_apps
                ]);
            }

            if ($request->login_type != '' && $request->login_type == 1) { // otp
                try {
                    \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));
                } catch (\Throwable $th) {
                    return [
                        'status'    => 'error',
                        'code'      => 2,
                        'message'   => 'terjadi kendala OTP, silahkan login menggunakan password'
                    ];
                }
            }

            // Response
            return new SigninResource($userProfile);
        }

        $request->validate([
            'email' => 'unique:users|max:191',
        ]);

        // SignUp Logic
        $user = User::create([
            'email'         => $email,
            'name'          => ucfirst($name[0]),
            'role_id'       => $accountType,
            'password'      => bcrypt($otpCode),
            'password_backup'      => bcrypt($otpCode),
            'is_instructor' => ($accountType == 1) ? 1 : 0,
            'is_active'     => 'y',
            'otp'           => $otpCode,
            'referral_code' => $this->generateRandomString(6),
            'imei'          => request('imei')
        ]);

        if ($user) {
            // Check FCM Token Exists
            if (request('fcm_token_apps')) {
                // Initialize
                $fcmToken = UserFcm::where('user_id', Auth()->user()->id)->first();

                if ($fcmToken) {
                    $fcmToken->delete();
                }

                UserFcm::create([
                    'user_id'   => Auth()->user()->id,
                    'fcm_id'    => request()->fcm_token_apps
                ]);
            }

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

                    // Insert Reward From Referral Invite
                    // Wallet::create([
                    //     'user_id'           => $check_user_referral->id,
                    //     'balance'           => 1500,
                    //     'original_balance'  => 1500,
                    //     'details'           => 'referral_invite|'.$user->id,
                    //     'is_verified'       => 1
                    // ]);

                    // Insert Reward From Fill In Refferal Code
                    // Wallet::create([
                    //     'user_id'           => $user->id,
                    //     'balance'           => 1000,
                    //     'original_balance'  => 1000,
                    //     'details'           => 'referral_register|'.$check_user_referral->id,
                    //     'is_verified'       => 1
                    // ]);
                    
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
            // \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));

            if ($request->login_type != '' && $request->login_type == 1) { // otp
                try {
                    \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));
                } catch (\Throwable $th) {
                    //throw $th;
    
                    // dd('a');
                    return [
                        'status'    => 'error',
                        'code'      => 2,
                        'message'   => 'terjadi kendala OTP, silahkan login menggunakan password'
                    ];
                }
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Register Successfully'
        ]);
    }

    public function validationOtpV2(Request $request)
    {
        $request->validate([
            'email' => 'required|max:191',
            'otp'   => 'required'
        ]);

        // Initialize
        $email    = request('email');
        $password = request('otp');

        $user_email = User::where('email', $email)->first();

        if ($user_email->otp != $password) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Kode OTP tidak berlaku!',
                'data'      => [
                    'otp'           => $password,
                    'error_code'    => 'otp_not_valid'
                ]
            ]);
        }

        if ($user_email->otp == $password) { // update password asli replace dengan OTP dijadikan password sementara

            $user_email->password = bcrypt($password);
            $user_email->save();
        }  

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Initialize
            $user            = auth()->user();
            $company         = auth()->user()->company;
            $token           = $user->createToken('RuangAjar App')->accessToken;
            $user_collection = collect($user);
            $user_collection->put('token', $token);

            // Berhasil login replace password OTP dengan backup password yg asli
            $get_user = User::find($user->id);
            $get_user->update([
                'password' => $get_user->password_backup,
                'otp'       => null,
            ]);

            // Track Login
            LoginActivity::create([
                'user_id'   => auth()->user()->id,
                'role_id'   => auth()->user()->role_id,
                'type'      => 'Mobile'
            ]);

            // Response
            return new OTPResource($user_collection);

            die;
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Kode OTP tidak berlaku!',
            'data'      => [
                'otp'           => $password,
                'error_code'    => 'otp_not_valid'
            ]
        ]);
    }

    public function siginVerify(Request $request)
    {
        /* 
            Rules : 
            1. Check User Exists By Email
            2. If user exists use Signin logic, but user not exists use SiginUp logic
            3. If Referral Code Exists in SignUp Then exect code wallet.
        */
       
        $request->validate([
            'email'         => 'required|max:191',
            'account_type'  => 'required'
        ]);
       
        // Initialize
        $email         = request('email');
        $accountType   = request('account_type');
        $name          = explode('@', $email);
        $referralCode  = null;

        if ($email == 'mentordummy@gmail.com' || $email == 'muriddummy@gmail.com') {
            $otpCode = '1123';
        } else {
            // $otpCode = rand(1111, 9999);
            $otpCode = '1234';
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
                    'status'    => 'error',
                    'message'   => 'Akun anda di suspend oleh admin!'
                ]);
            }
            
            // Initialize
            $userProfile->password      = bcrypt($otpCode);
            $userProfile->role_id       = $accountType;
            $userProfile->is_instructor = ($accountType == 1) ? 1 : 0;
            $userProfile->otp           = ($isDummy) ? $otpCode : null;
            $userProfile->save();
            $userProfile->otp           = $otpCode;

            // Send Email OTP Code
            \Mail::to($userProfile->email)->send(new \App\Mail\VerificationOtp($userProfile));

            // Check FCM Token Exists
            if (request('fcm_token_apps')) {
                // Initialize
                $fcmToken = UserFcm::where('user_id', Auth()->user()->id)->first();

                if ($fcmToken) {
                    $fcmToken->delete();
                }

                UserFcm::create([
                    'user_id'   => Auth()->user()->id,
                    'fcm_id'    => request()->fcm_token_apps
                ]);
            }

            // Response
            return new SigninResource($userProfile);
        }

        $request->validate([
            'email' => 'unique:users|max:191',
        ]);

        // SignUp Logic
        $user = User::create([
            'email'         => $email,
            'name'          => ucfirst($name[0]),
            'role_id'       => $accountType,
            'password'      => bcrypt($otpCode),
            'is_instructor' => ($accountType == 1) ? 1 : 0,
            'is_active'     => 'y',
            'referral_code' => $this->generateRandomString(6),
            'imei'          => request('imei')
        ]);

        if ($user) {
            // Check FCM Token Exists
            if (request('fcm_token_apps')) {
                // Initialize
                $fcmToken = UserFcm::where('user_id', Auth()->user()->id)->first();

                if ($fcmToken) {
                    $fcmToken->delete();
                }

                UserFcm::create([
                    'user_id'   => Auth()->user()->id,
                    'fcm_id'    => request()->fcm_token_apps
                ]);
            }
    
            // For Notification Email
            $user->otp = $otpCode;
            \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Register Successfully'
        ]);
    }

    public function validationOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|max:191',
            'otp'   => 'required'
        ]);

        // Initialize
        $email    = request('email');
        $password = request('otp');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Initialize
            $user            = auth()->user();
            $company         = auth()->user()->company;
            $token           = $user->createToken('RuangAjar App')->accessToken;
            $user_collection = collect($user);
            $user_collection->put('token', $token);

            // Track Login
            LoginActivity::create([
                'user_id'   => auth()->user()->id,
                'role_id'   => auth()->user()->role_id,
                'type'      => 'Mobile'
            ]);

            // Response
            return new OTPResource($user_collection);

            die;
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Kode OTP tidak berlaku!',
            'data'      => [
                'otp'           => $password,
                'error_code'    => 'otp_not_valid'
            ]
        ]);
    }

    public function validationPassword(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'email' => 'required|max:191',
            'is_validate_password' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
            ];

            return response()->json($data, 400);
        }

        $user = User::where('email', $request->email)->first();

        if ($request->is_validate_password == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'password' => 'required',
                'password_confirm'   => 'required',
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                ];

                return response()->json($data, 400);
            }

            if ($request->password == $request->password_confirm) {
    
                $user->password = bcrypt($request->password);
                $user->password_backup = bcrypt($request->password); // ackup password asli
                $user->is_validate_password = 1; // update pass sudah divalidasi
                $user->save();
            } else {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Password tidak cocok',
                ], 400);
            }

        }

        // Initialize
        $email    = request('email');
        $password = request('password');

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            // Initialize
            $user            = auth()->user();
            $company         = auth()->user()->company;
            $token           = $user->createToken('RuangAjar App')->accessToken;
            $user_collection = collect($user);
            $user_collection->put('token', $token);

            // Track Login
            LoginActivity::create([
                'user_id'   => auth()->user()->id,
                'role_id'   => auth()->user()->role_id,
                'type'      => 'Mobile'
            ]);

            // Response
            return new OTPResource($user_collection);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Password yang anda masukan salah',
            'data'      => [
                'otp'           => $password,
                'error_code'    => 'otp_not_valid'
            ]
        ]);
    }

    public function loginActivity()
    {
        // Initialize
        $activity = '';

        // Check Login or Anonim
        if (auth()->check()) {
            // Initialize
            $activity = LoginActivity::where('user_id', auth()->user()->id)->latest()->paginate(20);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data tersedia',
            'data'      => $activity
        ]);
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

    public function resendOtp(Request $request)
    {
       // Validation
        $validator = Validator::make(request()->all(), [
            'email' => 'required|max:191',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        // Check User
        $user = User::where('email', request('email'))->first();

        if (!$user) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'User dengan Email ('.request('email').') tidak ditemukan.'
            ]);
        }

        // Initialize
        $otpCode = rand(1111, 9999);

        $user->update([
            'password' => bcrypt($otpCode),
            'otp'      => $otpCode
        ]);

        // Send OTP With Mail
        \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));

        return response()->json([
            'status'    => 'success',
            'message'   => 'OTP berhasil dikirim ulang.'
        ]);
    }


    // AUTH ADMIN CMS
    public function validationAuthCms(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'email'   => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user();
            $success['token'] =  $user->createToken('CMS APP')->accessToken;

            // // Check FCM Token Exists
            // if (request('fcm_token_apps')) {

            //     $fcmToken = UserFcm::where('user_id', Auth()->user()->id)->first();

            //     if ($fcmToken) {
            //         $fcmToken->delete();
            //     }

            //     UserFcm::create([
            //         'user_id'   => Auth()->user()->id,
            //         'fcm_id'    => request()->fcm_token_apps
            //     ]);
            // }

            return response()->json(['status' => true, 'code' => 200, 'message' => 'login berhasil', 'result' => $success], 200);
        }

        return response()->json([
            'status' => false,
            'code' => 409,
            'message' => 'Password salah',
        ], 409);
    }

    public function logoutCms()
    {
        $user = Auth::user()->token();
        if ($user->revoke()) {
            return response()->json([
                'status' => true,
                'code'  => 200,
                'message' => 'logout success',
            ], 200);
        }
    }
}
