<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskReply extends Model
{
    protected $guarded = [];
    protected $table = 'task_reply';
    protected $with = ['user', 'task', 'taskReplyAttachments'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function taskReplyAttachments()
    {
        return $this->hasOne(TaskReplyAttachment::class, 'task_reply_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

}
