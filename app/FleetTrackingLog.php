<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FleetTrackingLog extends Model
{
    protected $guarded  = [];

    public function getPhotoAttribute($value)
    {
        return env('SITE_URL') . '/storage/' . $value;
    }
}
