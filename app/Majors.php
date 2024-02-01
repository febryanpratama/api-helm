<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Majors extends Model
{
    protected $table        = 'majors';
    protected $guarded      = [];
    public $timestamps      = false;
    protected $primaryKey   = 'ID';

    public function subject()
    {
        return $this->belongsToMany(Subject::class, 'majors_subjects', 'major_id')->orderBy('ID', 'DESC');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_majors', 'major_id');
    }

    public function checkUserHasMajors()
    {
        return $this->belongsToMany(User::class, 'user_majors', 'major_id', 'user_id')->where('users.id', auth()->user()->id);
    }

    public function task()
    {
        return $this->hasMany(Task::class, 'major_id');
    }
}
