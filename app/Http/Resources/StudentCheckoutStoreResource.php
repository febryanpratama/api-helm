<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentCheckoutStoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            'total_payment'             => $this->total_payment,
            'total_payment_original'    => $this->total_payment_original,
            'payment_type'              => $this->payment_type,
            'bank_name'                 => $this->bank_name,
            'no_rek'                    => $this->no_rek,
            'unique_code'               => $this->unique_code,
            'second_unique_code'        => $this->second_unique_code,
            'status_transaction'        => $this->status_transaction,
            'status_payment'            => $this->status_payment,
            'expired_transaction'       => $this->expired_transaction,
            'total_termin'              => $this->_totalTermin($this->checkoutDetail, $this->is_termin),
            'detail_transaction'        => $this->checkoutDetail,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at
        ];
    }

    private function _totalTermin($cd, $isTermin)
    {
        // Initialize
        $totalTermin = 0;

        foreach ($cd as $val) {
            if ($val->course->is_termin == 1 && $val->course->courseTermin) {
                // Initialize
                $finalTermin = finalTermin($val->course->id);

                foreach ($finalTermin as $index => $ft) {
                    if ($index <= 1) {
                        $totalTermin += $ft['value_num'];
                    } else {
                        break;
                    }
                }
            } else {
                if ($val->course->is_termin == 0) {
                    $totalTermin += 0;
                }
            }
        }

        return $totalTermin;
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan transaksi'
        ];
    }
}
