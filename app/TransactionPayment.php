<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    protected $table = "transactionpayments";
	protected $primaryKey = "ID";
	public $timestamps = false;
    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'IDTransaction');
    }
}
