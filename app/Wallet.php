<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table    = 'wallet';
    protected $guarded  = [];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
