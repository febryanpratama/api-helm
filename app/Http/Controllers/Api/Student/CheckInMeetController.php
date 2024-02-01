<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CheckInMeetRequest;
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
    public function store(CheckInMeetRequest $request)
    {
        // Check Meet Detail
        $meet = MeetingRoom::where('id', $request->meet_id)->first();

        if ($meet) {
            // Check Meet Type
            if ($meet->is_online == 1) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Hanya bisa melakukan Check In dengan tipe Meeting Offline'
                ]);
            }

            // Check Meet Date
            if (date('Y-m-d H:i:s') <= date('Y-m-d H:i:s', $meet->time)) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Belum masuk waktu Check In'
                ]);
            }

            // Check User Course
            $userCourse = UserCourse::where(['course_id' => $meet->course_id, 'user_id' => auth()->user()->id])->first();

            if ($userCourse) {
                // Check Data Exists
                $checkInDataExists = CheckInMeet::where(['meet_id' => $request->meet_id, 'user_id' => auth()->user()->id])->first();

                if (!$checkInDataExists) {
                    $checkIn = CheckInMeet::create([
                        'meet_id' => $request->meet_id,
                        'user_id' => auth()->user()->id
                    ]);

                    return response()->json([
                        'status'    => 'success',
                        'message'   => 'Check In berhasil',
                        'data'      => $checkIn
                    ]);
                }

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda sudah melakukan Check In'
                ]);
            }

            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak terdaftar di Paket ini'
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Meeting dengan ID '.$request->meet_id.' Tidak terdaftar'
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
        // Initialize
        $checkIn = CheckInMeet::where(['meet_id' => $id, 'user_id' => auth()->user()->id])->first();
        $data    = [];

        if ($checkIn) {
            $row['id']                      = $checkIn->id;
            $row['meet_id']                 = $checkIn->meet_id;
            $row['user_id']                 = $checkIn->user_id;
            $row['user']                    = $checkIn->user;
            $row['check_in_date']           = $checkIn->created_at->format('d F Y H:i');
            $row['check_in_original_date']  = $checkIn->created_at;
            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Check In tersedia',
            'data'      => $data
        ]);
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
