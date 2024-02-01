<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfflineTransactionRequest extends FormRequest
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
            'status_payment' => 'required',
            'total_pay'      => 'required|integer'
        ];
    }

    public function messages()
    {
        return [
            'status_payment.required' => 'Status Payment dibutuhkan',
            'total_pay.required'      => 'Total Pay dibutuhkan',
            'total_pay.integer'       => 'Total Pay harus berupa angka',
            // 'change.required'      => 'Change dibutuhkan'
        ];
    }
}
