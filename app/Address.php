<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table    = 'address';
    protected $guarded  = [];

    // Relation
    public function masterLocation()
    {
        return $this->belongsTo(MasterLocation::class, 'district_id');
    }
}
