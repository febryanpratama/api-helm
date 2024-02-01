<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Checkout extends Model
{
    use Notifiable;
    
    protected $table    = 'course_transaction';
    protected $guarded  = [];

    // Relation
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function checkoutDetail()
    {
        return $this->hasMany(CheckoutDetail::class, 'course_transaction_id');
    }
}
