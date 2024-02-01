<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TheoryResource extends JsonResource
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
            'id'                => $this->ID,
            'name'              => $this->Name,
            'file_type'         => fileTypes($this->FileType),
            'file_extension'    => $this->FileExtension,
            'duration'          => $this->Duration,
            'path'              => $this->Path,
            'thumbnail'         => $this->Thumbnail,
            'created_at'        => date('Y-m-d H:i:s', $this->AddedTime)
        ];
    }

    public function with($request)
    {
        return [
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
        ];
    }
}
