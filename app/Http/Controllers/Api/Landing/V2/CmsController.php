<?php

namespace App\Http\Controllers\Api\Landing\V2;

use App\Content;
use App\Http\Controllers\Controller;
use App\Navigation;
use App\NavigationContent;
use App\NavigationImage;
use App\NavigationVideo;
use App\Services\CmsService;
use App\Services\SettingService;
use App\Utils\ResponseCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class CmsController extends Controller
{
    //

    protected $settingService;
    protected $cmsService;


    // Navigation
    public function __construct(SettingService $settingService,CmsService $cmsService)
    {
        $this->settingService = $settingService;
        $this->cmsService = $cmsService;
    }

    public function indexNavigation(){
        $data = Navigation::get();
        
        return ResponseCode::succesGet('Data berhasil ditemukan', $data);
    }

    public function storeNavigation(Request $request){
        $validator = Validator::make($request->all(), [
            'nama_navigasi' => 'required',
        ]);

        if($validator->fails()){
            return ResponseCode::errorPost($validator->errors()->first());
        }
        try {
            //code...
            $nav = Navigation::create([
                'nama_navigasi' => $request->nama_navigasi,
                'link' => Str::slug($request->nama_navigasi),
            ]);

            return ResponseCode::succesGet('Data berhasil ditambahkan', $nav);

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseCode::errorPost($th->getMessage());
        }
    }

    public function showNavigation($id){
        $data = Navigation::find($id);

        if(!$data){
            return ResponseCode::errorPost('Data tidak ditemukan');
        }

        return ResponseCode::succesGet('Data berhasil ditemukan', $data);
    }

    public function updateNavigation(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'nama_navigasi' => 'required',
        ]);

        if($validator->fails()){
            return ResponseCode::errorPost($validator->errors()->first());
        }

        try {
            //code...
            $data = Navigation::find($id);

            if(!$data){
                return ResponseCode::errorPost('Data tidak ditemukan');
            }

            $data->update([
                'nama_navigasi' => $request->nama_navigasi,
                'link' => Str::slug($request->nama_navigasi),
            ]);

            return ResponseCode::succesUpdate('Data berhasil diubah', $data);

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseCode::errorPost($th->getMessage());
        }
    }

    public function destroyNavigation($id){
        try {
            //code...
            $data = Navigation::find($id);

            if(!$data){
                return ResponseCode::errorPost('Data tidak ditemukan');
            }

            $data->delete();

            return ResponseCode::succesDelete('Data berhasil dihapus');

        } catch (\Throwable $th) {
            //throw $th;
            return ResponseCode::errorPost($th->getMessage());
        }
    }

    // Sub Navigation
    // public function suggestOpenAi(Request $request){

    //     $validator = Validator::make($request->all(), [
    //         'navigation_id' => 'required|exists:navigations,id'
    //     ]);

    //     if($validator->fails()){
    //         return ResponseCode::errorPost($validator->errors()->first());
    //     }


    //     try{

        
    //         $respSettings = $this->settingService->getSetting();

    //         $navigasi = Navigation::find($request->navigation_id);

    //         $prompt = "Saya memiliki sebuah website dengan nama website ".$respSettings['title']." dan Deskripsinya adalah ".$respSettings['description'].".Selain itu Kata kunci yang biasa saya gunakan adalah ".$respSettings['keyword']."dan Navigasi situs web saya adalah ". $navigasi->nama_navigasi .". berdasarkan tersebut, buatkan konten website dengan 1000 kata beserta meta tag keyword disarankan untuk meningkatkan SEO yang menarik serta sajikan dalam kode html.";
            

    //         $data = [
    //             "prompt" => $prompt,
    //         ];

    //         $promptGambar =  "Berikan Suatu Gambar yang menarik untuk website saya dengan nama website ".$respSettings['title']." dan Deskripsinya adalah ".$respSettings['description'].".Selain itu Kata kunci yang biasa saya gunakan adalah ".$respSettings['keyword']."dan Navigasi situs web saya adalah ". $navigasi->nama_navigasi .". berdasarkan tersebut, buatkan gambar yang menarik untuk website saya maximal 50kb.";

    //         $gambar = [
    //             "prompt" => $promptGambar
    //         ];

    //         $headers = [
    //             'Content-Type: application/json',
    //         ];

    //         $respOpenAi = $this->settingService->openAi($data, $headers);

    //         $respOpenAiImage = $this->settingService->openAiImage($gambar, $headers);

    //         $regex = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $respOpenAi['data']);

    //         // // remove for now
    //         // // $htmlTagsRegex = preg_replace('/<html>(.*?)<\/html>/is', '', $respOpenAi['data']);

    //         $reg = "```html";

    //         $regex2 = substr($regex, strpos($regex, $reg) + 0);
            
    //         $x = [
    //             'content' => $regex2
    //         ];

    //         $regexGetTitle = "/<title>(.*?)<\/title>/i";
    //         $getTitle = $regex2;

    //         $x['title'] = preg_match($regexGetTitle, $getTitle, $matches) ? $matches[1] : null;

    //         $x['keyword'] = $respSettings['keyword'];
    //         $x['navigation_id'] = $request->navigation_id;
    //         $x['slug'] = Str::slug($navigasi['nama_navigasi']); 
    //         $x['image'] = $respOpenAiImage['data'];


    //         // // START KEYWORD
    //         // // $delimKey = '<meta name="keywords" content="';
    //         // // $delimKey2 = '>';
    //         // // $explode = explode($delimKey, $regex2);
    //         // // // dd($explode);
    //         // // // return ResponseCode::succesGet('Data berhasil ditemukan', $explode);
    //         // // $explode2 = explode($delimKey2, $explode[1]);

    //         // // return ResponseCode::succesGet('Data berhasil ditemukan', $explode2);

    //         // // $x['keyword'] = $explode2;
    //         // // END KEYWORD

    //         if($x['title'] == null){
    //             return ResponseCode::errorPost('Gagal Mengambil Title!! Silahkan coba lagi');
    //         }

    //         // $respon = [
    //         //     "content" => "<head>\n<meta charset=\"UTF-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n<meta name=\"keywords\" content=\"webbagus, keren, mantap, Helm Saya Baru, toko helm online, helm motor, helm berkualitas, helm terbaru, helm aman, Web Helm Saya, helm keren\">\n<meta name=\"description\" content=\"Helm Saya baru merupakan website yang menyediakan berbagai jenis helm berkualitas dengan pilihan beragam. Tidak hanya itu, kami juga akan memberikan informasi terbaru tentang perkembangan dunia helm yang ada di pasaran, dilengkapi dengan tips dan trik dalam memilih helm yang tepat untuk anda\">\n<title>Helm Saya Baru - Tempatnya Helm Berkualitas</title>\n</head>\n<header>\n<h1>Helm Saya Baru</h1>\n</header>\n\n<nav>\n<a href=\"aboutus.html\">About Us</a>\n</nav>\n\n<main>\n<section>\n<h2>Selamat datang di Helm Saya Baru</h2>\n<p>Helm Saya baru adalah tempatnya para pecinta otomotif, khususnya bagi anda yang sedang mencari helm dengan berbagai pilihan model dan tipe. Kami menyediakan helm berkualitas dengan harga yang terjangkau dan pastinya aman untuk anda gunakan.</p>\n</section>\n\n<section>\n<h2>Kenapa harus membeli di Helm Saya Baru?</h2>\n<p>Sebagai pengguna kendaraan, keamanan dan kenyamanan adalah hal yang harus diperhatikan. Salah satunya adalah dengan menggunakan helm yang berkualitas. Helm Saya baru hadir sebagai solusi untuk anda yang sedang mencari helm yang aman, nyaman dan pastinya keren.</p>\n<p>Beberapa kelebihan membeli helm di Helm Saya Baru, antara lain:</p>\n<ul>\n<li>Beragam pilihan model helm</li>\n<li>Helm dengan standar SNI</li>\n<li>Harga yang terjangkau</li>\n<li>Pelayanan yang ramah dan fast respon</li>\n<li>Pengiriman yang aman dan cepat</li>\n</ul>\n</section>\n\n<section>\n<h2>Menyediakan berbagai model Helm</h2>\n<p>Di Helm Saya Baru, kami menyediakan berbagai model helm yang bisa anda pilih sesuai dengan kebutuhan dan gaya anda. Mulai dari helm full face, half face, cross helm sampai dengan helm open face.</p>\n</section>\n\n<section>\n<h2>Update informasi tentang perkembangan dunia helm</h2>\n<p>Nah, untuk Anda yang ingin tahu perkembangan terbaru tentang model helm, materi helm, cara perawatan helm dan lainnya, Helm Saya Baru akan memberikan informasi tersebut secara reguler di website ini. Dengan ini, Anda tidak akan ketinggalan informasi tentang helm yang Anda gunakan sehari-hari.</p>\n</section>\n</main>\n\n<footer>\n<p>&copy; 2022 Helm Saya Baru. All rights reserved. Developed by Helm Saya Baru Team.</p>\n</footer>\n",
    //         //     "title" => "Helm Saya Baru - Tempatnya Helm Berkualitas",
    //         //     "keyword" => "webbagus,keren,mantap",
    //         //     "slug" => 'about-us'
    //         // ];

    //         // $respAddToContent = $this->settingService->addToContent();
            
    //        return ResponseCode::succesGet('Data berhasil ditemukan', $x);

    //     }catch(\Throwable $th){
    //         return ResponseCode::errorPost($th->getMessage());
    //     }
    // }

    // public function getContent(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'navigation_id' => 'required|exists:contents,navigation_id'
    //     ]);

    //     if($validator->fails()){
    //         return ResponseCode::errorPost($validator->errors()->first());
    //     }

    //     $data = Content::where('navigation_id', $request->navigation_id)->get();

    //     $x = [];

    //     foreach($data as $val){
    //         $x[] = json_decode($val->data_content);
    //     }

    //     return ResponseCode::succesGet('Data berhasil ditemukan', $x);

    //     // try {
    //     //     //code...
    //     //     $data = Navigation::with('content')->find($request->navigation_id);

    //     //     if(!$data){
    //     //         return ResponseCode::errorPost('Data tidak ditemukan');
    //     //     }

    //     //     return ResponseCode::succesGet('Data berhasil ditemukan', $data);

    //     // } catch (\Throwable $th) {
    //     //     //throw $th;
    //     //     return ResponseCode::errorPost($th->getMessage());
    //     // }
    // }

    // public function storeContent(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'content' => 'required',
    //         'title' => 'required',
    //         'keyword' => 'required',
    //         'navigation_id' => 'required|exists:navigations,id',
    //         'slug' => 'required',
    //         'image' => 'required',
    //     ]);

    //     if($validator->fails()){
    //         return ResponseCode::errorPost($validator->errors()->first());
    //     }

    //     $resp = $this->settingService->addToContent($request->all());

    //     if(!$resp['status']){
    //         return ResponseCode::errorPost($resp['message']);
    //     }

    //     return ResponseCode::succesCreate($resp['message']);
    // }

    // New Suggess Open AI

    public function OpenAi(Request $request){
        $validator = Validator::make($request->all(), [
            'navigation_id' => 'required|exists:navigations,id',
            'keywords' => 'required',
            'description' => 'required',
        ]);

        if($validator->fails()){
            return ResponseCode::errorPost($validator->errors()->first());
        }

        $respSettings = $this->settingService->getSetting();

        $navigasi = Navigation::find($request->navigation_id);


        $prompt = "Buatkan sebuah artikel website dengan detail berikut :  Saya memiliki sebuah website dengan nama website ".$respSettings['title']." dan Deskripsinya adalah ".$request->description.".Selain itu Kata kunci yang biasa saya gunakan adalah ".$request->keywords."dan Navigasi situs web saya adalah ". $navigasi->nama_navigasi .".";

        $body = [
            'prompt'=>$prompt,
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        $respOpenAi = $this->settingService->openAi($body, $headers);

        return ResponseCode::succesGet('Data berhasil ditemukan', $respOpenAi);
    }

    public function OpenAiImage(Request $request){
        $validator = Validator::make($request->all(), [
            'navigation_id' => 'required|exists:navigations,id',
            'keywords' => 'required',
            'description' => 'required',
        ]);

        if($validator->fails()){
            return ResponseCode::errorPost($validator->errors()->first());
        }

        $respSettings = $this->settingService->getSetting();
        $navigasi = Navigation::find($request->navigation_id);
        $prompt = "Sebagai seorang desain grafis, buatkan gambar yang menarik untuk website saya dengan nama website ".$respSettings['title']." dan Deskripsinya adalah ".$request->description.".Selain itu Kata kunci yang biasa saya gunakan adalah ".$request->keywords."dan Navigasi situs web saya adalah ". $navigasi->nama_navigasi .". berdasarkan tersebut, buatkan gambar yang menarik untuk website saya maximal 150kb.";
        $body = [
            'prompt'=>$prompt,
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        $respAiImage = $this->settingService->openAiImage($body, $headers);

        return ResponseCode::succesGet('Generate Image berhasil dilakukan', $respAiImage['data']);
    }

    public function detailNavigation($navigation_id){
        $data = Navigation::with('video','image','konten')->find($navigation_id);

        if(!$data){
            return ResponseCode::errorPost('Data tidak ditemukan');
        }

        return ResponseCode::succesGet('Data berhasil ditemukan', $data);
    }

    public function storeContent(Request $request, $navigation_id){
        $validator = Validator::make($request->all(),[
            // "navigation_id.*" => "required",
            "url_video.*" => "required",
            "position_video.*" => "required",
            "image.*" => "required",
            "position_image.*" => "required",
            "keywords.*" => "required",
            "deskripsi.*" => "required",
            "konten.*" => "required"
        ]);

        if($validator->fails()){
            return ResponseCode::errorPost($validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            //code...
            $data = Navigation::find($navigation_id);

            if(!$data){
                return ResponseCode::errorPost('Data tidak ditemukan');
            }

            
            foreach($request->all() as $key){

                NavigationVideo::create([
                    'detail_navigation_id' => $navigation_id,
                    'url_video' => $key['url_video'],
                    'position_video' => $key['position_video'],
                ]);

                NavigationImage::create([
                    'detail_navigation_id' => $navigation_id,
                    'url_image' => $key['image'],
                    'position_image' => $key['position_image'],
                ]);

                NavigationContent::create([
                    'detail_navigation_id' => $navigation_id,
                    'konten' => $key['konten'],
                    'keywords' => $key['keywords'],
                    'deskripsi' => $key['deskripsi'],
                ]);
            }

            DB::commit();

            return ResponseCode::succesCreate('Data berhasil Ditambahkan');

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return ResponseCode::errorPost($th->getMessage());
        }
    }
    
}
