<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function store()
    {
        $message = \App\Message::create([
            'user_id' => auth()->user()->id,
            'message' => request()->message,
        ]);

        if ($message) {
            $notif = [
                'status' => 'success',
                'message' => 'Message send successfully'
            ];
            return redirect()->back()->with($notif);
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Message send fail'
        ];

        return redirect()->back()->with($notif);
    }
}
