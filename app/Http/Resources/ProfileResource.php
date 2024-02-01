<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Course;
use App\Company;
use App\CourseCategory;
use App\Category;

class ProfileResource extends JsonResource
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
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'avatar'            => $this->avatar,
            'thumbnail'         => $this->thumbnail,
            'gender'            => $this->gender,
            'referral_code'     => $this->referral_code,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'last_login_at'     => $this->last_login_at,
            'curriculum_vitae'  => $this->curriculum_vitae,
            'role_id'           => $this->role_id,
            'is_admin_access'   => $this->is_admin_access,
            'owned_category'    => $this->_ownedCategory($this)
        ];
    }

    public function _ownedCategory($data)
    {
        if (auth()->user()->role_id == 6) {
            return null;
        }

        // Check Store
        if (!auth()->user()->company_id || auth()->user()->company_id == null) {
            return null;
        }

        // Initialize
        $store = Company::where('ID', auth()->user()->company_id)->first();

        if (!$store) {
            return null;
        }

        // Check Course by store and Category
        $courses        = Course::where('user_id', auth()->user()->id)->pluck('id');
        $courseCategory = CourseCategory::whereIn('course_id', $courses)->groupBy('category_id')->pluck('category_id');
        $category       = Category::whereIn('id', $courseCategory)->get();
        $data           = [];

        foreach($category as $val) {
            $row['id']      = $val->id;
            $row['name']    = $val->name;

            $data[] = $row;
        }

        return $data;
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data profil anda.'
        ];
    }
}
