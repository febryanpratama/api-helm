<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserCheckController extends Controller
{
    public function store()
    {
        $motivation = \App\UserCheck::updateOrCreate(
            [
                'id' => request()->id
            ], [
            'user_id' => auth()->user()->id,
            'location' => request()->location,
        ]);
    
        if ($motivation) {
            if (request()->file( 'file_photo' )) {
                $file_foto = request()->file( 'file_photo' );
        
                $imagePath = request('file_photo')->store('uploads/img/checked', 'public');
    
                $motivation->photo = env('SITE_URL') . '/storage/' . $imagePath;
            }
            $notif = [
                'status' => 'success',
                'message' => 'Checked successfully',
            ];
            return redirect()->back()->with($notif);
        }
    
        $notif = [
            'status' => 'failed',
            'message' => 'Checked fail'
        ];
    
        return redirect()->back()->with($notif);
    }
}
