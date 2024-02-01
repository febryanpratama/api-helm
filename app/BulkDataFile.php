<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BulkDataFile extends Model
{
    protected $table = 'bulk_data_file';
    protected $guarded = [];
    protected $appends = ['status', 'total_error', 'type_desc'];

    public function bulkErrors()
    {
        return $this->hasMany(BulkDataError::class, 'bulk_data_file_id');
    }

    public function getStatusAttribute()
    {
        if ($this->is_done == 1) {
            return 'Selesai';
        } else {
            return 'Pending/Diproses';
        }
    }

    public function getTotalErrorAttribute()
    {
        $bulk_error = BulkDataError::where('bulk_data_file_id', $this->id)->count();

        return $bulk_error;
    }

    public function getTypeDescAttribute()
    {
        if ($this->type == 1) {
            return 'Import Bulk Product';
        }

        if ($this->type == 2) {
            return 'Import Bulk Transaction';
        }

        if ($this->type == 3) {
            return 'Import Bulk User Internal';
        }

        if ($this->type == 4) {
            return 'Import Bulk Distributor Suplier';
        }

        return '-';
    }
}
