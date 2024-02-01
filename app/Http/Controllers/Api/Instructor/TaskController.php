<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskShowResource;
use App\Majors;
use App\Task;
use App\TaskAttachment;
use App\UserCourse;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $majors = Majors::where('ID', $request->session_id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Initialize
        $tasks = Task::where('major_id', request('session_id'))->get();
        $data  = [];

        // Custom Paginate
        $tasks = $this->paginate($tasks, 20, null, ['path' => $request->fullUrl()]);

        foreach ($tasks as $val) {
            // Initialize
            $row['id']          = $val->id;
            $row['session_id']  = $val->major_id;
            $row['name']        = $val->name;
            $row['details']     = $val->detail;
            $row['file']        = TaskAttachment::where(['task_id' => $val->id, 'is_report' => 'n'])->get();
            $row['created_at']  = $val->created_at;
            $row['updated_at']  = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Tugas.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $tasks->currentPage(),
                'from'              => 1,
                'last_page'         => $tasks->lastPage(),
                'next_page_url'     => $tasks->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $tasks->perPage(),
                'prev_page_url'     => $tasks->previousPageUrl(),
                'total'             => $tasks->total()
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
    public function store(TaskRequest $request)
    {
        // Initialize
        $majors = Majors::where('ID', $request->session_id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if (request()->file('upload_file')) {
            // Initialize
            $file       = request()->file('upload_file');
            $md5_name   = uniqid().md5_file($file->getRealPath());
            $ext        = $file->getClientOriginalExtension();
            $fileSize   = $file->getSize();

            if ($fileSize >= 3000000) { // 3 MB
                return response()->json([
                    'status'  => false,
                    'message' => 'Size File yang di upload maksimal 3 MB'
                ]);
            }
        }

        $task = Task::create([
            'user_id'     => auth()->user()->id,
            'major_id'    => request()->session_id,
            'name'        => request()->name,
            'detail'      => request()->details
        ]);

        if ($task) {
            if (request()->file('upload_file')) {
                // Initialize
                $file       = request()->file('upload_file');
                $md5_name   = uniqid().md5_file($file->getRealPath());
                $ext        = $file->getClientOriginalExtension();
                $fileSize   = $file->getSize();

                if ($fileSize <= 300000) { // 3 MB
                    // Check Extension
                    if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                        $destination_path = public_path('storage/uploads/img/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'image',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    } else if ($ext == 'mp4' || $ext == 'mkv') {
                        $destination_path = public_path('storage/uploads/video/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'video',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    } else if ($ext == 'pdf') {
                        $destination_path = public_path('storage/uploads/file/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'file',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    }
                }
            }
        }

        return new TaskResource($task);
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
        $task = Task::where('id', $id)->first();

        if (!$task) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tugas tidak ditemukan',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        return new TaskShowResource($task);
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
    public function update(TaskRequest $request, $id)
    {
        // Initialize
        $majors = Majors::where('ID', $request->session_id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Initialize
        $task = Task::where('id', $id)->first();

        $task->update([
            'name'        => request()->name,
            'detail'      => request()->details
        ]);

        if ($task) {
            if (request()->file('upload_file')) {
                // Initialize
                $file       = request()->file('upload_file');
                $md5_name   = uniqid().md5_file($file->getRealPath());
                $ext        = $file->getClientOriginalExtension();
                $fileSize   = $file->getSize();

                if ($fileSize <= 300000) { // 3 MB
                    // Delete File Exists
                    $taskAttachment = TaskAttachment::where('task_id', $task->id)->first();

                    if ($taskAttachment) {
                        // Initialize
                        $expPath = explode('/', $taskAttachment->path);

                        @unlink('storage/uploads/'.$expPath[5].'/task/'.$expPath[7]);

                        $taskAttachment->delete();
                    }

                    // Check Extension
                    if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                        $destination_path = public_path('storage/uploads/img/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'image',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    } else if ($ext == 'mp4' || $ext == 'mkv') {
                        $destination_path = public_path('storage/uploads/video/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'video',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    } else if ($ext == 'pdf') {
                        $destination_path = public_path('storage/uploads/file/task/');
                        $file->move($destination_path,"$md5_name.$ext");
                    
                        $attachment = TaskAttachment::create([
                            'user_id'   => auth()->user()->id,
                            'task_id'   => $task->id,
                            'type'      => 'file',
                            'path'      => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                            'is_report' => 'n'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'Size File yang di upload maksimal 3 MB'
                    ]);

                    die;
                }
            }
        }

        return new TaskResource($task);
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
        $task = Task::where('id', $id)->first();

        if (!$task) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tugas tidak ditemukan',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validate Account
        if ($task->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tugas gagal dihapus'
            ]);
        }

        // Check User Course
        $userCourse = UserCourse::where('course_id', $task->majors->IDCourse)->count();

        if ($userCourse > 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tugas tidak bisa di hapus'
            ]);
        }

        if ($task->taskAttachment) {
            // Unlink File
            $expPath = explode('/', $task->taskAttachment->path);

            @unlink('storage/uploads/'.$expPath[5].'/task/'.$expPath[7]);
        }

        $task->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus Tugas.'
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
