<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TaskMentorAssessment;
use App\TaskAttachment;

class TaskMentorAssessmentResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $taskAttachment = TaskAttachment::where(['user_id' => auth()->user()->id, 'id' => request('task_attachment_id')])->first();

        if (!$taskAttachment) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda belum mengumpulkan tugas'
            ]);
        }

        // Get Score
        $score = TaskMentorAssessment::where('task_attachment_id', request('task_attachment_id'))->first();

        if ($score) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil mendapatkan nilai tugas',
                'data'      => $score
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Tugas anda belum di nilai'
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
        //
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
