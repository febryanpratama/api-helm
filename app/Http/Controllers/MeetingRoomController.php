<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;
use App\MeetingRoom;
use App\Majors;
use App\CheckInMeet;

class MeetingRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data = MeetingRoom::where('session_id', request('sessionId'))->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Majors $majors)
    {
        // Initialize
        $randomString = $this->generateRandomString(4);

        return view('meeting-room.create', compact('majors','randomString'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Initialize
        $expLink = strtotime(date('Y-m-d H:i:s', strtotime('+22 hourse')));

        if (request('date_exp')) {
            $expLink = strtotime(request('date_exp').request('time_exp'));
        }
        
        $data = [
            'user_id'               => auth()->user()->id,
            'course_id'             => request('course_id'),
            'session_id'            => request('session_id'),
            'theory_id'             => request('theory_id'),
            'name'                  => request('name'),
            'description'           => request('description'),
            'time'                  => strtotime(request('date').request('time')),
            'link'                  => request('link'),
            'link_expiration_time'  => $expLink
        ];

        if (request('is_online') == 0) {
            // Initialize
            $data = [
                'user_id'       => auth()->user()->id,
                'course_id'     => request('course_id'),
                'session_id'    => request('session_id'),
                'theory_id'     => request('theory_id'),
                'name'          => request('name'),
                'description'   => request('description'),
                'address'       => request('address'),
                'time'          => strtotime(request('date').request('time')),
                'is_online'     => 0
            ];
        }

        // Insert To Table
        MeetingRoom::create($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil ditambahkan'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(MeetingRoom $meetingroom)
    {
        // Initialize
        $checkIn = CheckInMeet::where(['meet_id' => $meetingroom->id, 'user_id' => auth()->user()->id])->first();

        return view('meeting-room.show', compact('meetingroom', 'checkIn'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(MeetingRoom $meetingroom)
    {
        // Initialize
        $randomString = $this->generateRandomString(4);

        return view('meeting-room.edit', compact('meetingroom', 'randomString'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MeetingRoom $meetingroom)
    {
        // Initialize
        $expLink = strtotime(date('Y-m-d H:i:s', strtotime('+22 hourse')));

        if (request('date_exp')) {
            $expLink = strtotime(request('date_exp').request('time_exp'));
        }

        // Initialize
        $data = [
            'name'                  => request('name'),
            'description'           => request('description'),
            'link'                  => request('link'),
            'is_online'             => 1,
            'time'                  => strtotime(request('date').request('time')),
            'link_expiration_time'  => $expLink
        ];

        if (request('is_online') == 0) {
            // Initialize
            $data = [
                'name'          => request('name'),
                'description'   => request('description'),
                'address'       => request('address'),
                'time'          => strtotime(request('date').request('time')),
                'is_online'     => 0
            ];
        }

        // Insert To Table
        $meetingroom->update($data);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(MeetingRoom $meetingroom)
    {
        $meetingroom->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }

    function generateRandomString($length = 10) {
        // Initialize
        $characters         = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength   = strlen($characters);
        $randomString       = '';
        
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
