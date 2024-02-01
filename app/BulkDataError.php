<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BulkDataError extends Model
{
    protected $guarded = [];

    protected $casts = ['value_data' => 'array'];
    protected $appends = ['error_baris_ke'];

    public function getErrorBarisKeAttribute()
    {
        return $this->number_row + 1;
    }
}
