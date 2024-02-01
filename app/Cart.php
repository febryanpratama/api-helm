<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table    = 'cart';
    protected $guarded  = [];

    // Relation
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
