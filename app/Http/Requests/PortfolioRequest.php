<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioRequest extends FormRequest
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
            'portfolio_photo'       => 'required',
            'portfolio_description' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'portfolio_photo.required'          => 'Foto Portofolio dibutuhkan',
            'portfolio_description.required'    => 'Deskripsi Portofolio dibutuhkan'
        ];
    }
}
