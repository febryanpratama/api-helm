<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionDetails extends Model
{
    protected $table    = 'transaction_details';
    protected $guarded  = [];
    protected $casts    = [
                            'category_detail_inputs' => 'array',
                        ];

    // Relations
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function transactionDetailsCustomDocumentInput()
    {
        return $this->hasMany(TransactionDetailsCustomDocumentInput::class, 'transaction_details_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function terminSchedules()
    {
        return $this->hasMany(CourseTerminSchedule::class, 'course_transaction_detail_id');
    }
}
