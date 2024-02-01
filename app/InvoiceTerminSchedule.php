<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceTerminSchedule extends Model
{
    protected $table    = 'invoice_termin_schedule';
    protected $guarded  = [];

    // Relations
    public function terminSchedule($value='')
    {
        return $this->belongsTo(CourseTerminSchedule::class);
    }

    public function invoice($value='')
    {
        return $this->belongsTo(Invoice::class);
    }
}
