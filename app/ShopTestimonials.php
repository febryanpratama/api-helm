<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopTestimonials extends Model
{
    protected $table    = 'shop_testimonials';
    protected $guarded  = [];

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class, 'store_id');
    }
}
