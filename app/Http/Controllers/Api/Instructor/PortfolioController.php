<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Course;
use App\CourseCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\PortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Majors;
use App\Portfolio;
use App\PortofolioMain;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        // Initialize
        $portfolio = Portfolio::where('company_id', $id)->get();

        // Custom Paginate
        $portfolios = $this->paginate($portfolio, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($portfolios as $val) {
            // Initialize
            $row['id']                      = $val->id;
            $row['company_id']              = $val->company_id;
            $row['company']                 = $val->company;
            $row['portfolio_photo']         = $val->path_photo;
            $row['portfolio_video']         = $val->path_video;
            $row['portfolio_description']   = $val->description;
            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Portofolio.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $portfolios->currentPage(),
                'from'              => 1,
                'last_page'         => $portfolios->lastPage(),
                'next_page_url'     => $portfolios->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $portfolios->perPage(),
                'prev_page_url'     => $portfolios->previousPageUrl(),
                'total'             => $portfolios->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PortfolioRequest $request)
    {
        // Initialize
        $portfolioPhoto       = request()->file('portfolio_photo');
        $portfolioVideo       = request()->file('portfolio_video');
        $portfolioDescription = $request->portfolio_description;

        if ($portfolioPhoto) {
            // Initialize
            $extPortfolioPhoto = $portfolioPhoto->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioPhoto == 'php' || $extPortfolioPhoto == 'sql' || $extPortfolioPhoto == 'js'|| $extPortfolioPhoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }
           
            $pathPortfolioPhoto = $portfolioPhoto->store('uploads/'.auth()->user()->company->Name.'/portfolio/photo', 'public');
            $pathPortfolioPhoto = env('SITE_URL').'/storage/'.$pathPortfolioPhoto;
            $portfolioPhoto     = $pathPortfolioPhoto;
        }

        if ($portfolioVideo) {
            // Initialize
            $extPortfolioVideo = $portfolioVideo->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioVideo == 'php' || $extPortfolioVideo == 'sql' || $extPortfolioVideo == 'js'|| $extPortfolioVideo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Video File Not Supported!'
                ]);

                die;
            }
            
            $pathPortfolioVideo = $portfolioVideo->store('uploads/'.auth()->user()->company->Name.'/portfolio/video', 'public');
            $pathPortfolioVideo = env('SITE_URL').'/storage/'.$pathPortfolioVideo;
            $portfolioVideo     = $pathPortfolioVideo;
        }

        // Create
        $portfolio = Portfolio::create([
            'company_id'    => auth()->user()->company_id,
            'path_photo'    => $portfolioPhoto,
            'path_video'    => $portfolioVideo,
            'description'   => $portfolioDescription
        ]);

        return new PortfolioResource($portfolio);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Initialize
        $portfolio = Portfolio::find($id);

        return new PortfolioResource($portfolio);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Initialize
        $portfolio = Portfolio::findOrFail($id);

        // Initialize
        $portfolioPhoto       = request()->file('portfolio_photo');
        $portfolioVideo       = request()->file('portfolio_video');
        $portfolioDescription = $request->portfolio_description;

        if ($portfolioPhoto) {
            // Initialize
            $extPortfolioPhoto = $portfolioPhoto->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioPhoto == 'php' || $extPortfolioPhoto == 'sql' || $extPortfolioPhoto == 'js'|| $extPortfolioPhoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($portfolio) {
                // Initialize
                $expPortfolio = explode('/', $portfolio->path_photo);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/photo/'.$expPortfolio[8]);
            }

            $pathPortfolioPhoto = $portfolioPhoto->store('uploads/'.auth()->user()->company->Name.'/portfolio/photo', 'public');
            $pathPortfolioPhoto = env('SITE_URL').'/storage/'.$pathPortfolioPhoto;
            $portfolioPhoto     = $pathPortfolioPhoto;
        }

        if ($portfolioVideo) {
            // Initialize
            $extPortfolioVideo = $portfolioVideo->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioVideo == 'php' || $extPortfolioVideo == 'sql' || $extPortfolioVideo == 'js'|| $extPortfolioVideo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Video File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($portfolio->path_video) {
                // Initialize
                $expPortfolio = explode('/', $portfolio->path_video);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/video/'.$expPortfolio[8]);
            }

            $pathPortfolioVideo = $portfolioVideo->store('uploads/'.auth()->user()->company->Name.'/portfolio/video', 'public');
            $pathPortfolioVideo = env('SITE_URL').'/storage/'.$pathPortfolioVideo;
            $portfolioVideo     = $pathPortfolioVideo;
        }

        // Portfolio
        $data = [
            'path_photo'  => ($portfolioPhoto) ? $portfolioPhoto : $portfolio->path_photo,
            'path_video'  => ($portfolioVideo) ? $portfolioVideo : $portfolio->path_video,
            'description' => ($portfolioDescription) ? $portfolioDescription : $portfolio->description
        ];

        $portfolio->update($data);

        return new PortfolioResource($portfolio);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check Exists Data
        $portfolio = Portfolio::find($id);
        
        if ($portfolio) {
            // Unlink File
            if ($portfolio->path_photo) {
                // Initialize
                $expPortfolio = explode('/', $portfolio->path_photo);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/photo/'.$expPortfolio[8]);
            }

            // Unlink File
            if ($portfolio->path_video) {
                // Initialize
                $expPortfolio = explode('/', $portfolio->path_video);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/video/'.$expPortfolio[8]);
            }
        }

        $portfolio->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    // Portofolio V2
    public function indexV2(Request $request, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::with('portofolio')->where('company_id', $id)->orderBy('id', 'desc')->get();

        // Custom Paginate
        $portfolios = $this->paginate($portfolio, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($portfolios as $val) {
            // Initialize
            $row['id']                      = $val->id;
            $row['company_id']              = $val->company_id;
            $row['product_name']            = $val->product_name;
            $row['product_description']     = $val->product_description;
            $row['is_service']              = $val->is_service;
            $row['type']                    = $val->is_service == 1 ? 'Jasa' : 'Barang';
            $row['location']                = $val->location;
            $row['owner']                   = $val->owner;
            $row['year']                    = $val->year;
            $row['totalcost']               = $val->totalcost;
            $row['status']                  = $val->status;
            $row['company_id']              = $val->company_id;
            $row['company']                 = $val->company;
            $row['portofolio_media']        = $val->portofolio;
            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Portofolio.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $portfolios->currentPage(),
                'from'              => 1,
                'last_page'         => $portfolios->lastPage(),
                'next_page_url'     => $portfolios->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $portfolios->perPage(),
                'prev_page_url'     => $portfolios->previousPageUrl(),
                'total'             => $portfolios->total()
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showV2($id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data['id']                      = $portfolio->id;
        $data['company_id']              = $portfolio->company_id;
        $data['product_name']            = $portfolio->product_name;
        $data['product_description']     = $portfolio->product_description;
        $data['is_service']              = $portfolio->is_service;
        $data['type']                    = $portfolio->is_service == 1 ? 'Jasa' : 'Barang';
        $data['location']                = $portfolio->location;
        $data['owner']                   = $portfolio->owner;
        $data['year']                    = $portfolio->year;
        $data['totalcost']               = $portfolio->totalcost;
        $data['status']                  = $portfolio->status;
        $data['company_id']              = $portfolio->company_id;
        $data['company']                 = $portfolio->company;
        $data['portofolio_media']        = $portfolio->portofolio;
        $data['created_at']              = $portfolio->created_at;
        $data['updated_at']              = $portfolio->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Portofolio.',
            'data'      => $data,
        ]);
    }

    public function storeV2(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'product_name'              => 'required|string',
            'is_service'                => 'required|in:0,1',
            'product_description'       => 'required|string',
            'location'                  => 'nullable|string',
            'owner'                     => 'nullable|string',
            'year'                      => 'nullable|string',
            'status'                    => 'required|in:0,1',
            'totalcost'                 => 'nullable|string',
            'portfolio_photo'           => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'portfolio_video'           => 'nullable|mimes:mp4|max:10240',
            'portfolio_description'     => 'required|string',
            'category_id'               => 'required|integer|exists:category,id',
            'detail_name'               => 'required|string',
            'detail_description'        => 'required|string',
            'price'                     => 'required|numeric',
            'unit_id'                   => 'required|integer|exists:unit,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request()->file('portfolio_photo') == null && request()->file('portfolio_video') == null) {
            $data = [
                'status'    => 'error',
                'message'   => 'Wajib mengupload photo atau video',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }


        // Initialize
        $portfolioPhoto       = request()->file('portfolio_photo');
        $portfolioVideo       = request()->file('portfolio_video');
        $portfolioDescription = $request->portfolio_description;

        if ($portfolioPhoto) {
            // Initialize
            $extPortfolioPhoto = $portfolioPhoto->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioPhoto == 'php' || $extPortfolioPhoto == 'sql' || $extPortfolioPhoto == 'js'|| $extPortfolioPhoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }
           
            $pathPortfolioPhoto = $portfolioPhoto->store('uploads/'.auth()->user()->company->Name.'/portfolio/photo', 'public');
            $pathPortfolioPhoto = env('SITE_URL').'/storage/'.$pathPortfolioPhoto;
            $portfolioPhoto     = $pathPortfolioPhoto;
        }

        if ($portfolioVideo) {
            // Initialize
            $extPortfolioVideo = $portfolioVideo->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioVideo == 'php' || $extPortfolioVideo == 'sql' || $extPortfolioVideo == 'js'|| $extPortfolioVideo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Video File Not Supported!'
                ]);

                die;
            }
            
            $pathPortfolioVideo = $portfolioVideo->store('uploads/'.auth()->user()->company->Name.'/portfolio/video', 'public');
            $pathPortfolioVideo = env('SITE_URL').'/storage/'.$pathPortfolioVideo;
            $portfolioVideo     = $pathPortfolioVideo;
        }

        // MAIN PORTOFOLIO
        $portfolio = PortofolioMain::create([
            'company_id'            => auth()->user()->company_id,
            'product_name'          => $request->product_name,
            'is_service'            => $request->is_service,
            'product_description'   => $request->product_description,
            'location'              => $request->location,
            'owner'                 => $request->owner,
            'year'                  => $request->year,
            'totalcost'             => $request->totalcost,
            'status'                => $request->status,
        ]);

        // Create
        $portfolio_media = Portfolio::create([
            'portofolio_main_id'    => $portfolio->id,
            'company_id'            => auth()->user()->company_id,
            'path_photo'            => $portfolioPhoto,
            'path_video'            => $portfolioVideo,
            'description'           => $portfolioDescription,
            'category_id'           => $request->category_id,
            'detail_name'           => $request->detail_name,
            'detail_description'    => $request->detail_description,
            'price'                 => $request->price,
            'unit_id'               => $request->unit_id,
        ]);


        $data['id']                      = $portfolio->id;
        $data['company_id']              = $portfolio->company_id;
        $data['product_name']            = $portfolio->product_name;
        $data['product_description']     = $portfolio->product_description;
        $data['is_service']              = $portfolio->is_service;
        $data['type']                    = $portfolio->is_service == 1 ? 'Jasa' : 'Barang';
        $data['location']                = $portfolio->location;
        $data['owner']                   = $portfolio->owner;
        $data['year']                    = $portfolio->year;
        $data['totalcost']               = $portfolio->totalcost;
        $data['status']                  = $portfolio->status;
        $data['company_id']              = $portfolio->company_id;
        $data['company']                 = $portfolio->company;
        $data['portofolio_media']        = $portfolio->portofolio;
        $data['created_at']              = $portfolio->created_at;
        $data['updated_at']              = $portfolio->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);

        // return new PortfolioResource($portfolio);
    }

    public function updateV2(Request $request, $id)
    {
        $portfolio = PortofolioMain::find($id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'product_name'              => 'required|string',
            'is_service'                => 'required|in:0,1',
            'product_description'       => 'required|string',
            'location'                  => 'nullable|string',
            'owner'                     => 'nullable|string',
            'year'                      => 'nullable|string',
            'status'                    => 'required|in:0,1',
            'totalcost'                 => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // MAIN PORTOFOLIO
        $portfolio->update([
            'product_name'          => $request->product_name,
            'is_service'            => $request->is_service,
            'product_description'   => $request->product_description,
            'location'              => $request->location,
            'owner'                 => $request->owner,
            'year'                  => $request->year,
            'totalcost'             => $request->totalcost,
            'status'                => $request->status,
        ]);


        $data['id']                      = $portfolio->id;
        $data['company_id']              = $portfolio->company_id;
        $data['product_name']            = $portfolio->product_name;
        $data['product_description']     = $portfolio->product_description;
        $data['is_service']              = $portfolio->is_service;
        $data['type']                    = $portfolio->is_service == 1 ? 'Jasa' : 'Barang';
        $data['location']                = $portfolio->location;
        $data['owner']                   = $portfolio->owner;
        $data['year']                    = $portfolio->year;
        $data['totalcost']               = $portfolio->totalcost;
        $data['status']                  = $portfolio->status;
        $data['company_id']              = $portfolio->company_id;
        $data['company']                 = $portfolio->company;
        $data['portofolio_media']        = $portfolio->portofolio;
        $data['created_at']              = $portfolio->created_at;
        $data['updated_at']              = $portfolio->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);
    }

    // DELETE
    public function destroyV2(Request $request, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($id);
        $portfolio_media = $portfolio->portofolio;

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portfolio tidak ditemukan',
            ]);
        }

        
        if (count($portfolio_media) > 0) {

            foreach ($portfolio_media as $key => $media) {
                // Unlink File
                if ($media->path_photo) {
                    // Initialize
                    $expPortfolio = explode('/', $media->path_photo);
    
                    @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/photo/'.$expPortfolio[8]);
                }
    
                // Unlink File
                if ($media->path_video) {
                    // Initialize
                    $expPortfolio = explode('/', $media->path_video);
    
                    @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/video/'.$expPortfolio[8]);
                }
        
                $media->delete();
            }
        }

        $portfolio->delete();



        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    // Portofolio Media
    public function mediaIndex(Request $request, $portofolio_id)
    {
        $portfolio = Portfolio::where('portofolio_main_id', $portofolio_id)->orderBy('id', 'desc')->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data portofolio media',
            'data'      => $portfolio,
        ]);
    }

    public function mediaStore(Request $request, $portofolio_id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($portofolio_id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            // 'portfolio_photo'           => 'nullable|mimes:jpeg,png,jpg|max:2048',
            // 'portfolio_video'           => 'nullable|mimes:mp4|max:10240',
            'portfolio_photo'           => 'nullable|max:2048',
            'portfolio_video'           => 'nullable|max:10240',
            'portfolio_description'     => 'required|string',
            'category_id'               => 'required|integer|exists:category,id',
            'detail_name'               => 'required|string',
            'detail_description'        => 'required|string',
            'price'                     => 'required|numeric',
            'unit_id'                   => 'required|integer|exists:unit,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data);
        }

        if (request()->file('portfolio_photo') == null && request()->file('portfolio_video') == null) {
            $data = [
                'status'    => 'error',
                'message'   => 'Wajib mengupload photo atau video',
                'code'      => 400
            ];

            return response()->json($data);
        }


        // Initialize
        $portfolioPhoto       = request()->file('portfolio_photo');
        $portfolioVideo       = request()->file('portfolio_video');
        $portfolioDescription = $request->portfolio_description;

        if ($portfolioPhoto) {
            // Initialize
            $extPortfolioPhoto = $portfolioPhoto->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioPhoto == 'php' || $extPortfolioPhoto == 'sql' || $extPortfolioPhoto == 'js'|| $extPortfolioPhoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }
           
            $pathPortfolioPhoto = $portfolioPhoto->store('uploads/'.auth()->user()->company->Name.'/portfolio/photo', 'public');
            $pathPortfolioPhoto = env('SITE_URL').'/storage/'.$pathPortfolioPhoto;
            $portfolioPhoto     = $pathPortfolioPhoto;
        }

        if ($portfolioVideo) {
            // Initialize
            $extPortfolioVideo = $portfolioVideo->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioVideo == 'php' || $extPortfolioVideo == 'sql' || $extPortfolioVideo == 'js'|| $extPortfolioVideo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Video File Not Supported!'
                ]);

                die;
            }
            
            $pathPortfolioVideo = $portfolioVideo->store('uploads/'.auth()->user()->company->Name.'/portfolio/video', 'public');
            $pathPortfolioVideo = env('SITE_URL').'/storage/'.$pathPortfolioVideo;
            $portfolioVideo     = $pathPortfolioVideo;
        }

        // Create
        $portfolio_media = Portfolio::create([
            'portofolio_main_id'    => $portfolio->id,
            'company_id'            => auth()->user()->company_id,
            'path_photo'            => $portfolioPhoto,
            'path_video'            => $portfolioVideo,
            'description'           => $portfolioDescription,
            'category_id'           => $request->category_id,
            'detail_name'           => $request->detail_name,
            'detail_description'    => $request->detail_description,
            'price'                 => $request->price,
            'unit_id'               => $request->unit_id,
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $portfolio_media,
        ]);
        
    }

    public function mediaUpdate(Request $request, $portofolio_id, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($portofolio_id);
        $portfolio_media = Portfolio::find($id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portfolio tidak ditemukan',
            ]);
        }

        if (!$portfolio_media) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portofolio media tidak ditemukan',
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'portfolio_photo'           => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'portfolio_video'           => 'nullable|mimes:mp4|max:10240',
            'portfolio_description'     => 'required|string',
            'category_id'               => 'required|integer|exists:category,id',
            'detail_name'               => 'required|string',
            'detail_description'        => 'required|string',
            'price'                     => 'required|numeric',
            'unit_id'                   => 'required|integer|exists:unit,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request()->file('portfolio_photo') == null && request()->file('portfolio_video') == null) {
            $data = [
                'status'    => 'error',
                'message'   => 'Wajib mengupload photo atau video',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }


        // Initialize
        $portfolioPhoto       = $portfolio_media->path_photo;
        $portfolioVideo       = $portfolio_media->path_video;
        $portfolioDescription = $request->portfolio_description;

        if (request()->file('portfolio_photo') != '') {
            // Initialize
            $portfolioPhoto       = request()->file('portfolio_photo');
            $extPortfolioPhoto = $portfolioPhoto->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioPhoto == 'php' || $extPortfolioPhoto == 'sql' || $extPortfolioPhoto == 'js'|| $extPortfolioPhoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($portfolio_media) {
                // Initialize
                $expPortfolio = explode('/', $portfolio_media->path_photo);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/photo/'.$expPortfolio[8]);
            }

            $pathPortfolioPhoto = $portfolioPhoto->store('uploads/'.auth()->user()->company->Name.'/portfolio/photo', 'public');
            $pathPortfolioPhoto = env('SITE_URL').'/storage/'.$pathPortfolioPhoto;
            $portfolioPhoto     = $pathPortfolioPhoto;
        }

        if (request()->file('portfolio_video') != '') {
            // Initialize
            $portfolioVideo       = request()->file('portfolio_video');
            $extPortfolioVideo = $portfolioVideo->getClientOriginalExtension();

            // Check Extension
            if ($extPortfolioVideo == 'php' || $extPortfolioVideo == 'sql' || $extPortfolioVideo == 'js'|| $extPortfolioVideo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Video File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($portfolio_media->path_video) {
                // Initialize
                $expPortfolio = explode('/', $portfolio_media->path_video);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/video/'.$expPortfolio[8]);
            }

            $pathPortfolioVideo = $portfolioVideo->store('uploads/'.auth()->user()->company->Name.'/portfolio/video', 'public');
            $pathPortfolioVideo = env('SITE_URL').'/storage/'.$pathPortfolioVideo;
            $portfolioVideo     = $pathPortfolioVideo;
        }

        // Create
        $portfolio_media->update([
            'path_photo'            => $portfolioPhoto,
            'path_video'            => $portfolioVideo,
            'description'           => $portfolioDescription,
            'category_id'           => $request->category_id,
            'detail_name'           => $request->detail_name,
            'detail_description'    => $request->detail_description,
            'price'                 => $request->price,
            'unit_id'               => $request->unit_id,
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $portfolio_media,
        ]);
    }

    public function mediaShow(Request $request, $portofolio_id, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($portofolio_id);
        $portfolio_media = Portfolio::find($id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portfolio tidak ditemukan',
            ]);
        }

        if (!$portfolio_media) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portofolio media tidak ditemukan',
            ]);
        }


        return response()->json([
            'status'    => 'success',
            'message'   => 'Detail',
            'data'      => $portfolio_media
        ]);
    }

    public function mediaDelete(Request $request, $portofolio_id, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($portofolio_id);
        $portfolio_media = Portfolio::find($id);

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portfolio tidak ditemukan',
            ]);
        }

        if (!$portfolio_media) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portofolio media tidak ditemukan',
            ]);
        }


        if ($portfolio_media) {
            // Unlink File
            if ($portfolio_media->path_photo) {
                // Initialize
                $expPortfolio = explode('/', $portfolio_media->path_photo);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/photo/'.$expPortfolio[8]);
            }

            // Unlink File
            if ($portfolio_media->path_video) {
                // Initialize
                $expPortfolio = explode('/', $portfolio_media->path_video);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/portfolio/video/'.$expPortfolio[8]);
            }
        }

        $portfolio_media->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    // Portofolio JADIKAN PRODUK
    public function portofolioProduct(Request $request, $id)
    {
        // Initialize
        $portfolio = PortofolioMain::find($id);
        $media = $portfolio->portofolio;

        if (!$portfolio) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data portfolio tidak ditemukan',
            ]);
        }

        // Checking Data

        if (count($media) == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Gagal, mohon lengkapi data portofolio anda',
            ]);
        }

        // Insert Product
        foreach ($media as $key => $value) {
            // checking Product
            $cat = $value->category_id;
            $check_product = Course::whereHas('courseCategory', function($q) use($cat) {
                $q->where('category_id', $cat);
            })->where('user_id', auth()->user()->id)->where('name', $portfolio->product_name)->where('description', $portfolio->product_description)->where('price_num', $value->price)->where('unit_id', $value->unit_id)->first();

            // Jika belum ada product nya
            if (!$check_product) {
                // init
                $price = rupiah($value->price);
                $price = str_replace('Rp.', '', $price);
                $product = Course::create([
                    'user_id'                   => auth()->user()->id,
                    'name'                      => $portfolio->product_name,
                    'description'               => $portfolio->product_description,
                    'thumbnail'                 => $value->path_photo,
                    'price'                     => $price,
                    'price_num'                 => $value->price,
                    'unit_id'                   => $value->unit_id,
                    'course_package_category'   => $portfolio->is_service,
                    'commission'                => 5,
                    'slug'                      => \Str::slug($value->product_name.'-'.auth()->user()->company->Name.'-'.auth()->user()->id.date('Yds'), '-'),
                ]);
    
                CourseCategory::create([
                    'course_id'     => $product->id,
                    'category_id'   => $value->category_id
                ]);
    

                // Insert Detail

                // check detail
                $check_detail = Majors::where('IDCourse', $product->id)->where('Name', $value->detail_name)->where('Details', $value->detail_description)->first();

                if (!$check_detail) {
                    $product_detail = Majors::create([
                        'IDCourse' => $product->id,
                        'IDCompany' => auth()->user()->company_id,
                        'Name' => $value->detail_name,
                        'Details' => $value->detail_description,
                        'AddedTime' => time(),
                        'AddedByIP' => $request->ip(),
                    ]);
                }
    
            } else { // jika sudah
                // check detail
                $check_detail = Majors::where('IDCourse', $check_product->id)->where('Name', $value->detail_name)->where('Details', $value->detail_description)->first();

                if (!$check_detail) {
                    $product_detail = Majors::create([
                        'IDCourse' => $check_product->id,
                        'IDCompany' => auth()->user()->company_id,
                        'Name' => $value->detail_name,
                        'Details' => $value->detail_description,
                        'AddedTime' => time(),
                        'AddedByIP' => $request->ip(),
                    ]);
                }
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
        ]);
    }

}
