<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\AgreementLetter;

class AgreementLetterController extends Controller
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
        // Set Validation
        $validator = Validator::make(request()->all(), [
            "course_id"         => "required|integer",
            "agreement_letter"  => "required",
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => false,
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check SP Submission
        $submissionSP = AgreementLetter::where(['user_id' => auth()->user()->id, 'course_id' => request('course_id'), 'status' => 0])->first();

        if ($submissionSP) {
            // Initialize
            $explodePath = explode('/', $submissionSP->agreement_letter);

            @unlink('storage/uploads/checkout/sp/'.request('course_id').'/'.$explodePath[9]);

            // Delete Data
            $submissionSP->delete();
        }

        // Initialize
        $fileAL = request()->file('agreement_letter');
        $extFT  = $fileAL->getClientOriginalExtension();

        // Check Extension
        if ($extFT != 'pdf') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension agreement_letter File Not Supported!'
            ]);
        }

        // Upload File
        $path = $fileAL->store('uploads/checkout/sp/'.request('course_id'), 'public');

        $data = AgreementLetter::create([
            'user_id'          => auth()->user()->id,
            'course_id'        => request('course_id'),
            'agreement_letter' => env('SITE_URL').'/storage/'.$path
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil melakukan pengajuan.',
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
        // Initialize
        $data = AgreementLetter::where('id', $id)->first();

        if ($data) {
            $explodePath = explode('/', $data->agreement_letter);

            @unlink('storage/uploads/checkout/sp/'.$explodePath[7].'/'.$explodePath[9]);
            $data->delete();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
