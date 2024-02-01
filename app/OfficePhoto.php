<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfficePhoto extends Model
{
    protected $guarded  = [];

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
