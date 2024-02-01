<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpRequest extends FormRequest
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
            'payment_type'  => 'required',
            'bank'          => 'required',
            'nominal'       => 'required'
        ];
    }

    public function messages()
    {
        return [
            'payment_type.required' => 'Tipe Pembayaran harus diisi.',
            'bank.required'         => 'Bank harus diisi.',
            'nominal.required'      => 'Nominal harus diisi.'
        ];
    }
}
