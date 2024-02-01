<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MemberInviteController extends Controller
{
    public function inviteUrl()
    {
        $check_user = \App\User::find(Crypt::decrypt(request()->id));

        if ($check_user && $check_user->role_id == '1' || $check_user->role_id == '2') {
            
            $check_user_exists = \App\User::where('email', request()->email)->first();

            if (!$check_user_exists) {
                
                $user = \App\User::create([
                    'email' => request()->email,
                    'name' => request()->name,
                    'role_id' => 6,
                    'company_id' => $check_user->company_id,
                    'is_active' => 'y'
                ]);
                return redirect()->route('home');
            } else {
                $notif = [
                    'status' => 'failed',
                    'message' => 'Email sudah terdaftar',
                ];
                return redirect()->back()->with($notif);
            }

        }

        $notif = [
            'status' => 'failed',
            'message' => 'Link User invite tidak terdaftar',
        ];

        return redirect()->back()->with($notif);
        
    }

    public function index()
    {
        return view('member.invite.index');
    }
}
