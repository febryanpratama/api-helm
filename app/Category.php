<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table    = 'category';
    protected $guarded  = [];
    protected $appends  = ['is_popular'];

    public function categoryInputs()
    {
        return $this->hasMany(CategoryDetailInput::class, 'category_id');
    }

    public function getIsPopularAttribute()
    {
        $popular = LandingCategoryPopular::where('category_id', $this->id)->first();

        if ($popular) {
            return '1';
        }

        return '0';
    }
}
