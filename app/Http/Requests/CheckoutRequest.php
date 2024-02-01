<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bank'          => 'required',
            'payment_type'  => 'required'
        ];
    }

    public function messages()
    {
        return [
            'bank.required'         => 'Bank dibutuhkan.',
            'payment_type.required' => 'Tipe Pembayaran dibutuhkan.'
        ];
    }
}
