<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortofolioMain extends Model
{
    protected $table    = 'portofolio_main';
    protected $guarded  = [];

    public function portofolio()
    {
        return $this->hasMany(Portfolio::class, 'portofolio_main_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
