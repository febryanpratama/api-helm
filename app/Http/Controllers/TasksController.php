<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;
use App\TaskAttachment;
use App\Majors;
use App\Course;
use App\UserCourse;
use App\TaskMentorAssessment;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $tasks = Task::where('major_id', request('majorId'))->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $tasks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Validate Majors Id
        if (request('majorId')) {
            // Check User Course == User Login
            $major = Majors::where('id', request('majorId'))->first();

            if ($major) {
                // Initialize
                $course = Course::where('id', $major->IDCourse)->first();

                if ($course && $course->user_id == auth()->user()->id) {
                    return view('tasks.create');
                }
            }

            return redirect()->back();
        }

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (request()->file('upload_file')) {
            // Initialize
            $file       = request()->file('upload_file');
            $md5_name   = uniqid().md5_file($file->getRealPath());
            $ext        = $file->getClientOriginalExtension();
            $fileSize   = $file->getSize();

            if ($fileSize >= 300000) { // 3 MB
                return response()->json([
                    'status'  => false,
                    'message' => 'Size File yang di upload maksimal 3 MB'
                ]);
            }
        }

        $task = Task::create([
            'user_id'     => auth()->user()->id,
            'major_id'    => request()->major_id,
            'name'        => request()->task,
            'start_date'  => strtotime(request()->start_date . request()->start_time),
            'end_date'    => strtotime(request()->end_date . request()->end_time),
            'detail'      => request()->note
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
                    } else if ($ext == 'mp4' || $ext == 'avi') {
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
    public function show(Task $task)
    {
        // Initialize
        $listTaskAttachment = '';
        $taskAttachment     = TaskAttachment::where(['user_id' => auth()->user()->id, 'task_id' => $task->id])->first();

        if (auth()->user()->role_id == 1) {
            // Initialize
            $listTaskAttachment = TaskAttachment::where('task_id', $task->id)->paginate(10);
        }

        return view('tasks.show', compact('task', 'taskAttachment', 'listTaskAttachment'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $task->update([
            'name'        => request()->task,
            'start_date'  => strtotime(request()->start_date . request()->start_time),
            'end_date'    => strtotime(request()->end_date . request()->end_time),
            'detail'      => request()->note
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
                    } else if ($ext == 'mp4' || $ext == 'avi') {
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
    public function destroy(Task $task)
    {
        // Validate Account
        if ($task->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data gagal dihapus'
            ]);

            die;
        }

        // Check User Course
        $userCourse = UserCourse::where('course_id', $task->majors->IDCourse)->count();

        if ($userCourse > 0) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tugas tidak bisa di hapus'
            ]);

            die;
        }

        if ($task->taskAttachment) {
            // Unlink File
            $expPath = explode('/', $task->taskAttachment->path);

            @unlink('storage/uploads/'.$expPath[5].'/task/'.$expPath[7]);
        }

        $task->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }

    public function uploadReport()
    {
        // Initialize
        $attachment = null;

        if (request()->file('upload_file')) {
            $file       = request()->file('upload_file');
            $md5_name   = uniqid().md5_file($file->getRealPath());
            $ext        = $file->getClientOriginalExtension();

            if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                $destination_path = public_path('storage/uploads/img/task/');
                $file->move($destination_path, "$md5_name.$ext");

                $attachment = TaskAttachment::create([
                    'task_id'   => request('taskId'),
                    'user_id'   => auth()->user()->id,
                    'type'      => 'image',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);

            } else if ($ext == 'mp4' || $ext == 'avi') {
                $destination_path = public_path('storage/uploads/video/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = TaskAttachment::create([
                    'task_id'   => request('taskId'),
                    'user_id'   => auth()->user()->id,
                    'type'      => 'video',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);
            } else {
                $destination_path = public_path('storage/uploads/file/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = TaskAttachment::create([
                    'task_id'   => request('taskId'),
                    'user_id'   => auth()->user()->id,
                    'type'      => 'file',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Laporan berhasil diunggah'
        ]);
    }

    public function giveScore(TaskAttachment $taskattachment)
    {
        // Initialize
        $tma = TaskMentorAssessment::where('task_attachment_id', $taskattachment->id)->first();

        return view('tasks.give-score', compact('taskattachment', 'tma'));
    }

    public function giveScoreStore()
    {
        // Check Data Exists
        $dataExists = TaskMentorAssessment::where(['task_attachment_id' => request('task_attachments_id')])->first();

        if ($dataExists) {
            $dataExists->update([
                'score'     => request('score'),
                'response'  => request('response')
            ]);

            return response()->json([
                'status'    => true,
                'message'   => 'Berhasil mengubah data'
            ]);
        }

        // Initialize
        $taskrs = TaskMentorAssessment::create([
            'task_attachment_id' => request('task_attachments_id'),
            'score'              => request('score'),
            'response'           => request('response')
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menambahkan data',
            'data'      => $taskrs
        ]);
    }

    public function giveScoreDestroy(TaskMentorAssessment $taskmentorassessment)
    {
        $taskmentorassessment->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menghapus data'
        ]);
    }
}
