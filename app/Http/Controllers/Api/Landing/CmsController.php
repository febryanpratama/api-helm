<?php

namespace App\Http\Controllers\Api\Landing;

use App\Article;
use App\Category;
use App\Company;
use App\Course;
use App\Http\Controllers\Controller;
use App\LandingArticlePopular;
use App\LandingCarousel;
use App\LandingCarouselEvent;
use App\LandingCategoryPopular;
use App\LandingLogo;
use App\LandingNavbar;
use App\LandingProductPopular;
use App\LandingPromo;
use App\LandingYoutube;
use Illuminate\Http\Request;
use Validator;

class CmsController extends Controller
{
    public function setting(Request $request)
    {
        $setting = LandingLogo::with('template')->latest()->first();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'setting web',
            'result' => $setting
        ], 200);
    }

    public function settingStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'logo' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'background' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }


        $setting = LandingLogo::latest()->first();

        if (!$setting) {
            $file_logo = null;
            $file_background = null;
            $file_fav = null;

            if ($request->file('logo')) {
                $file_logo = $request->file('logo')->store('uploads/logo', 'public');
                $file_logo = env('SITE_URL') . '/storage/' . $file_logo;
            }
            if ($request->file('background')) {
                $file_background = $request->file('background')->store('uploads/background', 'public');
                $file_background = env('SITE_URL') . '/storage/' . $file_background;
            }

            if ($request->file('fav')) {
                $file_fav = $request->file('fav')->store('uploads/fav', 'public');
                $file_fav = env('SITE_URL') . '/storage/' . $file_fav;
            }

            $setting = LandingLogo::create([
                'template_id' => $request->template_id,
                'logo' => $file_logo,
                'web_name' => $request->web_name,
                'web_desc' => $request->web_desc,
                'web_color1' => $request->web_color1,
                'web_color2' => $request->web_color2,
                'background' => $file_background,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'tiktok' => $request->tiktok,
                'instagram' => $request->instagram,
                'keyword' => $request->keyword,
                'domain_archiloka' => $request->domain_archiloka,
                'product_recommendation_title' => $request->product_recommendation_title,
                'product_recommendation_desc' => $request->product_recommendation_desc,
                'category_recommendation_title' => $request->category_recommendation_title,
                'category_recommendation_desc' => $request->category_recommendation_desc,
                'fav' => $file_fav,
                'gmaps' => $request->gmaps,
                'contact' => $request->contact,
            ]);
        } else {

            $file_logo = $setting->logo;
            $file_background = $setting->background;
            $file_fav = $setting->fav;

            if ($request->file('logo')) {
                // Unlink file_photo
                if ($setting->logo) {
                    // Initialize
                    $logo = explode('/', $setting->logo);

                    @unlink('storage/uploads/logo/'.end($logo));
                }

                $file_logo = $request->file('logo')->store('uploads/logo', 'public');
                $file_logo = env('SITE_URL') . '/storage/' . $file_logo;
            }

            if ($request->file('background')) {
                // Unlink file_photo
                if ($setting->background) {
                    // Initialize
                    $background = explode('/', $setting->background);

                    @unlink('storage/uploads/background/'.end($background));
                }

                $file_background = $request->file('background')->store('uploads/background', 'public');
                $file_background = env('SITE_URL') . '/storage/' . $file_background;
            }

            if ($request->file('fav')) {
                // Unlink file_photo
                if ($setting->fav) {
                    // Initialize
                    $fav = explode('/', $setting->fav);

                    @unlink('storage/uploads/fav/'.end($fav));
                }

                $file_fav = $request->file('fav')->store('uploads/fav', 'public');
                $file_fav = env('SITE_URL') . '/storage/' . $file_fav;
            }

            $setting->update([
                'template_id' => $request->template_id,
                'logo' => $file_logo,
                'web_name' => $request->web_name,
                'web_desc' => $request->web_desc,
                'web_color1' => $request->web_color1,
                'web_color2' => $request->web_color2,
                'background' => $file_background,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'tiktok' => $request->tiktok,
                'instagram' => $request->instagram,
                'keyword' => $request->keyword,
                'domain_archiloka' => $request->domain_archiloka,
                'product_recommendation_title' => $request->product_recommendation_title,
                'product_recommendation_desc' => $request->product_recommendation_desc,
                'category_recommendation_title' => $request->category_recommendation_title,
                'category_recommendation_desc' => $request->category_recommendation_desc,
                'fav' => $file_fav,
                'gmaps' => $request->gmaps,
                'contact' => $request->contact,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'setting web berhasil disimpan',
            'result' => $setting
        ], 200);
    }

    public function navbar(Request $request)
    {
        $navbar = LandingNavbar::orderBy('no_order', 'ASC')->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'navbar web',
            'result' => $navbar
        ], 200);
    }

    public function navbarStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'no_order' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $navbar = LandingNavbar::create([
            'name' => $request->name,
            'no_order' => $request->no_order,
            'link' => $request->link,
        ]);


        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'navbar web',
            'result' => $navbar
        ], 200);
    }

    public function navbarDetail(LandingNavbar $navbar, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail navbar web',
            'result' => $navbar
        ], 200);
    }

    public function navbarDelete(LandingNavbar $navbar, Request $request)
    {
        $navbar->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus navbar web',
        ], 200);
    }

    // CAROUSEL
    public function carousel(Request $request)
    {
        $carousel = LandingCarousel::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'carousel web',
            'result' => $carousel
        ], 200);
    }

    public function carouselStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'type' => 'required|in:1,2',
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($request->type == 1) {
            //set validation
            $validator = Validator::make(request()->all(), [
                'category_id' => 'required|exists:category,id'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        $file_image = null;

        if ($request->file('image')) {
            $file_image = $request->file('image')->store('uploads/carousel', 'public');
            $file_image = env('SITE_URL') . '/storage/' . $file_image;
        }

        $carousel = LandingCarousel::create([
            'image' => $file_image,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'carousel web berhasil disimpan',
            'result' => $carousel
        ], 200);
    }

    public function carouselDetail(LandingCarousel $carousel, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail carousel web',
            'result' => $carousel
        ], 200);
    }

    public function carouselDelete(LandingCarousel $carousel, Request $request)
    {
        // Unlink file_photo
        if ($carousel->image) {
            // Initialize
            $background = explode('/', $carousel->image);

            @unlink('storage/uploads/carousel/'.end($background));
        }
        
        $carousel->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus carousel web',
        ], 200);
    }

    // CAROUSEL
    public function carouselEvent(LandingCarousel $carousel, Request $request)
    {
        $carousel_event = LandingCarouselEvent::where('carousel_id', $carousel->id)->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'carousel event web',
            'result' => $carousel_event
        ], 200);
    }

    public function carouselEventStore(LandingCarousel $carousel, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:jpg,jpeg,png,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($carousel->type != '2') {
            $data = [
                'status'  => false,
                'message' => 'Gagal disimpan, hanya carousel type event yang bisa menambahkan event',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $file_path = null;

        if ($request->file('file')) {
            $file_path = $request->file('file')->store('uploads/carousel-event', 'public');
            $file_path = env('SITE_URL') . '/storage/' . $file_path;
        }

        $carousel = LandingCarouselEvent::create([
            'file' => $file_path,
            'link' => $request->link,
            'carousel_id' => $carousel->id,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'carousel event web berhasil disimpan',
            'result' => $carousel
        ], 200);
    }

    public function carouselEventDetail(LandingCarousel $carousel, LandingCarouselEvent $carousel_event, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail carousel event web',
            'result' => $carousel_event
        ], 200);
    }

    public function carouselEventDelete(LandingCarousel $carousel, LandingCarouselEvent $carousel_event, Request $request)
    {
        // Unlink file_photo
        if ($carousel_event->file) {
            // Initialize
            $background = explode('/', $carousel_event->file);

            @unlink('storage/uploads/carousel-event/'.end($background));
        }
        
        $carousel_event->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus carousel event web',
        ], 200);
    }

    public function carouselPopUpEvent(LandingCarousel $carousel, Request $request)
    {

        // type product
        if ($carousel->type != 2) {
            $data = [
                'status'  => false,
                'message' => 'hanya carousel type event',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $carousel_event = LandingCarouselEvent::where('carousel_id', $carousel->id)->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail carousel popup web',
            'result' => $carousel_event
        ], 200);
    }

    public function carouselPopUpProduct(LandingCarousel $carousel, Request $request)
    {

        // type product
        if ($carousel->type != 1) {
            $data = [
                'status'  => false,
                'message' => 'hanya carousel type product',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $product = Course::whereHas('courseCategory', function($q) use($carousel) {
            return $q->where('category_id', $carousel->category_id);
        })->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail carousel popup web',
            'result' => $product
        ], 200);
    }

    public function categoryPopUpProduct(Request $request)
    {

        //set validation
        $validator = Validator::make(request()->all(), [
            'category_id' => 'required|exists:category,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $category_id = $request->category_id;

        $product = Course::whereHas('courseCategory', function($q) use($category_id) {
            return $q->where('category_id', $category_id);
        })->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail category popup web',
            'result' => $product
        ], 200);
    }


    // CATEGORY POPULAR
    public function category(Request $request)
    {
        $category = LandingCategoryPopular::latest()->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'category popular web',
            'result' => $category
        ], 200);
    }

    public function categoryStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'image' => 'required|mimes:jpg,jpeg,png|max:2048',
            'category_id' => 'required|exists:category,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $file_image = null;

        if ($request->file('image')) {
            $file_image = $request->file('image')->store('uploads/category-popular', 'public');
            $file_image = env('SITE_URL') . '/storage/' . $file_image;
        }

        $category = LandingCategoryPopular::create([
            'image' => $file_image,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'category popular web berhasil disimpan',
            'result' => $category
        ], 200);
    }

    public function categoryDelete(LandingCategoryPopular $category, Request $request)
    {
        // Unlink file_photo
        if ($category->image) {
            // Initialize
            $background = explode('/', $category->image);

            @unlink('storage/uploads/category-popular/'.end($background));
        }
        
        $category->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus category popular web',
        ], 200);
    }

    // PRODUCT POPULAR
    public function product(Request $request)
    {
        $product = LandingProductPopular::latest()->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'product popular web',
            'result' => $product
        ], 200);
    }

    public function productStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'product_id' => 'required|exists:course,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }


        $product = LandingProductPopular::create([
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'product popular web berhasil disimpan',
            'result' => $product
        ], 200);
    }

    public function productDelete(LandingProductPopular $product, Request $request)
    {        
        $product->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus product popular web',
        ], 200);
    }
    
    public function productList(Request $request)
    {
        $product = Course::where('name', 'like', '%' . $request->search . '%')->inRandomOrder()->paginate(10);

        if ($request->company_id) {
            $company_id = $request->company_id;
            $product = Course::whereHas('user', function($q) use($company_id) {
                return $q->where('company_id', $company_id);
            })->where('name', 'like', '%' . $request->search . '%')->inRandomOrder()->paginate(10);
        }

        if ($request->category_id) {
            $category_id = $request->category_id;
            $product = Course::whereHas('courseCategory', function($q) use($category_id) {
                return $q->where('category_id', $category_id);
            })->where('name', 'like', '%' . $request->search . '%')->inRandomOrder()->paginate(10);
        }

        if ($request->is_popular) {
            $product = Course::whereHas('popular')->where('name', 'like', '%' . $request->search . '%')->inRandomOrder()->paginate(10);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data product.',
            'result'      => $product
        ]);
    }

    public function categoryList(Request $request)
    {
        // Initialize
        // $category = \DB::table('category')->where('name', 'like', '%' . $request->search . '%')
        // ->select('id','name','description','thumbnail','banner')
        // ->orderBy('name', 'ASC')
        // ->get();

        $category = Category::where('name', 'like', '%' . $request->search . '%')->orderBy('name', 'ASC')->paginate(10);

        // Initialize
        // $data = [];

        // foreach($category as $val) {
        //     $row['id']          = $val->id;
        //     $row['name']        = $val->name;
        //     $row['description'] = $val->description;
        //     $row['thumbnail']   = $val->thumbnail;
        //     $row['banner']      = env('SITE_URL').'/'.$val->banner;

        //     $data[] = $row;
        // }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data kategori.',
            'data'      => $category
        ]);
    }

    public function companyList(Request $request)
    {
        $company = Company::where('Name', 'like', '%' . $request->search . '%')->inRandomOrder()->take(10)->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data company.',
            'result'      => $company
        ]);
    }

    // PROMO
    public function promo(Request $request)
    {
        $promo = LandingPromo::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'promo web',
            'result' => $promo
        ], 200);
    }

    public function promoStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'type' => 'required|in:1,2',
            'file' => 'required|mimes:jpg,jpeg,png,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($request->type == 2) { // voucher
            //set validation
            $validator = Validator::make(request()->all(), [
                'category_id' => 'required|exists:category,id'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 1) { // discount
            //set validation
            $validator = Validator::make(request()->all(), [
                'product_id' => 'required|exists:course,id'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        $file_path = null;

        if ($request->file('file')) {
            $file_path = $request->file('file')->store('uploads/promo', 'public');
            $file_path = env('SITE_URL') . '/storage/' . $file_path;
        }

        $promo = LandingPromo::create([
            'file' => $file_path,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'product_id' => $request->product_id,
            'start_period' => date('Y-m-d H:i:s', strtotime($request->start_period)),
            'end_period' => date('Y-m-d H:i:s', strtotime($request->end_period)),
            'percentage' => $request->percentage,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'promo web berhasil disimpan',
            'result' => $promo
        ], 200);
    }

    public function promoDetail(LandingPromo $promo, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail promo web',
            'result' => $promo
        ], 200);
    }

    public function promoDelete(LandingPromo $promo, Request $request)
    {
        // Unlink file_photo
        if ($promo->file) {
            // Initialize
            $background = explode('/', $promo->file);

            @unlink('storage/uploads/promo/'.end($background));
        }
        
        $promo->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus promo web',
        ], 200);
    }

    // ARTICLE POPULAR
    public function article(Request $request)
    {
        $article = LandingArticlePopular::whereHas('article')->latest()->get();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article popular web',
            'result' => $article
        ], 200);
    }

    public function articleStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'article_id' => 'required|exists:articles,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $data_article = Article::find($request->article_id);
        $data_article->update(['is_popular' => 1]);

        $article = LandingArticlePopular::where([
            'article_id' => $request->article_id,
        ])->first();
        if (!$article) {
            $article = LandingArticlePopular::create([
                'article_id' => $request->article_id,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article popular web berhasil disimpan',
            'result' => $article
        ], 200);
    }

    public function articleDelete(LandingArticlePopular $article, Request $request)
    {
        $data_article = Article::find($request->article_id);
        $data_article->update(['is_popular' => 0]);

        $article->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus article popular web',
        ], 200);
    }

    // Landing YOUTUBE
    public function youtube(Request $request)
    {
        $article = LandingYoutube::latest()->first();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'youtube landing web',
            'result' => $article
        ], 200);
    }

    public function youtubeStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'youtube' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $youtube = LandingYoutube::first();

        if (!$youtube) {
            $youtube = LandingYoutube::create([
                'youtube' => $request->youtube,
            ]);
        } else {
            $youtube->update([
                'youtube' => $request->youtube,
            ]);
        }


        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'youtube landing berhasil disimpan',
            'result' => $youtube
        ], 200);
    }

    public function youtubeDelete(LandingYoutube $youtube, Request $request)
    {
        $youtube->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus youtube landing web',
        ], 200);
    }

    // tombol jadikan category popular
    public function makeCategoryPopular(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'category_id' => 'required|exists:category,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $data_category = Category::find($request->category_id);

        $category = LandingCategoryPopular::where([
            'category_id' => $request->category_id,
        ])->first();

        if (!$category) {
            $category = LandingCategoryPopular::create([
                'image' => env('SITE_URL') . '/'. $data_category->banner,
                'category_id' => $request->category_id,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil merubah category popular',
        ], 200);
    }

    // tombol jadikan category popular
    public function removeCategoryPopular(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'category_id' => 'required|exists:category,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $data_category = Category::find($request->category_id);

        $category = LandingCategoryPopular::where([
            'category_id' => $request->category_id,
        ])->first();


        if ($category) {
            $category->delete();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil merubah category popular',
        ], 200);
    }

    public function makeProductPopular(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'product_id' => 'required|exists:course,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }


        $product = LandingProductPopular::where([
            'product_id' => $request->product_id,
        ])->first();

        if (!$product) {
            $product = LandingProductPopular::create([
                'product_id' => $request->product_id,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'product popular web berhasil disimpan',
            'result' => $product
        ], 200);
    }

    public function information()
    {
        // hotel, bengkel, toko
        
        $app_name = request()->get('application');

        $description = null;

        $price = array(
            [
                'id' => 1,
                'name' => 'Free',
                'price' => 'Rp.0'
            ],

            [
                'id' => 2,
                'name' => 'Bronze',
                'price' => 'Rp.150.000'
            ],

            [
                'id' => 3,
                'name' => 'Silver',
                'price' => 'Rp.350.000'
            ],
        );


        $faq = array(
            // [
            //     'faq' => 'What is Lorem Ipsum?',
            //     'faq_desc' => "<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>",
            // ],

            // [
            //     'faq' => 'Why do we use it?',
            //     'faq_desc' => "<p>It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that it has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making it look like readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their infancy. Various versions have evolved over the years, sometimes by accident, sometimes on purpose (injected humour and the like).</p>",
            // ],
        );

        if ($app_name == 'rental') {
            $description = array(
                'text' => "<p>Berikut adalah beberapa manfaat dari aplikasi rental mobil yang dimiliki oleh PT Incore Systems Solutions, disusun sesuai dengan format yang telah diberikan:</p>

                <p>Benefit Aplikasi Rental Mobil PT Incore Systems Solutions: 1. Sistem Pemesanan yang Efisien: - Mudah melakukan pemesanan mobil secara online - Menetapkan jadwal sewa mobil dengan cepat dan akurat - Melacak status reservasi dalam satu aplikasi 2. Manajemen Armada yang Terorganisir: - Pencatatan informasi armada dengan mudah - Menetapkan status mobil (tersedia, disewa, dalam perbaikan, dll.) - Memantau ketersediaan mobil dalam satu platform yang terorganisir 3. Komunikasi Terfokus dengan Pelanggan: - Komunikasi intensif dengan pelanggan tanpa hambatan - Memberikan pemberitahuan tentang status reservasi dan informasi penting lainnya - Membantu staf rental mobil tetap fokus pada kebutuhan pelanggan 4. Akses Informasi Rental Mobil Dimana Saja dan Kapan Saja: - Akses mudah terhadap informasi rental mobil dari mana saja dan kapan saja - Memastikan tidak ada pesanan atau perubahan jadwal yang terlewat - Memberikan fleksibilitas dalam manajemen armada 5. Optimalkan Proses Pembayaran dan Pengembalian: - Menyediakan sistem pembayaran yang aman dan efisien - Memproses transaksi pembayaran dan pengembalian dengan cepat - Meningkatkan pengalaman pelanggan dalam proses sewa mobil 6. Analisis Kinerja Armada Secara Real-Time: - Melihat kinerja armada dalam waktu nyata - Analisis data penggunaan mobil untuk perencanaan perawatan dan perluasan armada - Memahami tren pemesanan untuk strategi pemasaran dan penawaran khusus 7. Sistem Keamanan Transaksi yang Efektif: - Menyediakan keamanan tingkat tinggi untuk transaksi online - Melindungi informasi pembayaran pelanggan dengan cermat - Memberikan kepercayaan kepada pelanggan untuk menyewa mobil secara online 8. Pembaruan dan Dukungan Berkala: - Penyediaan pembaruan perangkat lunak secara berkala - Dukungan pelanggan yang responsif untuk membantu staf rental mobil - Memastikan aplikasi selalu memenuhi kebutuhan industri jasa rental mobil</p>
                
                <p>Dengan aplikasi rental mobil dari PT Incore Systems Solutions, diharapkan bisnis rental mobil dapat meningkatkan efisiensi operasional, memberikan layanan terbaik kepada pelanggan, dan memastikan manajemen armada yang lebih terorganisir secara keseluruhan. Berita baik untuk Anda... Sekarang, Anda dapat dengan mudah mengarahkan bisnis rental mobil Anda ke arah pertumbuhan yang lebih besar tanpa harus repot mengelola armada, status mobil, dan proses reservasi secara manual. Kelola Layanan Rental Mobil Anda Dalam 1 Aplikasi Tidak perlu lagi berkunjung ke berbagai platform untuk mengelola armada, merespons pertanyaan pelanggan melalui email, WhatsApp, atau Telegram, dan menyusun laporan reservasi. Dengan aplikasi kami, semua yang Anda butuhkan untuk mengelola layanan rental mobil ada dalam satu tempat, membebaskan waktu Anda untuk fokus pada pertumbuhan bisnis Anda. Kini Anda cukup menggunakan Aplikasi Rental Mobil kami untuk mengelola armada dan layanan dengan berbagai pelanggan dan secara internal. Anda dan tim kini bisa fokus pada reservasi dan berinteraksi dengan pelanggan dalam satu aplikasi yang terintegrasi. Dengan Aplikasi Rental Mobil kami, waktu Anda dan rekan tim di bisnis rental mobil akan menjadi lebih berkualitas, produktif, efektif, dan efisien. Anda akan memiliki lebih banyak waktu untuk fokus pada pertumbuhan bisnis, memberikan layanan terbaik kepada pelanggan, dan menikmati keberhasilan dalam dunia jasa rental mobil. Apapun jenis bisnis rental mobil Anda, Aplikasi kami adalah solusi terbaik Anda dalam perjalanan menuju pertumbuhan bisnis yang cepat di industri jasa rental mobil! Saatnya mudah menjalankan dan mengelola operasional rental mobil, mulai dari reservasi armada, manajemen status mobil, hingga proses penyewaan di mana pun, kapan pun, dan dengan siapa pun. Kini, hasil yang efektif dan efisien dapat Anda capai dalam dunia jasa rental mobil.</p>"
            );

            $faq = array(
                [
                    'faq' => 'Apa itu Aplikasi Rental?',
                    'faq_desc' => "<p>Solusi digital untuk menyewa kendaraan, properti, atau peralatan dengan mudah.</p>",
                ],
    
                [
                    'faq' => "Cara sewa barang?",
                    'faq_desc' => "<p>Pilih item, tentukan durasi sewa, dan selesaikan pemesanan di aplikasi.</p>",
                ],

                [
                    'faq' => "Ada asuransi?",
                    'faq_desc' => "<p>Ya, asuransi tersedia untuk sebagian besar item yang disewakan.</p>",
                ],

                [
                    'faq' => "Syarat penyewaan?",
                    'faq_desc' => "<p>Memerlukan identifikasi yang valid dan verifikasi keamanan.</p>",
                ],

                [
                    'faq' => "Metode pembayaran apa saja?",
                    'faq_desc' => "<p>Kartu kredit, transfer bank, atau dompet digital.</p>",
                ],

                [
                    'faq' => "Bagaimana jika ada masalah?",
                    'faq_desc' => "<p>Hubungi dukungan pelanggan melalui chat atau email management@indonesiacore.com</p>",
                ],

                [
                    'faq' => "Bisa batalkan atau ubah sewaan?",
                    'faq_desc' => "<p>Ya, cek kebijakan pembatalan dan perubahan di aplikasi.</p>",
                ],
            );
        }


        if ($app_name == 'hotel') {
            $description = array(
                'text' => "<p>MY HOTEL TEMUKAN TEMPAT MENGINAP TERBAIK DENGAN (MY HOTEL) Mudah, Cepat dan Terpercaya (MY HOTEL) adalah aplikasi yang diciptakan khusus untuk anda mencari semua hotel di dunia dengan mudah dan efektif. Kami menawarkan berbagai pilihan hotel yang dapat memenuhi semua kebutuhan anda, baik untuk perjalanan bisnis maupun liburan.</p>

                
                <p>Fitur utama : &#61623; Pencarian Cepat (Temukan hotel terbaik dalam hitungan detik) &#61623; Harga Kompetitif (Penawaran eksklusif dan diskon menarik) &#61623; Ulasan Nyata (Baca ulasan dari pelanggan sebelumnya untuk membuat Keputusan yang tepat) &#61623; Layanan Pelanggan 24/7 (Tim kami siap membantu anda kapan saja)</p>
                
                <p>Mengapa memilih aplikasi (MY HOTEL) : &#61623; Kemudahan Akses (Aplikasi mudah digunakan diberbagai perangkat) &#61623; Pilihan Luas (Dari hotel, budget hingga akomodasi mewah) &#61623; Transaksi Aman (Sistem pembayaran yang aman dan terenkripsi) &#61623; Penawaran Eksklusif (Akses ke penawaran khusus hanya melalui aplikasi MY HOTEL)</p>
                
                <p>Unduh Sekarang dan Mulai Petualangan Anda! (MY HOTEL) tersedia di Google Play Store dan Apple APP Store. Unduh sekarang dan temukan pengalaman menginak tidak terlupakan.</p>
                
                <p>Testimoni Klien (GAMBAR ULASAN) Ya, Saya Mau Pakai (MY HOTEL), Bagaimana Caranya? Simple banget, Anda bisa mulai dengan tiga Langkah ini : Langkah 1 Daftar dan Buat Akun Anda Langkah 2 Atur dan pilih Hotel yang Anda sukai Langkah 3 Pilih Waktu dan Tanggal reservasi Anda</p>"
            );

            $faq = array(
                [
                    'faq' => 'Apa itu Aplikasi Hotel?',
                    'faq_desc' => "<p>Platform digital yang memungkinkan Anda untuk melakukan pemesanan kamar, melihat fasilitas hotel, dan mengakses layanan khusus dengan mudah dari ponsel Anda.</p>",
                ],
    
                [
                    'faq' => "Bagaimana cara melakukan pemesanan melalui aplikasi?",
                    'faq_desc' => "<p>Anda dapat mencari kamar yang tersedia, memilih tanggal menginap, dan menyelesaikan pemesanan langsung melalui aplikasi. Pembayaran dapat dilakukan secara online dengan aman.</p>",
                ],

                [
                    'faq' => "Apakah ada diskon khusus yang tersedia di aplikasi?",
                    'faq_desc' => "<p>Ya, pengguna aplikasi sering mendapatkan akses ke promosi eksklusif dan diskon khusus. Pastikan untuk memeriksa bagian &#39;Penawaran&#39; di aplikasi.</p>",
                ],

                [
                    'faq' => "Bagaimana cara membatalkan atau mengubah pemesanan?",
                    'faq_desc' => "<p>Anda dapat membatalkan atau mengubah pemesanan Anda melalui aplikasi. Kebijakan pembatalan akan tersedia di halaman detail pemesanan.</p>",
                ],

                [
                    'faq' => "Apakah aplikasi ini menyediakan informasi tentang fasilitas hotel?",
                    'faq_desc' => "<p>Ya, aplikasi menyediakan informasi terperinci tentang semua fasilitas hotel, termasuk restoran, spa, gym, dan lainnya.</p>",
                ],

                [
                    'faq' => "Bisakah saya melakukan check-in dan check-out melalui aplikasi?",
                    'faq_desc' => "<p>Kami menyediakan fitur check-in dan check-out digital untuk memudahkan proses Anda tanpa harus antri di resepsionis.</p>",
                ],

                [
                    'faq' => "Bagaimana cara mendapatkan dukungan jika saya mengalami masalah dengan aplikasi?",
                    'faq_desc' => "<p>Anda dapat menghubungi dukungan pelanggan kami melalui fitur chat di aplikasi atau mengirim email ke management@indonesiacore.com</p>",
                ],
            );
        }

        if ($app_name == 'toko') {
            $description = array(
                'text' => "<p>SELAMAT DATANG DI APLIKASI TOKO ONLINE Berikut adalah beberapa manfaat dari aplikasi toko online yang dimiliki oleh PT Incore Systems Solutions, disusun sesuai dengan format yang telah diberikan: Benefit Aplikasi Toko Online PT Incore Systems Solutions: 1. Pengelolaan Produk yang Efisien: - Mudah menambahkan dan mengelola produk secara online - Memperbarui stok dan informasi produk dengan cepat - Mencatat transaksi penjualan dan melacak inventaris dengan mudah 2. Organisasi Kategori dan Penawaran: - Membuat kategori produk dengan mudah - Menyusun penawaran dan diskon secara terstruktur - Memantau popularitas produk dan kategori dalam satu platform yang terorganisir 3. Komunikasi Terfokus dengan Pelanggan: - Komunikasi intensif dengan pelanggan tanpa hambatan - Memberikan pemberitahuan tentang penawaran dan promosi - Membantu staf toko online tetap fokus pada kebutuhan pelanggan 4. Akses Informasi Toko Dimana Saja dan Kapan Saja: - Akses mudah terhadap informasi toko dari mana saja dan kapan saja - Memastikan tidak ada pesanan atau pertanyaan pelanggan yang terlewat - Memberikan fleksibilitas dalam manajemen toko online 5. Optimalkan Proses Pembayaran dan Pengiriman: - Menyediakan sistem pembayaran yang aman dan efisien - Memproses pesanan dengan cepat dan akurat - Melacak status pengiriman dan memberikan informasi pelacakan kepada pelanggan 6. Analisis Kinerja Toko Secara Real-Time: - Melihat kinerja toko dalam waktu nyata</p>

                <p>- Analisis data penjualan untuk pengambilan keputusan yang lebih baik - Memahami tren pembelian untuk perencanaan inventaris dan promosi 7. Sistem Keamanan Transaksi yang Efektif: - Menyediakan keamanan tingkat tinggi untuk transaksi online - Melindungi informasi pembayaran pelanggan dengan cermat - Memberikan kepercayaan kepada pelanggan untuk berbelanja secara online 8. Pembaruan dan Dukungan Berkala: - Penyediaan pembaruan perangkat lunak secara berkala - Dukungan pelanggan yang responsif untuk membantu staf toko online - Memastikan aplikasi selalu memenuhi kebutuhan perkembangan industri e- commerce Berita baik untuk Anda... Sekarang, Anda dapat dengan mudah mengarahkan bisnis online Anda ke arah pertumbuhan yang lebih besar tanpa harus repot mengelola inventaris, kategori produk, dan proses penjualan secara manual.</p>
                
                <p>Kelola Toko Online Anda Dalam 1 Aplikasi Tidak perlu lagi berkunjung ke berbagai platform untuk mengelola inventaris, merespons pertanyaan pelanggan melalui email, WhatsApp, atau Telegram, dan menyusun laporan penjualan. Dengan aplikasi kami, semua yang Anda butuhkan untuk mengelola toko online ada dalam satu tempat, membebaskan waktu Anda untuk fokus pada pertumbuhan bisnis Anda. Kini Anda cukup menggunakan Aplikasi Toko Online kami untuk mengelola produk dan layanan dengan berbagai pelanggan dan secara internal. Anda dan tim kini bisa fokus pada penjualan dan berinteraksi dengan pelanggan dalam satu aplikasi yang terintegrasi. Dengan Aplikasi Toko Online kami, waktu Anda dan rekan tim di toko online akan menjadi lebih berkualitas, produktif, efektif, dan efisien. Anda akan memiliki lebih banyak waktu untuk fokus pada pertumbuhan bisnis, memberikan layanan terbaik kepada pelanggan, dan menikmati keberhasilan dalam dunia e-commerce.</p>
                
                <p>Apapun jenis bisnis Anda, Aplikasi Toko Online kami adalah solusi terbaik Anda dalam perjalanan menuju pertumbuhan bisnis yang cepat di dunia e-commerce! Saatnya mudah menjalankan dan mengelola operasional toko online, mulai dari tugas karyawan, manajemen produk, hingga proyek promosi di mana pun, kapan pun, dan dengan siapa pun. Kini, hasil yang efektif dan efisien dapat Anda capai dalam dunia e-commerce.</p>"
            );
        }




        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'data info',
            'description' => $description,
            'price' => $price,
            'faq' => $faq,
        ], 200);
    }
}
