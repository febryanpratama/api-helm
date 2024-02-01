<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportTaskRequest extends FormRequest
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
            'task_id'       => 'required',
            'report_file'   => 'required'
        ];
    }

    public function messages()
    {
        return [
            'task_id.required'      => 'Task ID dibutuhkan',
            'report_file.required'  => 'Report File dibutuhkan'
        ];
    }
}
