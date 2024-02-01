<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BeginBalance extends Model
{
    protected $table = 'beginbalances';
    protected $primaryKey = 'ID';
    protected $guarded = [];
    public $timestamps = false;

    public function account() {
        return $this->belongsTo(Account::class, 'IDAccount');
    }

}
