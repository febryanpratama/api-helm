<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetenceRequest extends FormRequest
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
            'name'          => 'required',
            'file'          => 'required',
            'description'   => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required'         => 'Nama dibutuhkan.',
            'file.required'         => 'File dibutuhkan.',
            'description.required'  => 'Deskripsi dibutuhkan.'
        ];
    }
}
