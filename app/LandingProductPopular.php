<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingProductPopular extends Model
{
    protected $table = 'landing_product_popular';
    protected $guarded = [];

    protected $with = ['product'];

    public function product()
    {
        return $this->belongsTo(Course::class, 'product_id');
    }
}
