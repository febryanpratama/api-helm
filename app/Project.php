<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = [];
    protected $casts = ['question_category' => 'array'];

    public function bids()
    {
        return $this->hasMany(BiddingProject::class, 'project_id');
    }

    public function mediaProjects()
    {
        return $this->hasMany(MediaProjects::class);
    }
}
