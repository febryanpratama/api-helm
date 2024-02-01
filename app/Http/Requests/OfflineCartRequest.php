<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfflineCartRequest extends FormRequest
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
            'course_id' => 'required',
            'qty'       => 'required'
        ];
    }

    public function messages()
    {
        return [
            'course_id.required' => 'Course ID dibutuhkan',
            'qty.required'       => 'QTY dibutuhkan'
        ];
    }
}
