<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingArticlePopular extends Model
{
    protected $table = 'landing_article_popular';
    protected $guarded = [];
    protected $with = ['article'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
