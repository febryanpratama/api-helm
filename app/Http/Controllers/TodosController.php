<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use App\TodoReply;
use App\Task;

class TodosController extends Controller
{
    public function detailTask(\App\Task $task)
    {
        // Initialize
        $todo                   = \App\Todo::where('task_id', $task->id)->orderBy('id', 'asc')->get();
        $category_knowledge     = \App\Knowledge::select('category')->groupBy('category')->get();
 
        return view('todos.index', compact('task', 'todo', 'category_knowledge'));
    }

    public function store(\App\Task $task)
    {
        $todo = \App\Todo::updateOrCreate(
            [
                'id' => request()->id
            ], [
            'task_id' => $task->id,
            'todo' => request()->todo,
            'assigned_to' => request()->assigned_to,
        ]);

        if ($todo) {
            if (auth()->user()->id != request()->assigned_to) {
                $user = \App\User::find(request()->assigned_to);
                $user->notify(new \App\Notifications\Todo($user->id, auth()->user(), $todo));

                // OneSignal::sendNotificationUsingTags(
                //     "ada to do baru nih $todo->todo",
                //     array(
                //         ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $user->id],
                //     ),
                //     $url = route('todo.detail', $todo->id),
                //     $data = null,
                //     $buttons = null,
                //     $schedule = null
                // );
            }

            // send chat notif
            if (auth()->user()->id == $task->assignedBy->id) {
                $message = 'Menambahkan sub tugas ' . strtoupper($todo->todo) . ' dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' menambahkan sub tugas ' . strtoupper($todo->todo) . ' dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Menambahkan sub tugas ' . strtoupper($todo->todo) . ' dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

            // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            event(new \App\Events\RealTimeNotif($chat['message']));
            
            if (!request()->id) {
                \App\TodoActivity::create([
                    'user_id' => request()->assigned_to,
                    'todo_id' => $todo->id,
                    'status' => 'todo',
                ]);
            }

            if (request()->file('upload_file')) {
                $file = request()->file('upload_file');
                $md5_name = uniqid().md5_file($file->getRealPath());
                $ext = $file->getClientOriginalExtension();
    
                if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                    $destination_path = public_path('storage/uploads/img/todo/');
                    $file->move($destination_path,"$md5_name.$ext");
    
                    $attachment = \App\TodoAttachment::create([
                        'todo_id' => $todo->id,
                        'type' => 'image',
                        'path' => env('SITE_URL') . '/storage/' . "uploads/img/todo/$md5_name.$ext",
                        'is_report' => 'n',
                    ]);
    
                } else if ($ext == 'mp4' || $ext == 'avi') {
                    $destination_path = public_path('storage/uploads/video/todo/');
                    $file->move($destination_path,"$md5_name.$ext");
    
                    $attachment = \App\TodoAttachment::create([
                        'todo_id' => $todo->id,
                        'type' => 'video',
                        'path' => env('SITE_URL') . '/storage/' . "uploads/video/todo/$md5_name.$ext",
                        'is_report' => 'n',
                    ]);
                } else if ($ext == 'pdf') {
                    $destination_path = public_path('storage/uploads/file/todo/');
                    $file->move($destination_path,"$md5_name.$ext");
    
                    $attachment = \App\TodoAttachment::create([
                        'todo_id' => $todo->id,
                        'type' => 'file',
                        'path' => env('SITE_URL') . '/storage/' . "uploads/file/todo/$md5_name.$ext",
                        'is_report' => 'n',
                    ]);
                }
            }

            // Check ajax request
            if(request()->ajax()){
                // Check Create or Note
                if ($todo->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Sub Tugas berhasil disimpan'
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Sub Tugas berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'To Do created successfully'
            ];
            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Note
            if ($todo->wasRecentlyCreated) {
                return response()->json([
                    'status'    => true,
                    'message'   => 'Sub Tugas gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Sub Tugas gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'To Do created fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function detail(\App\Todo $todo)
    {
        $reply = \App\TodoReply::where('todo_id', $todo->id)->orderBy('id', 'asc')->get();

        // Check ajax request
        if(request()->ajax()){
            // Initialize
            $data = [];
            $data['id']             = $todo->id;
            $data['task_id']        = $todo->task_id;
            $data['assigned_to']    = $todo->assignedTo->name;
            $data['assigned_to_id'] = $todo->assigned_to;
            $data['todo']           = $todo->todo;
            $data['is_done']        = $todo->is_done;
            $data['created_at']     = $todo->created_at->format('d M y H:i');
            $data['updated_at']     = $todo->updated_at->format('d M y H:i');

            // Validate
            if ($todo->todoAttachment) {
                $data['attachment']  = $todo->todoAttachment->path;
            } else {
                $data['attachment']  = '-';
            }

            // Activity
            if ($todo->activity) {
                $data['work_on'] = '1';
            } else {
                $data['work_on'] = '0';
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Data tersedia',
                'data'      => $data
            ]);

            die;
        }
        
        return view('todos.detail', compact('todo', 'reply'));
    }

    public function reply(\App\Todo $todo)
    {
        $todo_reply = \App\TodoReply::create([
            'todo_id' => $todo->id,
            'user_id' => auth()->user()->id,
            'reply' => request()->reply,
        ]);

        if (request()->file('reply_upload')) {
            $file = request()->file('reply_upload');
            $md5_name = uniqid().md5_file($file->getRealPath());
            $ext = $file->getClientOriginalExtension();
            // dd($ext);

            if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                $destination_path = public_path('storage/uploads/img/todoreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TodoReplyAttachment::create([
                    'todo_reply_id' => $todo_reply->id,
                    'type' => 'image',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/img/todoreply/$md5_name.$ext",
                ]);

            } else if ($ext == 'mp4' || $ext == 'avi') {
                $destination_path = public_path('storage/uploads/video/todoreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TodoReplyAttachment::create([
                    'todo_reply_id' => $todo_reply->id,
                    'type' => 'video',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/video/todoreply/$md5_name.$ext",
                ]);
            } else {
                $destination_path = public_path('storage/uploads/file/todoreply/');
                $file->move($destination_path,"$md5_name.$ext");

                $attachment = \App\TodoReplyAttachment::create([
                    'todo_reply_id' => $todo_reply->id,
                    'type' => 'file',
                    'path' => env('SITE_URL') . '/storage/' . "uploads/file/todoreply/$md5_name.$ext",
                ]);
            }
        }

        if ($todo_reply) {
            $get_user = $todo->task->users()->get();

            if (count($get_user) > 0) {
                foreach ($get_user as $value) {

                    if ($value->id != auth()->user()->id) {
                        $value->notify(new \App\Notifications\TodoDiscuss($value->id, auth()->user(), $todo));

                        OneSignal::sendNotificationUsingTags(
                            "ada diskusi baru nih di $todo->todo",
                            array(
                                ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $value->id],
                            ),
                            $url = route('todo.detail', $todo->id),
                            $data = null,
                            $buttons = null,
                            $schedule = null
                        );
                    }
                }
            }

            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Message berhasil ditambahkan'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Reply successfully'
            ];
           
            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Message gagal ditambahkan'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Reply fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isDone(\App\Todo $todo)
    {
        $todo->is_done = request()->is_done;

        if ($todo->save()) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Sub Tugas berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Done successfully'
            ];

            return redirect()->back()->with($notif);
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Done fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function delete(\App\Todo $todo)
    {
        if ($todo->delete()) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Sub Tugas berhasil dihapus'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Delete successfully'
            ];
            
            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Sub Tugas gagal dihapus'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Delete fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function showUsers(\App\Task $task) {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $task->users
        ]);
    }

    public function listToDo(Task $task)
    {
        // Initialize
        $data = [];

        foreach($task->todos as $val) {
            // Initialize
            $attachment = '';
            $row        = [];
            
            $row['id']          = $val->id;
            $row['task_id']     = $val->task_id;
            $row['assigned_to'] = $val->assignedTo->name;
            $row['replies']     = count($val->replies);
            $row['todo']        = $val->todo;
            $row['is_done']     = $val->is_done;
            $row['created_at']  = $val->created_at->format('d M y H:i');
            $row['updated_at']  = $val->updated_at;

            if ($val->todoAttachment) {
                $attachment = $val->todoAttachment;
            }
            
            $row['attachment'] = $attachment;

            // To Do Status
            if ($val->activity) {
                // Initialize
                $todoStatus         = $val->activity->status;
                $row['todo_status'] = ucfirst($todoStatus);

                // Check Badge Color
                if ($todoStatus == 'todo') {
                    $row['badge_color'] = '#BDC002';
                } elseif ($todoStatus == 'doing') {
                    $row['badge_color'] = '#5884C1';
                } elseif ($todoStatus == 'done') {
                    $row['badge_color'] = '#38c172';
                } elseif ($todoStatus == 'trouble') {
                    $row['badge_color'] = '#e3342f';
                } elseif ($todoStatus == 'hold') {
                    $row['badge_color'] = '#6cb2eb';
                } elseif ($todoStatus == 'revisi') {
                    $row['badge_color'] = '#FF9500';
                }elseif ($todoStatus == 'cancel') {
                    $row['badge_color'] = '#999999';
                } else {
                    $row['badge_color'] = '#BDC002';
                }

                 $row['work_on'] = '1';
            } else {
                $row['todo_status'] = 'To Do Baru';
                $row['badge_color'] = '#1AD5D2';
                $row['work_on']    = '0';
            }

            $row['work_date']           = ($val->activity && $val->activity->is_doing_time) ? date('d F Y', $val->activity->is_doing_time) : '-';
            $row['date_of_completion']  = ($val->activity && $val->activity->is_done_time) ? date('d F Y', $val->activity->is_done_time) : '-';

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function listDiscussion() {
        // Initialize
        $discussions = TodoReply::where('todo_id', request('todo_id'))->get();

        $data = [];
        foreach($discussions as $discussion) {
            $row                = [];
            $row['id']          = $discussion->id;
            $row['reply']       = $discussion->reply;
            $row['todo_id']     = $discussion->todo_id;
            $row['user_id']     = $discussion->user_id;
            $row['user_id']     = $discussion->user_id;
            $row['userName']    = $discussion->user->name;
            // $row['created_at']  = $discussion->created_at->diffForHumans().' ('. $discussion->created_at->format('d M y H:i') .')';
            $row['created_at']  = $discussion->created_at->diffForHumans();
            $row['updated_at']  = $discussion->updated_at;

            // Attachment
            if ($discussion->todoReplyAttachments) {
                $row['attachment'] = $discussion->todoReplyAttachments->path;
            } else {
                $row['attachment'] = '-';
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function searchToDo()
    {
        // To Do
        $todos = Todo::where('assigned_to', auth()->user()->id)->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $todos
        ]);
    }
}
