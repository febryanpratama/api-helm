<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstructorCourseUpdateStatusResource extends JsonResource
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
            'periode_type'                  => $this->periode_type,
            'periode'                       => $this->periode,
            'course_type'                   => $this->course_type,
            'price'                         => $this->price,
            'price_num'                     => $this->price_num,
            'commission'                    => $this->commission,
            'slug'                          => $this->slug,
            'is_publish'                    => ($this->is_publish) ? true : false,
            'is_admin_confirm'              => $this->is_admin_confirm,
            'course_package_category'       => courseCategory($this->course_package_category),
            'course_package_category_id'    => $this->course_package_category,
            'min_user_joined'               => $this->min_user_joined,
            'max_user_joined'               => $this->max_user_joined,
            'commission_min'                => $this->commission_min_user_joined,
            'commission_max'                => $this->commission_max_user_joined,
            'create_at'                     => $this->created_at,
            'update_at'                     => $this->updated_at
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data paket kursus'
        ];
    }
}
