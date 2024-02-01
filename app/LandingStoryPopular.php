<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingStoryPopular extends Model
{
    protected $table = 'landing_stories_popular';
    protected $guarded = [];

    // protected $with = ['story'];

    public function story()
    {
        return $this->belongsTo(Story::class, 'story_id');
    }
}
