<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckInMeet extends Model
{
    protected $table    = 'check_in_meet';
    protected $guarded  = [];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
