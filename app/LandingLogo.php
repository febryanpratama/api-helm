<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingLogo extends Model
{
    protected $table = 'landing_logo';
    protected $guarded = [];

    public function template()
    {
        return $this->belongsTo(LandingTemplate::class, 'template_id');
    }
}
