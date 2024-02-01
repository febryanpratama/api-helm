<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Task;

class TodoActivitiesController extends Controller
{
    public function store()
    {
        \App\TodoActivity::create([
            'user_id' => auth()->user()->id,
            'todo_id' => request('todoId'),
            'status' => 'todo'
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Status Sub Task berhasil diperbaharui'
        ]);
    }

    public function isDoing(\App\Todo $todo)
    {
        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'doing',
                'is_doing_time' => $get_activities->is_doing_time ? $get_activities->is_doing_time . '|' . time() : time(), 
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi doing dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi doing dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi doing dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        } else {
            // Insert To Do
            \App\TodoActivity::create([
                'user_id'       => auth()->user()->id,
                'todo_id'       => $todo->id,
                'status'        => 'doing',
                'is_doing_time' => time()
            ]);

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isTrouble(\App\Todo $todo)
    {
        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'trouble',
                'is_trouble_time' => $get_activities->is_trouble_time ? $get_activities->is_trouble_time . '|' . time() : time(),
                'reason_trouble' => $get_activities->reason_trouble ? $get_activities->reason_trouble . '|' . request()->reason_trouble : request()->reason_trouble,
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi trouble dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi trouble dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi trouble dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isHold(\App\Todo $todo)
    {
        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'hold',
                'is_hold_time' => $get_activities->is_hold_time ? $get_activities->is_hold_time . '|' . time() : time(),
                'reason_hold' => $get_activities->reason_hold ? $get_activities->reason_hold . '|' . request()->reason_hold : request()->reason_hold,
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi hold dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi hold dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi hold dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isCancel(\App\Todo $todo)
    {
        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'cancel',
                'is_cancel_time' => $get_activities->is_cancel_time ? $get_activities->is_cancel_time . '|' . time() : time(),
                'reason_cancel' => $get_activities->reason_cancel ? $get_activities->reason_cancel . '|' . request()->reason_cancel : request()->reason_cancel,
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi cancel dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi cancel dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi cancel dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isDone(\App\Todo $todo)
    {
        // Update is_done to y
        $todo->update(['is_done' => 'y']);

        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'done',
                'is_done_time' => $get_activities->is_done_time ? $get_activities->is_done_time . '|' . time() : time(),
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi done dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi done dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi done dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function isRevisi(\App\Todo $todo)
    {
        // $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->where('user_id', auth()->user()->id)->first();
        $get_activities = \App\TodoActivity::where('todo_id', $todo->id)->first();

        if ($get_activities) {
            $get_activities->update([
                'user_id' => auth()->user()->id,
                'status' => 'revisi',
                'is_revisi_time' => $get_activities->is_revisi_time ? $get_activities->is_revisi_time . '|' . time() : time(),
                'reason_revisi' => $get_activities->reason_revisi ? $get_activities->reason_revisi . '|' . request()->reason_revisi : request()->reason_revisi,
            ]);

            // send chat notif
            if (auth()->user()->id == $todo->task->assignedBy->id) {
                $message = 'Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi revisi dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            } else {
                $message = auth()->user()->name . ' merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi revisi dari tugas ' . strtoupper($todo->task->name);
                $receiver = $todo->task->assignedBy->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));

                $message =' Merubah sub tugas ' . strtoupper($todo->todo) . ' menjadi revisi dari tugas ' . strtoupper($todo->task->name);
                $receiver = auth()->user()->id;
                $chat = app('App\Http\Controllers\ChatsController')->chatNotif($message, $receiver, auth()->user(), route('task.index', $todo->task->id));
            }

             // Pusher Notification
            event(new \App\Events\ChatNotif($chat['participants']));
            // Pusher Notification
            event(new \App\Events\RealTimeNotif($chat['message']));

            // Check Ajax Request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Status Sub Task berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'change activity success'
            ];
    
            return redirect()->back()->with($notif);
        }

        // Check Ajax Request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Status Sub Task Gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'change activity fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function listActivity(Task $task)
    {
        // Initialize
        $todos      = \App\Todo::where('task_id', $task->id)->pluck('id');
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
}
