<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    //
    protected $table = 'navigations';

    protected $guarded = ['id'];

    // public function content(){
    //     return $this->hasOne(Content::class, 'navigation_id', 'id');
    // }

    public function video(){
        return $this->hasMany(NavigationVideo::class, 'detail_navigation_id', 'id');
    }

    public function image(){
        return $this->hasMany(NavigationVideo::class, 'detail_navigation_id', 'id');
    }

    public function konten(){
        return $this->hasMany(NavigationContent::class, 'detail_navigation_id', 'id');
    }
}
