<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryPurchases extends Model
{
    protected $table    = 'inventory_purchases';
    protected $guarded  = [];

    // Relations
    public function inventoryPurchasesDetails()
    {
        return $this->hasMany(InventoryPurchasesDetails::class, 'inventory_purchases_id');
    }
}
