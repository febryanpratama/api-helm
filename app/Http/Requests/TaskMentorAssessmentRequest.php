<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskMentorAssessmentRequest extends FormRequest
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
            'task_attachments_id'   => 'required',
            'score'                 => 'required',
            'response'              => 'required'
        ];
    }

    public function messages()
    {
        return [
            'task_attachments_id.required'  => 'Task Attachment ID dibutuhkan',
            'score.required'                => 'Score dibutuhkan',
            'response.required'             => 'Response dibutuhkan'
        ];
    }
}
