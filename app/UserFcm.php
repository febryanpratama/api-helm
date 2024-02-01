<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFcm extends Model
{
    protected $table    = 'user_fcm_token';
    protected $guarded  = [];
}
