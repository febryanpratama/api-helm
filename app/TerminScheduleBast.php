<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TerminScheduleBast extends Model
{
    protected $table    = 'termin_schedule_bast';
    protected $guarded  = [];

    // Relations
    public function mediaTerminScheduleBast()
    {
        return $this->hasMany(MediaTerminScheduleBast::class);
    }

    public function courseTerminSchedule()
    {
        return $this->belongsTo(CourseTerminSchedule::class);
    }
}
