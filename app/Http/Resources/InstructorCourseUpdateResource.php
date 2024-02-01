<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InstructorCourseUpdateResource extends JsonResource
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
            'unit_id'                       => $this->unit_id,
            'name'                          => $this->name,
            'description'                   => $this->description,
            'thumbnail'                     => $this->thumbnail,
            'price'                         => $this->price,
            'price_num'                     => $this->price_num,
            'slug'                          => $this->slug,
            'is_publish'                    => ($this->is_publish) ? true : false,
            'is_take_down'                  => $this->is_take_down,
            'is_private'                    => $this->is_private,
            'stock'                         => $this->user_quota_join,
            'weight'                        => $this->weight,
            'discount'                      => $this->discount,
            'is_sp'                         => $this->is_sp,
            'sp_file'                       => $this->sp_file,
            // 'course_type'                   => $this->course_type,
            'course_package_category'       => courseCategory($this->course_package_category),
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
