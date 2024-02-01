<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\MasterLocation;

class OTPResource extends JsonResource
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
            'id'                => $this['id'],
            'name'              => $this['name'],
            'email'             => $this['email'],
            'phone'             => $this['phone'],
            'avatar'            => $this['avatar'],
            'thumbnail'         => $this['thumbnail'],
            'gender'            => null,
            'referral_code'     => $this['referral_code'],
            'is_admin_access'   => $this['is_admin_access'],
            'created_at'        => $this['created_at'],
            'updated_at'        => $this['updated_at'],
            'last_login_at'     => null,
            'store'             => $this->_store($this)
        ];
    }

    private function _store($store)
    {
        // Initialize
        $data = null;

        if (auth()->user()->role_id == 1) {
            if ($this['company']) {
                // Initialize
                $masterLocation = MasterLocation::where('kota_id', $this['company']['city_id'])->first();

                if ($masterLocation) {
                    $data = [
                        'status'            => $this['company']['status'],
                        'status_detail'     => companyStatus($this['company']['status']),
                        'is_verified'       => $this['company']['is_verified'],
                        'city_id'           => $this['company']['city_id'],
                        'city'              => $masterLocation
                    ];
                } else {
                    $data = [
                        'status'            => null,
                        'status_detail'     => null,
                        'is_verified'       => null,
                        'city_id'           => null,
                        'city'              => null
                    ];
                }
            } else {
                $data = [
                    'status'            => null,
                    'status_detail'     => null,
                    'is_verified'       => null,
                    'city_id'           => null,
                    'city'              => null
                ];
            }
        }
        
        return $data;
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil verifikasi OTP, Selamat!',
            'token'     => $this['token']
        ];
    }
}
