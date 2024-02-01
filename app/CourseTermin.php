<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseTermin extends Model
{
    protected $table    = 'course_termin';
    protected $guarded  = [];

    protected $casts = ['value' => 'array', 'completion_percentage' => 'array', 'completion_percentage_detail' => 'array', 'termin_duedate_number' => 'array', 'termin_duedate_name' => 'array'];

    protected function castAttribute($key, $value)
    {
        if ($this->getCastType($key) == 'array' && is_null($value)) {
            return [];
        }

        return parent::castAttribute($key, $value);
    }
}
