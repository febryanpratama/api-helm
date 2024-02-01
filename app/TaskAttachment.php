<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $guarded = [];
    protected $table = 'task_attachments';

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function taskmentorassessment()
    {
        return $this->hasOne(TaskMentorAssessment::class);
    }
}
