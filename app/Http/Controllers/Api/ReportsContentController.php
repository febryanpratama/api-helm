<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\ReportsContent;
use App\Rating;
use App\Course;
use App\Company;

class ReportsContentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $data = ReportsContent::with('user')->where([
                'content_type'  => request('content_type'),
                'content_id'    => request('content_id')
            ])
            ->latest()
            ->get();

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
        // Set Validation
        $validator = Validator::make(request()->all(), [
            "content_type"  => "required|integer",
            "content_id"    => "required|integer",
            "report"        => "required",
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => false,
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check Reports
        $dataExists = ReportsContent::where([
                        'user_id'       => auth()->user()->id,
                        'content_type'  => request('content_type'),
                        'content_id'    => request('content_id')
                    ])
                    ->first();

        if ($dataExists) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah membuat laporan sebelumnya.'
            ]);
        }

        // Check Data
        if (request('content_type') == 0) {
            // Initialize
            $course = Course::where('id', request('content_id'))->first();

            if (!$course) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Produk dengan ID ('.request('content_id').') tidak ditemukan.'
                ]);
            }
        } else if (request('content_type') == 1) {
            // Initialize
            $rating = Rating::where('id', request('content_id'))->first();

            if (!$rating) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Rating dengan ID ('.request('content_id').') tidak ditemukan.'
                ]);
            }
        } else if (request('content_type') == 2) {
            // Initialize
            $store = Company::where('ID', request('content_id'))->first();

            if (!$store) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Toko dengan ID ('.request('content_id').') tidak ditemukan.'
                ]);
            }
        }

        $data = ReportsContent::create([
            'content_type'  => request('content_type'),
            'content_id'    => request('content_id'),
            'user_id'       => auth()->user()->id,
            'report'        => request('report')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
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
        $data = ReportsContent::with('user')->where(['content_id' => $id, 'user_id' => auth()->user()->id])->first();

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
