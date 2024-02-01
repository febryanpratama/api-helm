<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BiddingEventTermin extends Model
{
    protected $table = 'termin_bidding_events';
    protected $guarded = [];
    protected $casts = ['value' => 'array', 'completion_percentage' => 'array', 'completion_percentage_detail' => 'array'];

    public function bid()
    {
        return $this->belongsTo(BiddingEvent::class, 'bidding_event_id');
    }
}
