<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Chat;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use App\TaskUser;
use DB;
use App\Reward;
use App\Events\ChatNotif;
use App\Events\RealTimeNotif;

class TasksController extends Controller
{
    /* === Supervisor === */
    /**
     * Show the application Create.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function supervisorCreateTask()
    {
        $user = \App\User::where('role_id', 6)->orWhere('id', auth()->user()->id)->get();
        $supervisor = \App\User::whereNotNull('supervised_by')->where('supervised_by', auth()->user()->id)->get();
        $user = $user->merge($supervisor);
        return view('supervisor.task_create', compact('user'));
    }

    public function supervisorEditTask(\App\Task $task)
    {
        $user = \App\User::where('role_id', 6)->get();
        return view('supervisor.task_create', compact('user', 'task'));
    }

    public function supervisorDeleteTask(\App\Task $task)
    {
        $project_id = $task->project_id;
        if ($task->delete()) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Tugas berhasil dihapus'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Deleted successfully',
                'project_id' => $project_id,
            ];

            return redirect()->back()->with($notif);
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Deleted Fail'
        ];

        return redirect()->back()->with($notif);
    }
    
    public function store()
    {
        $task = \App\Task::updateOrCreate(
            [
                'id' => request()->id
            ], [
            'name'              => request()->task,
            'assigned_by'       => auth()->user()->id,
            'assigned_to'       => request()->assigned_to[0],
            'start_date'        => strtotime(request()->start_date . request()->start_time),
            'end_date'          => strtotime(request()->end_date . request()->end_time),
            'detail'            => request()->note,
            'project_id'        => request()->project,
            'background_color'  => request()->background_color
        ]);

        if ($task) {


            if (request()->id) {
                $task->users()->detach(request()->assigned_to);
                $task->subjects()->detach(request()->subject);
            }

            $task->users()->attach(request()->assigned_to);
            $task->subjects()->attach(request()->subject);

            if (!request()->id) {
                if (request()->assigned_to) {
                    $user_name = '';
                    for ($i=0; $i < count(request()->assigned_to) ; $i++) { 
                        $user = \App\User::find(request()->assigned_to[$i]);

                        // send chat notif
                        if (auth()->user()->id != request()->assigned_to[$i]) {
                            $user = \App\User::find(request()->assigned_to[$i]);
                            $conversation = \DB::table('chat_conversations')->where('is_notif', 'y')->first();

                            if ($conversation) {
                                $conversation = Chat::conversations()->getById($conversation->id);
                                $check = \DB::table('chat_participation')->where('conversation_id', $conversation->id)->where('messageable_id', $user->Id)->first();
                                if ($check) {
                                    $add_participants = Chat::conversation($conversation)->addParticipants([$user]);

                                    $user->notify(new \App\Notifications\GroupChat($user->id, auth()->user(), $conversation));
                                }
                            }
                            $message = auth()->user()->name . ' menugaskan anda untuk mengerjakan tugas ' . $task->name;
                            $receiver = $user->id;
                            $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $task->id), $user->id);
                        }

                        // if ($user->id != auth()->user()->id) {
                        //     $user->notify(new \App\Notifications\Task($user->id, auth()->user(), $task));

                        //     OneSignal::sendNotificationUsingTags(
                        //         "ada tugas baru nih tugas-$task->name",
                        //         array(
                        //             ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $user->id],
                        //         ),
                        //         $url = route('todo.detail_task', $task->id),
                        //         $data = null,
                        //         $buttons = null,
                        //         $schedule = null
                        //     );
                        // }

                        $user_name .= $user->name . ', ';
                    }

                    // send chat notif to created task
                    $message = 'Anda menugaskan ' . $user_name . ' untuk mengerjakan tugas ' . $task->name;
                    $receiver = auth()->user()->id;
                    $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $task->id), auth()->user()->id);

                    // Pusher Notification
                    // event(new ChatNotif($chat['participants']));

                    // Pusher Notification
                    // event(new RealTimeNotif($chat['message']));
                }
            }

            if (request()->file('upload_file')) {
                // Initialize
                $file       = request()->file('upload_file');
                $md5_name   = uniqid().md5_file($file->getRealPath());
                $ext        = $file->getClientOriginalExtension();
                $fileSize   = $file->getSize();

                // Check Account
                if (auth()->user()->is_demo == 1) {
                    if ($fileSize <= 100000) { // 1 MB
                        // Check Extension
                        if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                            $destination_path = public_path('storage/uploads/img/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'image',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        } else if ($ext == 'mp4' || $ext == 'avi') {
                            $destination_path = public_path('storage/uploads/video/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'video',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        } else if ($ext == 'pdf') {
                            $destination_path = public_path('storage/uploads/file/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'file',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        }
                    }
                } else {
                    if ($fileSize <= 300000) { // 3 MB
                        // Check Extension
                        if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                            $destination_path = public_path('storage/uploads/img/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'image',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        } else if ($ext == 'mp4' || $ext == 'avi') {
                            $destination_path = public_path('storage/uploads/video/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'video',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        } else if ($ext == 'pdf') {
                            $destination_path = public_path('storage/uploads/file/task/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\TaskAttachment::create([
                                'task_id' => $task->id,
                                'type' => 'file',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                                'is_report' => 'n',
                            ]);
                        }
                    }
                }
            }

            if (request('reward_type') != 0) {
                // Check Reward
                $rewardExists = Reward::where('task_id', $task->id)->first();

                if ($rewardExists) {
                    // Update Reward
                    Reward::where('task_id', $task->id)->update([
                        'reward_type'   => request('reward_type'),
                        'reward_value'  => request('reward_value')
                    ]);
                } else {
                    // Insert Reward
                    Reward::create([
                        'task_id'       => $task->id,
                        'reward_type'   => request('reward_type'),
                        'reward_value'  => request('reward_value')
                    ]);
                }
            }

            // Check ajax request
            if(request()->ajax()){
                // Check Create or Not
                if ($task->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Tugas berhasil disimpan',
                        'data'      => $task
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Tugas berhasil diperbaharui',
                    'data'      => $task
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Task created successfully',
                'project_id' => $task->project_id,
                'task_id' => $task->id,
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Not
            if ($task->wasRecentlyCreated) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Tugas gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Tugas gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Task created fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function edit(\App\Task $task)
    {
        // Initialize
        $user        = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor  = \App\User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)           ->get();
        $assign_user = $user->merge($supervisor);

        $html = '';

        foreach ($assign_user as $val) {
            // Initialize
            $selected = '';
            
            if ($task->project_id == $val->id) {
                $selected = 'selected';
            }

            $html .= '<option value="'.$val->id.'" '.$selected.'>'.$val->name.'</option>';
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => [
                'task'          => $task,
                'assignUser'    => $html
            ]
        ]);
    }

    public function reply(\App\Task $task)
    {
        $task_reply = \App\TaskReply::create([
            'task_id' => $task->id,
            'user_id' => auth()->user()->id,
            'reply' => request()->reply,
        ]);

        if (request()->file('reply_upload')) {
            $file = request()->file('reply_upload');
            $md5_name = uniqid().md5_file($file->getRealPath());
            $ext = $file->getClientOriginalExtension();
            // dd($ext);

            if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                $destination_path = public_path('storage/uploads/img/taskreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskReplyAttachment::create([
                    'task_reply_id' => $task_reply->id,
                    'type' => 'image',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/img/taskreply/$md5_name.$ext",
                ]);

            } else if ($ext == 'mp4' || $ext == 'avi') {
                $destination_path = public_path('storage/uploads/video/taskreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskReplyAttachment::create([
                    'task_reply_id' => $task_reply->id,
                    'type' => 'video',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/video/taskreply/$md5_name.$ext",
                ]);
            } else {
                $destination_path = public_path('storage/uploads/file/taskreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskReplyAttachment::create([
                    'task_reply_id' => $task_reply->id,
                    'type' => 'file',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/file/taskreply/$md5_name.$ext",
                ]);
            }
        }

        if ($task_reply) {
            $get_user = $task->users()->get();

            if (count($get_user) > 0) {
                foreach ($get_user as $value) {

                    if ($value->id != auth()->user()->id) {
                        $value->notify(new \App\Notifications\TaskDiscuss($value->id, auth()->user(), $task));
                        OneSignal::sendNotificationUsingTags(
                            "ada diskusi baru nih di tugas-$task->name",
                            array(
                                ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $value->id],
                            ),
                            $url = route('todo.detail_task', $task->id),
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );
                    }
                }
            }

            $notif = [
                'status' => 'success',
                'message' => 'Reply successfully'
            ];
            return redirect()->back()->with($notif);
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Reply fail'
        ];

        return redirect()->back()->with($notif);
    }

    /* === Member === */
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function indexMember()
    {
        $task = auth()->user()->tasks;
        $message = \App\Message::all();
        $list_conversation = auth()->user()->listUserConversation();
        $user = \App\User::whereIn('role_id', [6,8])->where('id', '!=', auth()->user()->id)->get();
        $category_knowledge = \App\Knowledge::select('category')->groupBy('category')->get();
        $knowledge_public = \App\Knowledge::where('is_private', 'n')->orderBy('id', 'desc')->get();

        $my_knowledge = \App\Knowledge::where('is_private', 'y')->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();
        return view('member.task.index-member', compact('task', 'message', 'list_conversation', 'user', 'category_knowledge', 'knowledge_public', 'my_knowledge'));
    }

    public function uploadReport(\App\Task $task)
    {
        $attachment = null;
        if (request()->file('upload')) {
            $file = request()->file('upload');
            $md5_name = uniqid().md5_file($file->getRealPath());
            $ext = $file->getClientOriginalExtension();
            // dd($ext);

            if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                $destination_path = public_path('storage/uploads/img/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskAttachment::create([
                    'task_id'   => $task->id,
                    'user_id'   => auth()->user()->id,
                    'type'      => 'image',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/img/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);

            } else if ($ext == 'mp4' || $ext == 'avi') {
                $destination_path = public_path('storage/uploads/video/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskAttachment::create([
                    'task_id'   => $task->id,
                    'user_id'   => auth()->user()->id,
                    'type'      => 'video',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/video/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);
            } else {
                $destination_path = public_path('storage/uploads/file/task/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TaskAttachment::create([
                    'task_id'   => $task->id,
                    'user_id'   => auth()->user()->id,
                    'type'      => 'file',
                    'path'      => env('SITE_URL') . '/storage/' . "uploads/file/task/$md5_name.$ext",
                    'is_report' => 'y'
                ]);
            }
        }

        if ($attachment) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Laporan berhasil diunggah'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Upload successfully'
            ];
            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Laporan gagal diunggah'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Upload fail'
        ];

        return redirect()->back()->with($notif);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function supervisor()
    {
        // assigned_by / menugaskan
        $task = \App\Task::where('assigned_by', auth()->user()->id)->get();

        // assigned / ditugaskan
        $assigned_task = auth()->user()->tasks;
        $message = \App\Message::all();

        $list_conversation = auth()->user()->listUserConversation();

        $user = \App\User::whereIn('role_id', [6,8])->where('id', '!=', auth()->user()->id)->get();

        $category_knowledge = \App\Knowledge::select('category')->groupBy('category')->get();

        $knowledge_public = \App\Knowledge::where('is_private', 'n')->orderBy('id', 'desc')->get();

        $my_knowledge = \App\Knowledge::where('is_private', 'y')->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();

        $user = \App\User::where('role_id', 6)->orWhere('id', auth()->user()->id)->get();
        $supervisor = \App\User::whereNotNull('supervised_by')->where('supervised_by', auth()->user()->id)->get();
        $assign_user = $user->merge($supervisor);

        // data project
        $projects = \App\Project::all();

        // add user client
        $client_user = \App\User::where('role_id', 9)->get();

        return view('supervisor.home', compact('task', 'message', 'assigned_task', 'list_conversation', 'user', 'category_knowledge', 'knowledge_public', 'my_knowledge', 'assign_user', 'client_user', 'projects'));
    }

    public function download($id)
    {
        $media = \App\TaskAttachment::where('task_id', $id)->orderBy('id', 'desc')->first();

        $path_file = str_replace(env('SITE_URL').'/', '', $media->path);

        if (is_file(public_path($path_file))) {
            return response()->download(public_path($path_file));
        } else {
            return false;
        }
    }

    /* === Main === */
    public function index(\App\Task $task)
    {
        // Initiailze
        $taskUser       = TaskUser::where('user_id', auth()->user()->id)->whereNotIn('task_id', [$task->id])->pluck('task_id');
        $bgColor        = ($task->project) ? $task->project->background_color : '';
        $project        = $task->project;

        // get all data subject user login
        $subject_id     = auth()->user()->getDivisionMajorsSubject();
        $task_subject   = \App\Task::whereHas('subjects', function($q) use($subject_id) {
                                $q->whereIn('subjects.ID', $subject_id);
                            })->with('subjects')->get();
        $task           = \App\Task::where('id', $task->id)->get();
        $getTask        = \App\Task::doesntHave('subjects')->whereIn('id', $taskUser)->paginate('24');
        $tasks          = $task->merge($getTask);
        $tasks          = $tasks->merge($task_subject);

        // filter task by subject
        if (request()->has('subject') && request()->get('subject') != '') {
            $subject_id = request()->get('subject');
            $task_subject   = \App\Task::whereHas('subjects', function($q) use($subject_id) {
                $q->whereIn('subjects.ID', $subject_id);
            })->with('subjects')->get();
            $tasks           = $task_subject;
        }
        // end filter
        
        $user           = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor     = \App\User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)              ->get();
        $assign_user    = $user->merge($supervisor);
        $data_project   = new \App\Project;
        $projects       = $data_project->checkCompany();

        // get all subject data users
        $subjects_users = auth()->user()->getDivisionMajorsSubjectData();

        return view('member.task.index', compact('tasks', 'task', 'assign_user', 'project', 'projects', 'bgColor', 'subjects_users', 'getTask'));
    }

    public function listActivity()
    {
        // Initialize
        $taskUser   = TaskUser::where('user_id', auth()->user()->id)->pluck('task_id');
        $tasks      = \App\Task::whereIn('id', $taskUser)->get();
        $todos      = \App\Todo::whereIn('task_id', $tasks)->pluck('id');
        $activities = \App\TodoActivity::whereIn('todo_id', $todos)->latest('updated_at')->get();

        $data = [];
        foreach ($activities as $val) {
            $row = [];
            $row['todo']        = ucfirst($val->todo->todo);
            $row['status']      = ucfirst($val->status);
            $row['user_name']   = ucfirst($val->user->name);
            $row['created_at']  = $val->created_at->format('d M y H:i');
            $row['updated_at']  = $val->updated_at->format('d M y H:i');
            $row['avatar']      = $val->user->avatar;
            $row['task']        = ucfirst($val->todo->task->name);

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    // Search Task
    public function searchTask()
    {
        // Initialize
        $q = request('search');

        // Check Search
        if ($q == 'task-from-me') {
            // Initialize
            $taskId = \App\Task::where('assigned_by', auth()->user()->id)->pluck('id');
            $tasks  = TaskUser::whereIn('task_id', $taskId)->groupBy('task_id')->get();
        } elseif ($q == 'me-task') {
            // Initialize
            $tasks  = TaskUser::where('user_id', auth()->user()->id)->with('task')->get();
        } else {
            // Initialize
            $taskMe         = TaskUser::where('user_id', auth()->user()->id)->with('task')->get();
            $taskId         = \App\Task::where('assigned_by', auth()->user()->id)->pluck('id');
            $taskFromMe     = TaskUser::whereIn('task_id', $taskId)->groupBy('task_id')->get();
            $tasks          = $taskMe->merge($taskFromMe);
        }

        $data = $this->_assignedTask($tasks);

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    private function _assignedTask($tasks) {
        // Initialize
        $val = [];

        foreach($tasks as $task) {
            // Initialize
            $row = [];
            $row['id']              = $task->task->id;
            $row['name']            = $task->task->name;
            $row['project_id']      = $task->task->project_id;

            if ($task->task->project) {
                $row['project_by']          = $task->task->project->title;
                $row['background_color']    = $task->task->project->background_color;
            } else {
                $row['project_by']          = '-';
                $row['background_color']    = '#36a8d9';
            }

            $row['assigned_by'] = $task->task->assigned_by;
            $usersTask          = '';

            // Get Users Assigned To In Task
            foreach ($task->task->users as $user) {
                $usersTask .= "<button class='btn btn-sm btn-info text-white mb-2 mr-2'>".$user->name."</button>";
            }

            $row['assigned_to']     = $usersTask;
            $row['start_date']      = date('d M y H:i', $task->task->start_date);
            $row['end_date']        = date('d M y H:i', $task->task->end_date);
            $row['start_date_num']  = $task->task->start_date;
            $row['end_date_num']    = $task->task->end_date;
            $row['report_path']     = $task->task->report_path;
            $row['detail']          = $task->task->detail;
            $row['created_at']      = $task->task->created_at->format('d M y H:i');
            $row['updated_at']      = $task->task->updated_at;
            $row['pivot_user_id']   = $task->task->pivot_user_id;
            $row['pivot_task_id']   = $task->task->pivot_task_id;

            // Check Progress %
            if (count($task->task->todos) > 0) {
                $percentage = (count($task->task->isDone())/count($task->task->todos)) * 100;
            } else {
                $percentage = '0';
            }

            // Check Task Attachment
            if ($task->task->taskAttachment) {
                $attachment     = $task->task->taskAttachment->type;
                $pathAttachment = $task->task->taskAttachment->path;
            } else {
                $attachment     = null;
                $pathAttachment = null;
            }

            $row['percentage']          = ceil($percentage);
            $row['attachment']          = $attachment;
            $row['pathAttachment']      = $pathAttachment;
            $row['assignedBy']          = $task->task->assignedBy->name;
            $row['users']               = $task->task->users;
            $row['todos']               = $task->task->todos;

            // init percentage
            if (!empty($task->task->isDone()) && count($task->task->todos) > 0) {
                $percentage = (count($task->task->isDone())/count($task->task->todos)) * 100;
            } else {
                $percentage = 0;
            }

            // Initialize
            $badgeColor = 'badge-danger text-white';
            $bgColor 	= 'bg-danger';
            $textProgress = 'text-dark';

            if (ceil($percentage) >= 10 && ceil($percentage) <= 60) {
                $badgeColor = 'badge-warning text-dark';
                $bgColor 	= 'bg-warning text-dark';
                $textProgress = 'text-white';
            } elseif (ceil($percentage) >= 60 && ceil($percentage) <= 80) {
                $badgeColor = 'badge-info text-white';
                $bgColor 	= 'bg-info';
                $textProgress = 'text-white';
            } elseif (ceil($percentage) > 80) {
                $badgeColor = 'badge-success text-white';
                $bgColor 	= 'bg-success';
                $textProgress = 'text-white';
            }

            $row['user_count'] = $task->task->users->count();
            $row['task_done_count'] = count($task->task->isDone());
            $row['todo_count'] = $task->task->todos->count();
            $row['badge_color'] = $badgeColor;
            $row['bg_color'] = $bgColor;
            $row['text_progress'] = $textProgress;

            // Check Status
            if ($task->task->status) {
                $row['status'] = $task->task->status;
            } else {
                $row['status'] = 0;
            }

            $val[] = $row;
        }

        // $data = collect($val)->sortBy('percentage')->toArray();  // <- By Percentage
        $data = collect($val)->sortBy('status')->toArray();

        return $data;
    }

    public function updateStatus(\App\Task $task)
    {
        $task->update(['status' => '1']);

        return response()->json([
            'status'    => true,
            'message'   => 'Status berhasil diperbaharui'
        ]);
    }

    public function uploadImage()
    {
        $path = request('file')->store('uploads/tinymce', 'public');

        return response()->json(['location' =>  env('SITE_URL') . '/storage/' . $path]);
    }

    public function projects()
    {
        if (request('taskId')) {
            $task      = \App\Task::where('id', request('taskId'))->first();
            $projectId = $task->project_id;
        } elseif (request('projectId')) {
            $projectId = request('projectId');
        } else {
            $projectId = '';
        }

        // Initialize
        $data_project = new \App\Project;
        $projects     = $data_project->checkCompany();

        $html = '<option value="">--- Pilih ---</option>';

        foreach ($projects as $val) {
            // Initialize
            $selected = '';
            
            if ($projectId == $val->id) {
                $selected = 'selected';
            }

            $html .= '<option value="'.$val->id.'" '.$selected.'>'.$val->title.'</option>';
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $html
        ]);
    }

    public function assignedTo(\App\Task $task)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $task->users
        ]);
    }
}
