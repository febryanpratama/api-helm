<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Story;
use App\StoryMedia;
use App\Transformers\StoryMediaTransformer;
use App\Transformers\StoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Validator;
use Image;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiStoriesController extends Controller
{
    public function myStory(Request $request)
    {
        // Initialize
        $story = Story::whereHas('storyMedias')
                ->where('IDUser', auth()->user()->id)
                ->orderBy('ID', 'desc')
                ->get();

        // Custom Paginate
        $storys = $this->paginate($story, 20, null, ['path' => $request->fullUrl()]);
        $data   = [];

        foreach ($storys as $val) {
            $row['id']          = $val->ID;
            $row['user_id']     = $val->IDUser;
            $row['user_name']   = $val->user->name;
            $row['caption']     = $val->caption;
            $row['hastag']      = $val->hastag;
            $row['is_popular']  = $val->is_popular;
            $row['media']       = $val->storyMedias;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Toko.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $storys->currentPage(),
                'from'              => 1,
                'last_page'         => $storys->lastPage(),
                'next_page_url'     => $storys->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $storys->perPage(),
                'prev_page_url'     => $storys->previousPageUrl(),
                'total'             => $storys->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stories'           => 'required|max:255',
            'type'              => 'required|numeric|in:1,2',
            'file'              => 'required|mimes:jpeg,png,jpg,mp4|max:10240',
            'video_thumbnail'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];

            return response()->json($data, 400);
        }

        $story = Story::create([
            'IDUser' => auth()->user()->id,
            'Stories' => $request->stories,
            'AddedTime'=>time(),
            'AddedByIP'=>$request->ip(),
        ]);

        if ($request->type == '1') {
            if ($request->hasFile('file')) {

                $file_foto = $request->file('file');
                $md5_name = uniqid() . md5_file( $file_foto->getRealPath() );
				$ext = $file_foto->getClientOriginalExtension();
                $thumb = null;

                $path = $request->file('file')->store('uploads/'.auth()->user()->company->Name.'/stories', 'public');
                $path = env('SITE_URL').'/storage/'.$path;


                if ($ext == 'png') {
                    $get_filename = explode('/', $path);
    
                    // make tumbnail
                    if (!file_exists(storage_path( '/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb' ))) {
                        mkdir(storage_path( '/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb' ), 0777, true);
                    }
                    $img = Image::make($request->file('file'));
                    $img->resize(320, 240);
                    $img->save(storage_path().'/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb/thumb_'. end($get_filename));
    
                    $thumb =  env('SITE_URL').'/storage/'. 'uploads/'.auth()->user()->company->Name.'/stories/thumb/thumb_'. end($get_filename);
                }
    
                $file = explode('/', $path);
                $size = 0;
                $check_file = Storage::disk('public')->exists('uploads/'.auth()->user()->company->Name.'/stories/'. end($file));

                if ($check_file) {
                    $size = round(Storage::disk('public')->size('uploads/'.auth()->user()->company->Name.'/stories/' . end($file)) / 1024);
                }

                $story_media = StoryMedia::create([
                    'IDStorie'      => $story->ID,
                    'SourceName'    => '',
                    'Width'         => 400,
                    'Height'        => 600,
                    'Location'      => $path,
                    'ThumbLocation' => $thumb,
                    'Size'          => $size,
                    'Type'          => 'image',
                    'AddedTime'     => time(),
                    'AddedByIP'     => $request->ip(),
                ]);
            }
        } else { // video
            if ($request->hasFile('file')) {
                $path = $request->file('file')->store('uploads/'.auth()->user()->company->Name.'/stories', 'public');
                $path = env('SITE_URL').'/storage/'.$path;

                $thumb = null;
                if ($request->hasFile('video_thumbnail')) {
                    $thumb = $request->file('video_thumbnail')->store('uploads/'.auth()->user()->company->Name.'/stories/thumb', 'public');
                    $thumb = env('SITE_URL').'/storage/'.$thumb;
                }

                $file = explode('/', $path);
                $size = 0;
                $check_file = Storage::disk('public')->exists('uploads/'.auth()->user()->company->Name.'/stories/'. end($file));

                if ($check_file) {
                    $size = round(Storage::disk('public')->size('uploads/'.auth()->user()->company->Name.'/stories/' . end($file)) / 1024);
                }
    
                $story_media = StoryMedia::create([
                    'IDStorie'      => $story->ID,
                    'SourceName'    => '',
                    'Width'         => 400,
                    'Height'        => 600,
                    'Location'      => $path,
                    'ThumbLocation' => $thumb,
                    'Size'          => $size,
                    'Type'          => 'video',
                    'AddedTime'     => time(),
                    'AddedByIP'     => $request->ip(),
                ]);
            }
        }

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil disimpan',
            'data'      => $story
        ];

        return response()->json($response, 200);
    }

    public function update(Story $story, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stories'           => 'required|max:255',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];

            return response()->json($data, 400);
        }

        $story->update([
            'Stories'       => $request->stories,
            'EditedTime'    => time(),
            'EditedByIP'    => $request->ip(),
        ]);

        // Initialize
        $row['id']          = $story->ID;
        $row['user_id']     = $story->IDUser;
        $row['user_name']   = $story->user->name;
        $row['caption']     = $story->caption;
        $row['hastag']      = $story->hastag;
        $row['media']       = $story->storyMedias;

        $data = $row;

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'berhasil dirubah',
            'data'      => $data
        ];

        return response()->json($response, 200);
    }

    public function detail(Story $story, Request $request)
    {
        // Initialize
        $row['id']          = $story->ID;
        $row['user_id']     = $story->IDUser;
        $row['user_name']   = $story->user->name;
        $row['caption']     = $story->caption;
        $row['hastag']      = $story->hastag;
        $row['is_popular']  = $story->is_popular;
        $row['media']       = $story->storyMedias;

        $data = $row;

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Detail story',
            'data'      => $data
        ];

        return response()->json($response, 200);
    }

    public function delete(Story $story)
    {
        // Check User Story
        if ($story->IDUser != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        // Initialize
        $media = $story->storyMedias;

        if (count($media) > 0) {
            foreach ($media as $key => $value) {
                
                // delete image
                $path = explode('/storage', $value->Location);
                $image_path = public_path().'/storage/' . end($path);

                if (file_exists($image_path)){
                    unlink($image_path);
                }

                // delete thumb
                if ($value->ThumbLocation) {
                    $path_thumb = explode('/storage', $value->ThumbLocation);
                    $image_path_thumb = public_path().'/storage/' . end($path_thumb);
            
                    if (file_exists($image_path_thumb)){
                        unlink($image_path_thumb);
                    }
                }
            }
        }

        $story->delete();

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'hapus berhasil'
        ];

        return response()->json($response, 200);
    }

    public function storyMedia(Story $story, Request $request)
    {
        // Initialize
        $storyMedia = StoryMedia::where('IDStorie', $story->ID)->get();
        $data       = [];

        foreach($storyMedia as $val) {
            // Initialize
            $row['id']              = $val->ID;
            $row['story_Id']        = $val->IDStorie;
            $row['location']        = $val->Location;
            $row['thumblocation']   = $val->ThumbLocation;
            $row['typemedia']       = $val->Type;
            $row['size']            = $val->Size;

            $data = $row;
        }

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'list story media',
            'data'      => $data
        ];

        return response()->json($response, 200);
    }

    public function storyMediaStore(Story $story, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file'  => 'required|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($request->hasFile('file')) {

            $file_foto = $request->file('file');
            $md5_name = uniqid() . md5_file( $file_foto->getRealPath() );
            $ext = $file_foto->getClientOriginalExtension();
            $thumb = null;

            $path = $request->file('file')->store('uploads/'.auth()->user()->company->Name.'/stories', 'public');
            $path = env('SITE_URL').'/storage/'.$path;


            if ($ext == 'png') {
                $get_filename = explode('/', $path);

                // make tumbnail
                if (!file_exists(storage_path( '/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb' ))) {
                    mkdir(storage_path( '/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb' ), 0777, true);
                }
                $img = Image::make($request->file('file'));
                $img->resize(320, 240);
                $img->save(storage_path().'/app/public/uploads/'.auth()->user()->company->Name.'/stories/thumb/thumb_'. end($get_filename));

                $thumb =  env('SITE_URL').'/storage/'. 'uploads/'.auth()->user()->company->Name.'/stories/thumb/thumb_'. end($get_filename);
            }

            $file = explode('/', $path);
            $size = 0;
            $check_file = Storage::disk('public')->exists('uploads/'.auth()->user()->company->Name.'/stories/'. end($file));

            if ($check_file) {
                $size = round(Storage::disk('public')->size('uploads/'.auth()->user()->company->Name.'/stories/' . end($file)) / 1024);
            }

            $story_media = StoryMedia::create([
                'IDStorie'      => $story->ID,
                'SourceName'    => '',
                'Width'         => 400,
                'Height'        => 600,
                'Location'      => $path,
                'ThumbLocation' => $thumb,
                'Size'          => $size,
                'Type'          => 'image',
                'AddedTime'     => time(),
                'AddedByIP'     => $request->ip(),
            ]);
        }

        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'berhasil disimpan',
            'data'      => $story_media
        ];

        return response()->json($data, 200);
    }

    public function storyMediaDelete(Story $story, StoryMedia $media)
    {
        // delete image
        $path = explode('/storage', $media->Location);
        $image_path = public_path().'/storage/' . end($path);

        if (file_exists($image_path)){
            unlink($image_path);
        }

        // delete thumb
        if ($media->ThumbLocation) {
            $path_thumb = explode('/storage', $media->ThumbLocation);
            $image_path_thumb = public_path().'/storage/' . end($path_thumb);
    
            if (file_exists($image_path_thumb)){
                unlink($image_path_thumb);
            }
        }

        $media->delete();

        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'hapus berhasil'
        ];

        return response()->json($data, 200);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
