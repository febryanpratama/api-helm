<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CheckInMeet;
use App\MeetingRoom;
use App\UserCourse;

class CheckInMeetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check Meet Detail
        $meet = MeetingRoom::where('id', $request->meetId)->first();

        if ($meet) {
            // Check Meet Type
            if ($meet->is_online == 1) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Hanya bisa melakukan Check In dengan tipe Meeting Offline'
                ]);
            }

            // Check Meet Date
            if (date('Y-m-d H:i:s') <= date('Y-m-d H:i:s', $meet->time)) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Belum masuk waktu Check In'
                ]);
            }

            // Check User Course
            $userCourse = UserCourse::where(['course_id' => $meet->course_id, 'user_id' => auth()->user()->id])->first();

            if ($userCourse) {
                // Check Data Exists
                $checkInDataExists = CheckInMeet::where(['meet_id' => $request->meetId, 'user_id' => auth()->user()->id])->first();

                if (!$checkInDataExists) {
                    $checkIn = CheckInMeet::create([
                        'meet_id' => $request->meetId,
                        'user_id' => auth()->user()->id
                    ]);

                    return response()->json([
                        'status'    => true,
                        'message'   => 'Check In berhasil',
                        'data'      => $checkIn
                    ]);
                }

                return response()->json([
                    'status'    => false,
                    'message'   => 'Anda sudah melakukan Check In'
                ]);
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Anda tidak terdaftar di Paket ini'
            ]);
        }

        return response()->json([
            'status'    => false,
            'message'   => 'Meeting dengan ID '.$request->meetId.' Tidak terdaftar'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
