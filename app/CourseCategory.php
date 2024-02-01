<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseCategory extends Model
{
    protected $table    = 'course_category';
    protected $guarded  = [];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
