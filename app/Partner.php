<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $table    = 'partner';
    protected $guarded  = [];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
