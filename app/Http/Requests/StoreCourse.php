<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourse extends FormRequest
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
            'name'          => 'required|max:191',
            'description'   => 'required',
            // 'thumbnail'     => 'required',
            'price'         => 'required',
            // 'stock'         => 'required',
            'unit_id'       => 'required',
            'category_id'   => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required'        => 'Nama dibutuhkan.',
            'name.max'             => 'Panjang Maksimal Karakter Nama, 191 Karakter.',
            'description.required' => 'Deskripsi dibutuhkan.',
            // 'thumbnail.required'   => 'Thumbnail dibutuhkan.',
            'price.required'       => 'Harga dibutuhkan.',
            // 'stock.required'       => 'Stok dibutuhkan.',
            'unit_id.required'     => 'Satuan dibutuhkan.',
            'category_id.required' => 'Kategori dibutuhkan.'
        ];
    }
}
