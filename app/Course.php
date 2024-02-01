<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table    = 'course';
    protected $guarded  = [];
    protected $appends  = ['is_popular'];

    // Relatioin
    public function majors()
    {
        return $this->hasMany(Majors::class, 'IDCourse');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function userCourse()
    // {
    //     return $this->hasMany(UserCourse::class);
    // }

    public function checkout()
    {
        return $this->hasMany(Checkout::class);
    }

    // public function getCountStudentsJoinAttribute()
    // {
    //     // Initialize
    //     $joined = UserCourse::where('course_id', $this->id)->count();

    //     return $joined;
    // }

    public function getTotalInvByCourseAttribute()
    {
        // Initialize
        $checkout = CheckoutDetail::where('course_id', $this->id)->pluck('course_transaction_id');
        $total    = Checkout::whereIn('id', $checkout)->sum('total_payment');

        return $total;
    }

    public function courseTermin()
    {
        return $this->hasOne(CourseTermin::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function courseCategory()
    {
        return $this->hasOne(CourseCategory::class);
    }

    public function getStartTimeMinAttribute($value)
    {
        if ($value) {
            return date('H:i', $value);
        }

        return $value;
    }

    public function getEndTimeMinAttribute($value)
    {
        if ($value) {
            return date('H:i', $value);
        }

        return $value;
    }

    public function customDocumentInput()
    {
        return $this->hasMany(CustomDocumentInput::class);
    }

    public function wholesalePrice()
    {
        return $this->hasMany(WholesalePrice::class);
    }

    public function getIsPopularAttribute()
    {
        $popular = LandingProductPopular::where('product_id', $this->id)->first();

        if ($popular) {
            return '1';
        }

        return '0';
    }

    public function popular()
    {
        return $this->hasOne(LandingProductPopular::class, 'product_id');
    }
}
