<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table    = 'transaction';
    protected $guarded  = [];

    // Relation
    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetails::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'store_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function fleetPosition()
    {
        return $this->hasMany(FleetTrackingLog::class, 'transaction_id')->orderBy('created_at', 'desc');
    }

    public function complain()
    {
        return $this->hasOne(TransactionComplain::class, 'transaction_id');
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }
}
