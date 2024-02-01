<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Competence extends Model
{
    protected $table    = 'competence';
    protected $guarded  = [];

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
