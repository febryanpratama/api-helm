<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table   = 'articles';
    protected $guarded = [];

    public function comments()
    {
        return $this->hasMany(ArticleComment::class, 'article_id');
    }
}
