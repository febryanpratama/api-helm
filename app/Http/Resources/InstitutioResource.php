<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;
use App\Address;

class InstitutioResource extends JsonResource
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
        $address    = Address::where(['company_id' => $this->ID, 'main_address' => 1])->first();

        if (!$address) {
            $address = Address::where(['company_id' => $this->ID])->first();
        }

        return [
            'id'            => $this->ID,
            'name'          => $this->Name,
            'email'         => $this->Email,
            'phone'         => $this->Phone,
            'address'       => $this->Address,
            'is_take_down'  => $this->IsTakeDown,
            'logo'          => $this->Logo,
            'added_time'    => $this->AddedTime,
            'facebook'      => $this->facebook,
            'instagram'     => $this->instagram,
            'youtube'       => $this->youtube,    
            'linkedin'      => $this->linkedin,
            'status'        => $this->status,
            'status_detail' => companyStatus($this->status),
            'is_verified'   => $this->is_verified,
            'city_id'       => $this->city_id,
            'city'          => $masterCity,
            'instructor'    => $this->admin,
            'seller'        => $this->admin,
            'address_id'    => ($address) ? $address->id : null
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
