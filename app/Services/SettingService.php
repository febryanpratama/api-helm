<?php

namespace App\Services;

use App\Content;
use App\LandingLogo;
use App\Utils\ResponseCode;

class SettingService{
    static function getSetting(){
        $setting = LandingLogo::latest()->first();

        $data = [
            'keyword' => $setting->keyword,
            'description' => $setting->web_desc,
            'title' => $setting->web_name,
        ];

        return $data;
        
    }

    static function openAi($data, $headers){
        $url = 'https://chatgpt.febryancaesarpratama.com/api/text';
        $ch = curl_init($url);
        
        // curl_setopt($ch, CURLOPT_URL, 'https://chatgpt.febryancaesarpratama.com/api/text');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return [
                'status' => false,
                'message' => curl_error($ch)
            ];
        }
        
        curl_close($ch);

        $result = json_decode($response, true);

        if($result['status']){
            return [
                // 'status' => true,
                // 'message' => $result['message'],
                'data' => $result['data']['content']
            ];

        }else{

            return [
                // 'status' => false,
                'data' => $result['message']
            ];
        }

        
    }

    static function openAiImage($data, $headers){
        $url = 'https://chatgpt.febryancaesarpratama.com/api/image';
        $ch = curl_init($url);
        
        // curl_setopt($ch, CURLOPT_URL, 'https://chatgpt.febryancaesarpratama.com/api/text');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return [
                'status' => false,
                'message' => curl_error($ch)
            ];
        }
        
        curl_close($ch);

        $result = json_decode($response, true);

        if($result['status']){

            $url = $result['data']['image'];
            
            return [
                'data' => $url
            ];

            // return ResponseCode::errorPost('Gagal mengambil gambar');

        }else{

            return ResponseCode::errorPost($result['message']);
        }
    }

    static function addToContent($data){

 
        $jsonData = [
            "content" => $data['content'],
            "title" => $data['title'],
            "keyword" => $data['keyword'],
            "slug" => $data['slug'],
            'image' => $data['image'],
        ];

        $content = Content::create([
            'navigation_id' => $data['navigation_id'],
            'data_content' => json_encode($jsonData),
        ]);

        if(!$content){
            return [
                'status' => false,
                'message' => 'Gagal menambahkan data'
            ];
        }

        return [
            'status' => true,
            'message' => 'Berhasil menambahkan data',
            'data' => $content
        ];
    }

}