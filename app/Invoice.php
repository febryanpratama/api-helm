<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table    = 'invoice';
    protected $guarded  = [];

    // Relations
    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }

    public function invoiceAddress()
    {
        return $this->hasOne(InvoiceAddress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i:s', strtotime($value));
    }

    public function invoiceTerminSchedule()
    {
        // Initialize
        return $this->hasOne(InvoiceTerminSchedule::class);
    }
}
