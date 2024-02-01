<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table    = 'portfolio';
    protected $guarded  = [];

    // Relation
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
