<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MeetingRoom;

class MeetController extends Controller
{
    public function index($param = '', Request $request)
    {
        // Initialize
        $roomName = 'tugas-'.rand(100, 100000);
        $path     = $request->fullUrl();
        $date     = date('Y-m-d H:i');

        // Check Valid Data
        $meetingRoom = MeetingRoom::where('link', $path)->first();

        if (!$meetingRoom) {
            // Initialize
            request()->session()->flash('message', 'Meeting Room Tidak Valid!');

            return redirect()->back();
        }

        // Initialize
        $linkExp = date('Y-m-d H:i', $meetingRoom->link_expiration_time);

        if ($date > $linkExp) {
            // Initialize
            request()->session()->flash('message', 'Link Meeting Room Expired.');

            return redirect()->back();
        }

        if ($meetingRoom) {
            if (date('Y-m-d H:i', $meetingRoom->time) >= $date) {
                // Initialize
                request()->session()->flash('message', 'Meeting Room Akan dibuka pada '.date('d F Y H:i', $meetingRoom->time).' (WIB) ');
                
                return redirect()->back();
            } else {
                // Initialize
                if ($param) {
                    $roomName = $param;
                }

                return view('meet.index', compact('roomName'));
            }
        }
    }
}
