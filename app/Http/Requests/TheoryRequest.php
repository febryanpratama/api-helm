<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TheoryRequest extends FormRequest
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
            'session_id'    => 'required',
            'name'          => 'required',
            'upload_file'   => 'required'
        ];
    }

    public function messages()
    {
        return [
            'session_id.required'   => 'Session Id dibutuhkan',
            'name.required'         => 'Nama dibutuhkan',
            'upload_file.required'  => 'File dibutuhkan'
        ];
    }
}
