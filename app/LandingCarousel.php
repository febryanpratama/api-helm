<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingCarousel extends Model
{
    protected $table = 'landing_carousel';
    protected $guarded = [];
    protected $appends = ['type_description'];

    public function getTypeDescriptionAttribute()
    {
        if ($this->type == 1) {
            return 'Product';
        } else {
            return 'Event';
        }
    }
}
