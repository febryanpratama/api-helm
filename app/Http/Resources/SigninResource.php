<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SigninResource extends JsonResource
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
            'id'            => $this->id,
            'email'         => $this->email,
            'type'          => ($this->role_id == 1) ? 'Mentor' : 'Murid',
            'is_validate_password' => $this->is_validate_password,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updatd_at
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil masuk, silahkan cek email anda.'
        ];
    }
}
