<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $table = "stories";
    protected $primaryKey = "ID";
    public $timestamps = false;
    protected $guarded = [];

    protected $append = ['hastag', 'caption', 'is_popular'];

    public function storyMedias()
    {
        return $this->hasMany(StoryMedia::class, 'IDStorie');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'IDUser');
    }

    public function getIsPopularAttribute()
    {
        $popular = LandingStoryPopular::where('story_id', $this->ID)->first();

        if ($popular) {
            return '1';
        }

        return '0';
    }

    public function popular()
    {
        return $this->hasOne(LandingStoryPopular::class, 'story_id');
    }

    public function getHastagAttribute()
    {
        $getHastag = explode('#', $this->Stories);
        $caption = $getHastag[0];

        if (count($getHastag) > 1) {
            $getAllHastag = null;
            for ($i=1; $i < count($getHastag); $i++) { 
                $set = count($getHastag) - $i;
                if ($set == 1) {
                    $koma = '';
                } else {
                    $koma = ',';
                }
                $getAllHastag .= isset($getHastag[$i]) ? '#' . $getHastag[$i] . $koma: null;
            }
        } else {
            $getAllHastag = null;
        }

        $h = null;

        if ($getAllHastag) {
            $h = explode(',', $getAllHastag);
        }
        $hastag = $h;

        return $hastag;
    }

    public function getCaptionAttribute()
    {
        $getHastag = explode('#', $this->Stories);
        $caption = $getHastag[0];

        if (count($getHastag) > 1) {
            $getAllHastag = null;
            for ($i=1; $i < count($getHastag); $i++) { 
                $set = count($getHastag) - $i;
                if ($set == 1) {
                    $koma = '';
                } else {
                    $koma = ',';
                }
                $getAllHastag .= isset($getHastag[$i]) ? '#' . $getHastag[$i] . $koma: null;
            }
        } else {
            $getAllHastag = null;
        }

        $h = null;

        if ($getAllHastag) {
            $h = explode(',', $getAllHastag);
        }
        $hastag = $h;

        return $caption;
    }
}
