<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscussComment extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(DiscussComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(DiscussComment::class, 'parent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function discuss()
    {
        return $this->belongsTo(Discuss::class, 'article_id');
    }
}
