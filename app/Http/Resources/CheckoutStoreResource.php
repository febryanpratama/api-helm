<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutStoreResource extends JsonResource
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
            'id'                         => $this->id,
            'user_id'                    => $this->user_id,
            'total_payment'              => $this->total_payment,
            'total_payment_original'     => $this->total_payment_original,
            'total_payment_rupiah'       => rupiah($this->total_payment),
            'total_payment_with_balance' => $this->total_payment_with_balance,
            'total_shipping_cost'        => $this->total_shipping_cost,
            'total_shipping_cost_rupiah' => rupiah($this->total_shipping_cost),
            'transaction_fees'           => $this->transaction_fees,
            'payment_type'               => $this->payment_type,
            'bank_name'                  => $this->bank_name,
            'no_rek'                     => $this->no_rek,
            'unique_code'                => $this->unique_code,
            'second_unique_code'         => $this->second_unique_code,
            'status'                     => $this->status,
            'is_termin'                  => $this->is_termin,
            'total_payment_termin'       => $this->total_payment_termin,
            'expired_transaction'        => $this->expired_transaction,
            'buy_now'                    => $this->buy_now,
            'status_tf_moota'            => $this->status_tf_moota,
            'date_tf_moota'              => $this->date_tf_moota,
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan transaksi.'
        ];
    }
}
