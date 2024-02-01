<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    protected $table    = 'fleet';
    protected $guarded  = [];

    // Relations
    public function masterLocation()
    {
        return $this->belongsTo(MasterLocation::class);
    }
}
