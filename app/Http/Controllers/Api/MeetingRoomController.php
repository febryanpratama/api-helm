<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MeetingRoomRequest;
use App\Http\Requests\MeetingRoomUpdateRequest;
use App\Majors;
use App\MeetingRoom;
use App\UserCourse;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MeetingRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!request('session_id')) {
            return response()->json([
                'message'   => 'The given data was invalid.',
                'errors'    => [
                                'session_id' => [ 'Session Id dibutuhkan.' ]
                            ]
            ]);
        }

        // Check Session
        $session = Majors::where('ID', request('session_id'))->first();

        if (!$session) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data (session_id) tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Check Access
        if (auth()->user()->role_id == 1) {
            if ($session->IDCompany != auth()->user()->company_id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak memiliki akses!',
                    'data'      => [
                       'error_code' => 'not_accessible'
                    ]
                ]);
            }
        } elseif (auth()->user()->role_id == 6) {
            // Get Users Registered In Course
            $majors = Majors::where(['ID' => request('session_id')])->first();

            if ($majors) {
                $users = UserCourse::where(['course_id' => $majors->IDCourse, 'user_id' => auth()->user()->id])->first();

                if (!$users) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Anda tidak memiliki akses!',
                        'data'      => [
                           'error_code' => 'not_accessible'
                        ]
                    ]);
                }
            }
        }

        // Initialize
        $meeting = MeetingRoom::where('session_id', request('session_id'))->latest()->get();
        $data    = [];

        // Custom Paginate
        $meeting = $this->paginate($meeting, 20, null, ['path' => $request->fullUrl()]);

        foreach ($meeting as $val) {
            // Initialize
            $row['id']                      = $val->id;
            $row['name']                    = $val->name;
            $row['description']             = $val->description;
            $row['is_online']               = $val->is_online;
            $row['link']                    = $val->link;
            $row['address']                 = $val->address;
            $row['time']                    = $val->time;
            $row['link_expiration_time']    = $val->link_expiration_time;
            $row['custom_time']             = ($val->time) ? date('m/d/Y H:i', $val->time) : null;
            $row['custom_time_expiration']  = ($val->link_expiration_time) ? date('m/d/Y H:i', $val->link_expiration_time) : null;
            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data meeting room.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $meeting->currentPage(),
                'from'              => 1,
                'last_page'         => $meeting->lastPage(),
                'next_page_url'     => $meeting->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $meeting->perPage(),
                'prev_page_url'     => $meeting->previousPageUrl(),
                'total'             => $meeting->total()
            ]
        ]);
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
    public function store(MeetingRoomRequest $request)
    {
        // Check Session
        $session = Majors::where('ID', request('session_id'))->first();
        $link    = request('link');

        if (!$session) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data (session_id) tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if (request('is_online') == 0) {
            if (!request('address')) {
                return response()->json([
                    'message'   => 'The given data was invalid.',
                    'errors'    => [
                                    'address' => [ 'Alamat dibutuhkan.' ]
                                ]
                ]);
            }
        } else {
            // if (!request('link')) {
            //     return response()->json([
            //         'message'   => 'The given data was invalid.',
            //         'errors'    => [
            //                         'link' => [ 'Link dibutuhkan.' ]
            //                     ]
            //     ]);
            // }
            
            if (!$link) {
                // Initialize
                $randomString  = $this->generateRandomString(4);
                $link          = env('SITE_URL').'/meet/'.strtolower(str_replace(' ', '-', $session->Name)).'-'.$randomString;
            }
        }

        // Initialize
        $data = [
            'user_id'               => auth()->user()->id,
            'course_id'             => $session->IDCourse,
            'session_id'            => $session->ID,
            'name'                  => request('name'),
            'description'           => request('description'),
            'link'                  => $link,
            'time'                  => strtotime(request('time')),
            'link_expiration_time'  => strtotime(request('link_expiration_time'))
        ];

        if (request('is_online') == 0) {
            // Initialize
            $data = [
                'user_id'       => auth()->user()->id,
                'course_id'     => $session->IDCourse,
                'session_id'    => $session->ID,
                'name'          => request('name'),
                'description'   => request('description'),
                'address'       => request('address'),
                'time'          => strtotime(request('time')),
                'is_online'     => 0
            ];
        }

        // Insert To Table
        MeetingRoom::create($data);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data meeting room',
            'data'      => $data
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
        $meetingroom = MeetingRoom::where('id', $id)->first();

        if (!$meetingroom) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data (Meeting Room) tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data meeting room',
            'data'      => $meetingroom
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
    public function update(MeetingRoomUpdateRequest $request, $id)
    {
        // Initialize
        $meetingroom = MeetingRoom::where('id', $id)->first();
        $link        = request('link');

        if (!$meetingroom) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data (Meeting Room) tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if (request('is_online') == 0) {
            if (!request('address')) {
                return response()->json([
                    'message'   => 'The given data was invalid.',
                    'errors'    => [
                                    'address' => [ 'Alamat dibutuhkan.' ]
                                ]
                ]);
            }
        } else {
            // if (!request('link')) {
            //     return response()->json([
            //         'message'   => 'The given data was invalid.',
            //         'errors'    => [
            //                         'link' => [ 'Link dibutuhkan.' ]
            //                     ]
            //     ]);
            // }
            
            if (!$link) {
                // Initialize
                $randomString  = $this->generateRandomString(4);
                $link          = env('SITE_URL').'/meet/'.strtolower(str_replace(' ', '-', $session->Name)).'-'.$randomString;
            }
        }

        // Initialize
        $data = [
            'user_id'               => auth()->user()->id,
            'name'                  => request('name'),
            'description'           => request('description'),
            'link'                  => $link,
            'time'                  => strtotime(request('time')),
            'link_expiration_time'  => strtotime(request('link_expiration_time'))
        ];

        if (request('is_online') == 0) {
            // Initialize
            $data = [
                'user_id'       => auth()->user()->id,
                'name'          => request('name'),
                'description'   => request('description'),
                'address'       => request('address'),
                'time'          => strtotime(request('time')),
                'is_online'     => 0
            ];
        }

        // Insert To Table
        $meetingroom->update($data);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data meeting room',
            'data'      => $meetingroom
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Initialize
        $meetingroom = MeetingRoom::where('id', $id)->first();

        if (!$meetingroom) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data (Meeting Room) tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }
        
        $meetingroom->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data meeting room',
            'data'      => [
                'id'        => $id,
                'delete_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function generateRandomString($length = 10) {
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
