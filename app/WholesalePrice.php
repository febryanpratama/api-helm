<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WholesalePrice extends Model
{
    protected $table    = 'wholesale_price';
    protected $guarded  = [];

    // Relation
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
