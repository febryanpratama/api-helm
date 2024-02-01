<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BiddingProject extends Model
{
    protected $table = 'bidding_project';
    protected $guarded = [];
    protected $with = ['termin'];

    protected $casts = ['project_json' => 'array'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function termin()
    {
        return $this->hasOne(BiddingProjectTermin::class, 'bidding_project_id');
    }
}
