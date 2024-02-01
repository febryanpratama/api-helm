<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingPromo extends Model
{
    protected $table = 'landing_promo';
    protected $guarded = [];
    protected $appends = ['type_desc'];

    public function getTypeDescAttribute()
    {
        if ($this->type == 1) {
            return 'Discount';
        }

        if ($this->type == 2) {
            return 'Coupon/Voucher';
        }

        if ($this->type == 3) {
            return 'Flash sale';
        }

        if ($this->type == 4) {
            return 'Free Gift';
        }

        if ($this->type == 5) {
            return 'Cashback';
        }

        if ($this->type == 6) {
            return 'Challenge';
        }

        if ($this->type == 7) {
            return 'Loyalty';
        }

        if ($this->type == 8) {
            return 'Referral';
        }

        if ($this->type == 9) {
            return 'Donation';
        }
    }
}
