<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoryMedia extends Model
{
    protected $table = "storiesmedia";
    protected $primaryKey = "ID";
    public $timestamps = false;
    protected $guarded = [];
}
