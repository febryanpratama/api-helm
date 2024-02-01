<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
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
            'id'                        => $this->id,
            'company_id'                => $this->company_id,
            'company'                   => $this->company,
            'portfolio_photo'           => $this->path_photo,
            'portfolio_video'           => $this->path_video,
            'portfolio_description'     => $this->description,
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil'
        ];
    }
}
