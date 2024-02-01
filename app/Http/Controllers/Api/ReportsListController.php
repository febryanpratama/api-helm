<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ReportsContent;
use App\Course;
use App\Rating;
use App\Company;

class ReportsListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check Is_admin_access
        if (!auth()->user()->is_admin_access) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Akun Anda tidak memiliki akses. (is_admin_access must true or 1)'
            ]);
        }

        // Initialize
        $reports = ReportsContent::groupBy('content_id')->latest()->get();
        $data    = [];

        foreach($reports as $val) {
            // Initialize
            $row['id']           = $val->id;
            $row['content_type'] = $val->content_type;
            $row['content_id']   = $val->content_id;

            if ($val->content_type == 0) {
                // Initialize
                $course = Course::where('id', $val->content_id)->first();

                if ($course) {
                    $row['content_details'] = $course;
                    $row['is_take_down']    = $course->is_take_down;
                }
            } else if ($val->content_type == 1) {
                // Initialize
                $rating = Rating::where('id', $val->content_id)->first();

                if ($rating) {
                    $row['content_details'] = $rating;
                    $row['is_take_down']    = $rating->is_take_down;
                }
            } else if ($val->content_type == 2) {
                // Initialize
                $company = Company::where('ID', $val->content_id)->first();

                if ($company) {
                    $row['content_details'] = $company;
                    $row['is_take_down']    = $company->IsTakeDown;
                }
            }

            $row['total_user_report'] = ReportsContent::where('content_id', $val->content_id)->count();
            $row['created_at']        = $val->created_at;
            $row['updated_at']        = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
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
    public function store($id)
    {
        // Check Is_admin_access
        if (!auth()->user()->is_admin_access) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Akun Anda tidak memiliki akses. (is_admin_access must true or 1)'
            ]);
        }

        // Initialize
        $report = ReportsContent::where('id', $id)->first();

        if ($report) {
            if ($report->content_type == 0) {
                // Check Course
                $course = Course::where('id', $report->content_id)->first();

                if ($course) {
                    $course->update([
                        'is_take_down' => 1
                    ]);
                }
            } else if ($report->content_type == 1) {
                // Check Rating
                $rating = Rating::where('id', $report->content_id)->first();

                if ($rating) {
                    $rating->update([
                        'is_take_down' => 1
                    ]);
                }
            } else if ($report->content_type == 2) {
                // Initialize
                $company = Company::where('ID', $report->content_id)->first();

                if ($company) {
                    $company->update([
                        'IsTakeDown' => 1
                    ]);
                }
            }

            // Update All Report Status
            $allReport = ReportsContent::where([
                            'content_id'    => $report->content_id,
                            'content_type'  => $report->content_type
                        ])->update([
                            'is_approve' => 1
                        ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Take Down data Berhasil.',
            'data'      => [
                'id'        => $id,
                'is_report' => 1
            ]
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
        // Check Is_admin_access
        if (!auth()->user()->is_admin_access) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Akun Anda tidak memiliki akses. (is_admin_access must true or 1)'
            ]);
        }

        // Initialize
        $data = ReportsContent::with('user')->where(['content_id' => $id])->latest()->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
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
