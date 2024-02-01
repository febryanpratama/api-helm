<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Course;

class InstructorCourseShowTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Course $course)
    {
        return [
            'id'                => $course->id,
            'name'              => $course->name,
            'description'       => $course->description,
            'thumbnail'         => $course->thumbnail,
            'periode_type'      => $course->periode_type,
            'periode'           => $course->periode,
            'course_type'       => $course->course_type,
            'price'             => $course->price,
            'price_num'         => $course->price_num,
            'commission'        => $course->commission,
            'slug'              => $course->slug,
            'is_publish'        => ($course->is_publish) ? true : false,
            'is_admin_confirm'  => $course->is_admin_confirm,
            'create_at'         => $course->created_at,
            'update_at'         => $course->updated_at,
            'session'           => $this->_manageSessionData($course)
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
}
