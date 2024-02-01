<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TaskMentorAssessmentRequest;
use App\TaskMentorAssessment;
use App\TaskAttachment;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskReportStudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $reports = TaskAttachment::where(['task_id' => request()->task_id, 'is_report' => 'y'])->latest()->get();
        $data    = [];

        // Custom Paginate
        $reports = $this->paginate($reports, 20, null, ['path' => $request->fullUrl()]);

        foreach ($reports as $val) {
            // Initialize
            $row['task_attachments_id']     = $val->id;
            $row['student']                 = $val->user;
            $row['task_id']                 = $val->task_id;
            $row['task']                    = $val->task;
            $row['path']                    = $val->path;

            // Mentors Assesment
            $tma = TaskMentorAssessment::where('task_attachment_id', $val->id)->first();

            $row['mentors_assessment_id']   = ($tma) ? $tma->id : null;

            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Tugas.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $reports->currentPage(),
                'from'              => 1,
                'last_page'         => $reports->lastPage(),
                'next_page_url'     => $reports->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $reports->perPage(),
                'prev_page_url'     => $reports->previousPageUrl(),
                'total'             => $reports->total()
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
    public function store(TaskMentorAssessmentRequest $request)
    {
        // Check Data Exists
        $dataExists = TaskMentorAssessment::where(['task_attachment_id' => request('task_attachments_id')])->first();

        if ($dataExists) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan penilaian untuk task_attachments_id '.request('task_attachments_id')
            ]);
        }

        // Initialize
        $taskrs = TaskMentorAssessment::create([
            'task_attachment_id' => request('task_attachments_id'),
            'score'              => request('score'),
            'response'           => request('response')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data',
            'data'      => $taskrs
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(TaskMentorAssessment $taskmentorassessment)
    {
        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $taskmentorassessment
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
    public function update(TaskMentorAssessmentRequest $request, TaskMentorAssessment $taskmentorassessment)
    {
        $taskmentorassessment->update([
            'score'     => request('score'),
            'response'  => request('response')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data',
            'data'      => $taskmentorassessment
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskMentorAssessment $taskmentorassessment)
    {
        $taskmentorassessment->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data'
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
