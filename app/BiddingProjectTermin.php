<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BiddingProjectTermin extends Model
{
    protected $table = 'termin_bidding_project';
    protected $guarded = [];
    protected $casts = ['value' => 'array', 'completion_percentage' => 'array', 'completion_percentage_detail' => 'array'];

    public function bid()
    {
        return $this->belongsTo(BiddingProject::class, 'bidding_project_id');
    }
}
