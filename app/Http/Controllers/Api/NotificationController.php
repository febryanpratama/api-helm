<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use DB;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $notify = [];

        if (auth()->check()) {
            // Initialize
            $user   = User::findOrFail(auth()->user()->id);
            // $notif = $user->unreadNotifications()->limit(10)->get()->toArray();
            $notif = DB::table('notifications')->where('notifiable_id', auth()->user()->id)->latest()->get();

            foreach ($notif as $val) {
                // Initialize
                $data = json_decode($val->data, true);
                
                $row['id']          = $val->id;
                $row['title']       = $data['title'];
                $row['code']        = $data['code'];
                $row['message']     = $data['message'];
                $row['data']        = $data['data'];
                $row['icon']        = $data['icon'];
                $row['read_at']     = $val->read_at;
                $row['created_at']  = $val->created_at;

                $notify[] = $row;
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $notify
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function read($id)
    {
        // Initialize
        $userUnreadNotification = auth()->user()
            ->notifications
            ->where('id', $id)
            ->first();

        if ($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Read Data Success',
            'data'      => [
                'read_at'   => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function readAll()
    {
        // Initialize
        $user = auth()->user();

        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Read Data Success',
            'data'      => [
                'read_at'   => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
