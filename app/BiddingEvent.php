<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BiddingEvent extends Model
{
    protected $guarded = [];
    protected $with = ['termin'];

    protected $casts = ['event_json' => 'array'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function termin()
    {
        return $this->hasOne(BiddingEventTermin::class, 'bidding_event_id');
    }
}
