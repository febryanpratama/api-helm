<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyMotivationController extends Controller
{
    public function store()
    {
        $motivation = \App\CheckMotivation::updateOrCreate(
            [
                'id' => request()->id
            ], [
            'company_id' => auth()->user()->company_id,
            'motivation' => request()->motivation
        ]);
    
        if ($motivation) {
            // Check ajax request
            if(request()->ajax()){
                // Check Create or Not
                if ($motivation->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Motivasi berhasil disimpan'
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Motivasi berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Motivation created successfully',
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Not
            if ($motivation->wasRecentlyCreated) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Motivasi gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Motivasi gagal diperbaharui'
            ]);

            die;
        }
    
        $notif = [
            'status' => 'failed',
            'message' => 'Task created fail'
        ];
    
        return redirect()->back()->with($notif);
    }

    public function delete(\App\CheckMotivation $motivation)
    {
        if ($motivation->delete()) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Motivasi berhasil dihapus'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Deleted successfully',
            ];
            
            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Motivasi gagal dihapus'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Deleted Fail'
        ];
        
        return redirect()->back()->with($notif);
    }
}
