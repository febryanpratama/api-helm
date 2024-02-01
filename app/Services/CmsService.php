<?php

namespace App\Services;

use App\NavigationContent;
use App\NavigationImage;
use App\NavigationVideo;

class CmsService {
    // Video
    public function storeVideo($data){

        NavigationVideo::create([
            'detail_navigation_id' => '1',
            'url_video' => $data['navigation_id'],
            'position_video'=> $data['position_video']
        ]);

        return [
            'status' => true
        ];
    }

    // Image
    public function storeImage($data, $navigation_id){
        NavigationImage::create([
            'detail_navigation_id' => $navigation_id,
            'url_image' => $data['navigation_id'],
            'position_image'=> $data['position_image']
        ]);

        return [
            'status' => true
        ];
    }

    // Content
    public function storeContent($data,$navigation_id){
        NavigationContent::create([
            'detail_navigation_id' => $navigation_id,
            'konten' => $data['konten'],
            'keywords' => $data['keywords'],
            'deskripsi' => $data['deskripsi']
        ]);

        return [
            'status' => true
        ];
    }

}