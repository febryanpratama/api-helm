<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Course;

class InstructorCourseTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    // protected $defaultIncludes = [
    //     //
    // ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    // protected $availableIncludes = [
    //     //
    // ];
    
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
            'update_at'         => $course->updated_at
        ];
    }
}
