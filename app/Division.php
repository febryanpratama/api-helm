<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table        = 'division';
    protected $guarded      = [];
    public $timestamps      = false;
    protected $primaryKey   = 'ID';

    public function subject()
    {
        return $this->belongsToMany(Subject::class, 'divisions_subjects')->orderBy('ID', 'DESC');
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_division');
    }


    public function checkUserHasDivision()
    {
        return $this->belongsToMany(User::class, 'user_division')->where('users.id', auth()->user()->id);
    }
}
