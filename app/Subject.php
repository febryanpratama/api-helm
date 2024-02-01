<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table        = 'subjects';
    protected $guarded      = [];
    public $timestamps      = false;
    protected $primaryKey   = 'ID';

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'projects_subjects', 'subject_id', 'project_id');
    }

    public function majorsSubject()
    {
        return $this->belongsTo(MajorsSubject::class, 'ID');
    }

    public function majorsSubjectv2()
    {
        return $this->hasOne(MajorsSubject::class, 'subject_id');
    }
}
