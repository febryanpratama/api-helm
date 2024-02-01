<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamPhoto extends Model
{
    protected $table    = 'team_photo';
    protected $guarded  = [];

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
