<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $guarded = [];
    protected $with = ['product'];

    public function product()
    {
        return $this->belongsTo(Course::class, 'product_id');
    }
}
