<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Str;
use App\Article;

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
        $articles = Article::where('user_id', auth()->user()->id)->latest()->paginate(10);

        return view('article.index', compact('articles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Initialze
        $thumbnail = null;

        if (request()->file('upload_file')) {
            // Initialize
            $path       = request()->file('upload_file')->store('uploads/article', 'public');
            $thumbnail  = env('SITE_URL').'/storage/'.$path;
        }

        Article::create([
            'user_id'       => auth()->user()->id,
            'title'         => request('title'),
            'slug'          => Str::slug(request('title'), '-').'-'.date('Y-m-d-h-i-s-s'),
            'description'   => request('description'),
            'thumbnail'     => $thumbnail
        ]);

        return response()->json([
            'status'    => 'true',
            'message'   => 'Data berhasil ditambahkan'
        ]);
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
            'message'   => 'Data tersedia',
            'data'      => $article
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
        $thumbnail = null;

        if (request()->file('upload_file')) {
            // Initialize
            $path      = request()->file('upload_file')->store('uploads/article', 'public');
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
            'thumbnail'   => $thumbnail
        ]);

        return response()->json([
            'status'    => 'true',
            'message'   => 'Data berhasil diperbaharui'
        ]);
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
            'status'    => 'true',
            'message'   => 'Data berhasil dihapus'
        ]);
    }
}
