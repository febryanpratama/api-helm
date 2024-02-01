<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table    = 'course_rating';
    protected $guarded  = [];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
