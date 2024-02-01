<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MeetingRoomRequest extends FormRequest
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
            'session_id' => 'required',
            'name'       => 'required',
            'is_online'  => 'required',
            'time'       => 'required'
        ];
    }

    public function messages()
    {
        return [
            'session_id.required' => 'Session ID dibutuhkan',
            'name.required'       => 'Nama dibutuhkan',
            'is_online.required'  => 'Is Online dibutuhkan',
            'time.required'       => 'Waktu/Tanggal dibutuhkan'
        ];
    }
}
