<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceAddress extends Model
{
    protected $table    = 'invoice_address';
    protected $guarded  = [];

    // Relations
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
