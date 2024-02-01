<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ReportTaskRequest;
use App\TaskAttachment;

class ReportTaskController extends Controller
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
    public function store(ReportTaskRequest $request)
    {
        // Check Data Exists
        $taskAttachment = TaskAttachment::where(['user_id' => auth()->user()->id, 'task_id' => request('task_id')])->first();

        if ($taskAttachment) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan report sebelumnya',
                'data'      => [
                    'task_attachments_id' => $taskAttachment->id
                ]
            ]);
        }

        // Initialize
        $attachment = null;

        if (request()->file('report_file')) {
            $file       = request()->file('report_file');
            $md5_name   = uniqid().md5_file($file->getRealPath());
            $ext        = $file->getClientOriginalExtension();

            if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                $destination_path = public_path('storage/uploads/img/task/');
                $file->move($destination_path, "$md5_name.$ext");

                $attachment = TaskAttachment::create([
                    'task_id'   => request('task_id'),
                    'user_id'   => auth()->user()->id,
                    'type'      => 'image',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);

            } else if ($ext == 'mp4' || $ext == 'avi') {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'File Extension not Supported!'
                ]);
            } else {
                $destination_path = public_path('storage/uploads/file/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = TaskAttachment::create([
                    'task_id'   => request('task_id'),
                    'user_id'   => auth()->user()->id,
                    'type'      => 'file',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Report berhasil diunggah',
            'data'      => $attachment
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
