<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMajors extends Model
{
    protected $table   = 'user_majors';
    protected $guarded = [];
    public $timestamps = false;
}
