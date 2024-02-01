<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportsContent extends Model
{
    protected $table    = 'reports_content';
    protected $guarded  = [];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
