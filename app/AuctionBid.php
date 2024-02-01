<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuctionBid extends Model
{
    protected $guarded = [];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
