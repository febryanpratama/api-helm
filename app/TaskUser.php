<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    protected $table    = 'tasks_users';
    protected $guarded  = [];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}
