<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Course;
use App\LandingPromo;
use App\Rating;

class InstructorCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                            => $this->id,
            'name'                          => $this->name,
            'description'                   => $this->description,
            'thumbnail'                     => $this->thumbnail,
            'price'                         => $this->price,
            'price_num'                     => $this->price_num,
            'discount'                      => $this->discount,
            'weight'                        => $this->weight,
            'unit_id'                       => $this->unit_id,
            'unit_name'                     => ($this->unit_id != null) ? $this->unit->name : null,
            'commission'                    => $this->commission,
            'slug'                          => $this->slug,
            'is_publish'                    => ($this->is_publish) ? true : false,
            'is_admin_confirm'              => $this->is_admin_confirm,
            'is_private'                    => $this->is_private,
            'instructor'                    => $this->_manageInstructor($this),
            'is_sp'                         => $this->is_sp,
            'sp_file'                       => $this->sp_file,
            'course_package_category'       => courseCategory($this->course_package_category),
            'category_id'                   => ($this->courseCategory) ? $this->courseCategory->category_id : null,
            'is_immovable_object'           => $this->is_immovable_object,
            'back_payment_status'           => $this->back_payment_status,
            'end_time_min'                  => $this->end_time_min,
            'start_time_min'                => $this->start_time_min,
            'period_day'                    => $this->period_day,
            'termin_percentage'             => ($this->courseTermin) ? array_map('intval', $this->courseTermin->value) : null,
            'completion_percentage'         => ($this->courseTermin) ? array_map('intval', $this->courseTermin->completion_percentage) : null,
            'completion_percentage_detail'  => ($this->courseTermin) ? $this->courseTermin->completion_percentage_detail : null,
            'dp_duedate_number'             => ($this->courseTermin) ? (int)$this->courseTermin->dp_duedate_number : null,
            'dp_duedate_name'               => ($this->courseTermin) ? $this->courseTermin->dp_duedate_name : null,
            'termin_duedate_number'         => ($this->courseTermin) ? $this->courseTermin->termin_duedate_number : null,
            'termin_duedate_name'           => ($this->courseTermin) ? $this->courseTermin->termin_duedate_name : null,
            'is_percentage'                 => ($this->courseTermin) ? $this->courseTermin->is_percentage : null,
            'is_hidden'                     => ($this->courseTermin) ? $this->courseTermin->is_hidden : null,
            'custom_document_input'         => $this->_customDocumentInput($this),
            'thumbnail_path'                => json_decode($this->thumbnail_path, true),
            'promotion'                     => LandingPromo::where('product_id', $this->id)->where('end_period', '>=', date('Y-m-d H:i:s'))->where('start_period', '<=', date('Y-m-d H:i:s'))->get(),
            'create_at'                     => $this->created_at,
            'update_at'                     => $this->updated_at
        ];
    }

    private function _manageSessionData($course)
    {
        // Initialize
        $data = [];

        foreach ($course->majors as $val) {
            // Initialize
            $row['id']          = $val->ID;
            $row['name']        = $val->Name;
            $row['detail']      = $val->Details;
            $row['create_at']   = $val->AddedTime;
            $row['update_at']   = $val->EditedTime;
            $row['subjects']    = $this->_manageSubjectsData($val->subject);
            $row['task']        = $val->task;

            $data[] = $row;
        }

        return $data;
    }

    private function _manageSubjectsData($subjects)
    {
        // Initialize
        $data = [];

        foreach ($subjects as $val) {
            // Initialize
            $row['id']         =  $val->ID;
            $row['name']       =  $val->Name;
            $row['duration']   =  null;
            $row['path']       =  $val->Path;
            $row['type']       =  ($val->FileType == 1) ? 'document' : 'video';
            $row['create_at']   = $val->AddedTime;
            $row['update_at']   = $val->EditedTime;

            $data[] = $row;
        }

        return $data;
    }

    private function _manageInstructor($course)
    {
        // Initialize
        $row['id']          = $course->user->id;
        $row['name']        = $course->user->name;
        $row['phone']       = $course->user->phone;
        $row['email']       = $course->user->email;
        $row['avatar']      = $course->user->avatar;
        $row['thumbnail']   = $course->user->thumbnail;

        // Initialize
        $courses    = Course::where('user_id', $course->user->id)->pluck('id');
        $totalRate  = Rating::whereIn('course_id', $courses)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;

        $row['total_course']   = Course::where(['is_publish' => 1, 'user_id' => $course->user->id])->count();
        $row['total_rating']   = ($totalRate) ? $totalRate : 0;

        $data[] = $row;

        return $data;
    }

    private function _customDocumentInput($data)
    {
        // Initialize
        $customDocumentInput = [];

        if ($data->customDocumentInput) {
            foreach ($data->customDocumentInput as $key => $valCDI) {
                $cdi['course_id']   = $valCDI->course_id;    
                $cdi['value']       = json_decode($valCDI->value, true);
                $cdi['created_at']  = $valCDI->created_at;
                $cdi['updated_at']  = $valCDI->updated_at;

                $customDocumentInput[] = $cdi;
            }
        }

        return $customDocumentInput;
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus'
        ];
    }
}
