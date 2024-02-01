<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $primaryKey = 'ID';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = ['JournalDocNo' => 'array', 'json_debit' => 'array', 'json_credit' => 'array'];
}
