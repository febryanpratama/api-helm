<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MajorsSubject extends Model
{
    protected $table   = 'majors_subjects';
    protected $guarded = [];
    public $timestamps = false;

    // Relation
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function majors()
    {
        return $this->belongsTo(Majors::class, 'major_id');
    }
}
