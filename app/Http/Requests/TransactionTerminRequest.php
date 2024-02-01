<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionTerminRequest extends FormRequest
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
            'course_termin_schedule_id' => 'required',
            'payment_type'              => 'required',
            'bank'                      => 'required'
        ];
    }

    public function messages()
    {
        return [
            'course_termin_schedule_id.required'    => 'Course Termin Schedule Id dibutuhkan',
            'payment_type.required'                 => 'Tipe Pembayaran dibutuhkan',
            'bank.required'                         => 'Bank dibutuhkan',
        ];
    }
}
