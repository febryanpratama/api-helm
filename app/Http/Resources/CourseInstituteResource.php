<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;

class CourseInstituteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Initialize
        $masterCity = DB::table('master_kota')->where('id', $this->city_id)->first();

        return [
            'id'                    => $this->ID,
            'name'                  => $this->Name,
            'logo'                  => $this->Logo,
            'description'           => $this->Description,
            'phone'                 => $this->Phone,
            'address'               => $this->Address,
            'email'                 => $this->Email,
            'social_media'          => [
                'facebook'          => $this->facebook,
                'instagram'         => $this->instagram,
                'youtube'           => $this->youtube,
                'linkedin'          => $this->linkedin
            ],
            'status'                => $this->status,
            'status_detail'         => companyStatus($this->status),
            'is_verified'           => $this->is_verified,
            'reason_for_refusal'    => $this->reason_for_refusal,
            'city_id'               => $this->city_id,
            'city'                  => $masterCity,
            'created_at'            => date('Y-m-d H:i:s', $this->AddedTime),
            'updated_at'            => ($this->EditedTime) ? date('Y-m-d H:i:s', $this->EditedTime) : ''
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Toko.'
        ];
    }
}
