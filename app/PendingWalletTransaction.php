<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PendingWalletTransaction extends Model
{
    protected $table   = 'pending_wallet_transaction';
    protected $guarded = [];
}
