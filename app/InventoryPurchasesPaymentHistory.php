<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchasesPaymentHistory extends Model
{
    protected $table    = 'inventory_purchases_payment_history';
    protected $guarded  = [];

    // Relations
    public function inventoryPurchases()
    {
        return $this->belongsTo(InventoryPurchases::class);
    }
}
