<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CourseTerminSchedule extends Model
{
    protected $table    = 'course_termin_schedule';
    protected $guarded  = [];

    // Relation
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->hasOne(CourseTransactionTerminPayment::class);
    }

    public function transactionDetails()
    {
        return $this->belongsTo(TransactionDetails::class, 'course_transaction_detail_id');
    }

    public function getDueDateAttribute($value)
    {
        return date('d-m-Y', strtotime($value));
    }

    public function invoiceTerminSchedule()
    {
        return $this->hasOne(InvoiceTerminSchedule::class, 'termin_schedule_id');
    }

    public function checkTotalValue($transaction_detail_id = null)
    {
        if ($transaction_detail_id) {
            $data = $this->where('course_transaction_detail_id', $transaction_detail_id)->sum('value');
            return $data;
        }

        return 0;
    }
}
