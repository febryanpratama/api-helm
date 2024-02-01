<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingCategoryPopular extends Model
{
    protected $table = 'landing_category_popular';
    protected $guarded = [];
    protected $with = ['category'];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
