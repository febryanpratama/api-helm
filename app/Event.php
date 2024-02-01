<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $guarded = [];
    protected $casts = ['question_category' => 'array'];

    public function bids()
    {
        return $this->hasMany(BiddingEvent::class, 'event_id');
    }

    public function mediaEvents()
    {
        return $this->hasMany(MediaEvents::class);
    }
}
