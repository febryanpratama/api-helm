<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CourseQuotation;
use DB;

class CourseQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data = [];

        if (auth()->user()->role_id == 1) {
            $cq = CourseQuotation::where(['company_id' => auth()->user()->company_id])->get();
        } else {
            $cq = CourseQuotation::where(['user_id' => auth()->user()->id])->get();
        }

        foreach ($cq as $val) {
            $row['id']                  = $val->id;
            $row['user_id']             = $val->user_id;
            $row['status']              = ($val->status) ? 'Disetujui' : 'Pending';
            $row['req_json']            = json_decode($val->req_json, true);
            $row['final_price_json']    = json_decode($val->final_price_json, true);

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
    public function store(Request $request)
    {
        // Initialize
        $requestData = request()->all();

        if ($requestData) {
            // Initialize
            $quotations = $requestData['quotations'];
            $course     = Course::where('id', $requestData['course_id'])->first();

            if (!$course) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Kursus tidak terdaftar.'
                ]);
            }

            foreach ($quotations as $key => $quotation) {
                // Insert To History Quotation
                DB::table('history_quotation')->insert([
                    'course_id' => $requestData['course_id'],
                    'subject'   => $quotation['subject']
                ]);
            }

            // Insert Course Quotation
            $courseQotation = [
                'user_id'    => auth()->user()->id,
                'course_id'  => $requestData['course_id'],
                'qty'        => $requestData['qty'],
                'company_id' => $course->company_id,
                'req_json'   => json_encode($quotations)
            ];

            $cq = CourseQuotation::create($courseQotation);

            if ($cq) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Berhasil mengajukan quotation.',
                    'data'      => $cq
                ]);
            }
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Gagal mengajukan quotation.'
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
