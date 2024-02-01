<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetingRoom extends Model
{
    protected $table    = 'meet';
    protected $guarded  = [];

    // Relations
    public function majors($value='')
    {
        return $this->belongsTo(Majors::class, 'session_id', 'ID');
    }
}
