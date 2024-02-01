<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
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
            'balance'               => 'required',
            'account_number'        => 'required',
            'account_holder_name'   => 'required',
            'bank_name'             => 'required'
        ];
    }

    public function messages()
    {
        return [
            'balance.required'              => 'Balance dibutuhkan.',
            'account_number.required'       => 'Nomor Akun dibutuhkan.',
            'account_holder_name.required'  => 'Nama Akun dibutuhkan.',
            'bank_name.required'            => 'Bank/Wallet dibutuhkan.'
        ];
    }
}
