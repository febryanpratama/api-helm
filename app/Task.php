<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $guarded = [];
    // protected $with = ['assignedBy', 'users', 'project', 'reportAttachments', 'taskAttachment'];
    // protected $appends = ['todo_done', 'todo_on_going', 'progress_percentage', 'end_date_format'];


    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies($search = null)
    {
        $result = $this->hasMany(TaskReply::class, 'task_id');
        if ($search) {
            $result = $this->hasMany(TaskReply::class, 'task_id')->where('reply', 'like', '%'.$search.'%');
        }
        return $result;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tasks_users'); // assuming user_id and task_id as fk
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    public function taskAttachment()
    {
        return $this->hasOne(TaskAttachment::class, 'task_id')->where('is_report', 'n');
    }

    public function reportAttachments()
    {
        return $this->hasMany(TaskAttachment::class, 'task_id')->where('is_report', 'y');
    }

    public function majors()
    {
        return $this->belongsTo(Majors::class, 'major_id');
    }
}
