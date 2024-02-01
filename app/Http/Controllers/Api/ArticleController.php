<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Str;
use App\Article;
use Validator;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $articles = Article::latest()->paginate(10);

        if (request()->search) {
            $articles = Article::where('title', 'like', '%' . request()->search . '%')->paginate(10);
        }

        if (request()->is_popular) {
            $articles = Article::where('is_popular', 1)->inRandomOrder()->paginate(10);
        }

        if (request()->search && request()->is_popular) {
            $articles = Article::where('title', 'like', '%' . request()->search . '%')->where('is_popular', 1)->inRandomOrder()->paginate(10);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list article',
            'result' => $articles
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'file'  => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // Initialze
        $thumbnail = null;

        if (request()->file('file')) {
            // Initialize
            $path       = request()->file('file')->store('uploads/article', 'public');
            $thumbnail  = env('SITE_URL').'/storage/'.$path;
        }

        $article = Article::create([
            'user_id'       => auth()->user()->id,
            'title'         => request('title'),
            'slug'          => Str::slug(request('title'), '-').'-'.date('Y-m-d-h-i-s-s'),
            'description'   => request('description'),
            'keyword'       => request('keyword'),
            'thumbnail'     => $thumbnail,
            'summary'       => request('summary'),
            'is_popular'    => request('is_popular') ? request('is_popular') : 0,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article berhasil disimpan',
            'result' => $article
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return response()->json([
            'status'    => true,
            'code' => 200,
            'message'   => 'Data tersedia',
            'result'      => $article
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        // Initialize
        $thumbnail = $article->thumbnail;

        if (request()->file('file')) {
            // Initialize
            $path      = request()->file('file')->store('uploads/article', 'public');
            $thumbnail = env('SITE_URL').'/storage/'.$path;

            // Check Thumbnail
            if ($article->thumbnail) {
                $explodePath = explode('/', $article->thumbnail);

                @unlink('storage/uploads/article/'.$explodePath[6]);
            }
        }

        $article->update([
            'title'       => request('title'),
            'description' => request('description'),
            'summary'     => request('summary'),
            'thumbnail'   => $thumbnail,
            'keyword'       => request('keyword'),
            'is_popular'    => request('is_popular') ? request('is_popular') : 0,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article berhasil disimpan',
            'result' => $article
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        // Check Thumbnail
        if ($article->thumbnail) {
            $explodePath = explode('/', $article->thumbnail);

            @unlink('storage/uploads/article/'.$explodePath[6]);
        }

        $article->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article berhasil dihapus',
        ], 200);
    }
}
