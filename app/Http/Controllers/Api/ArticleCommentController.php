<?php

namespace App\Http\Controllers\Api;

use App\Article;
use App\ArticleComment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class ArticleCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Article $article)
    {
        // Initialize
        $comment = ArticleComment::with('replies')->where('article_id', $article->id)->whereNull('parent_id')->paginate(10);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list article comments',
            'result' => $comment
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Article $article, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'comment_reply_id' => 'nullable|exists:article_comments,id',
            'comment'  => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $comment = ArticleComment::create([
            'user_id'       => auth()->user()->id,
            'article_id'    => $article->id,
            'comment'       => $request->comment,
            'parent_id'     => $request->comment_reply_id
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article komen berhasil disimpan',
            'result' => $comment
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article, ArticleComment $comment)
    {
        $comment->replies;
        return response()->json([
            'status'    => true,
            'code' => 200,
            'message'   => 'Data tersedia',
            'result'      => $comment
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Article $article, ArticleComment $comment, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'comment_reply_id' => 'nullable|exists:article_comments,id',
            'comment'  => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $comment->update([
            'user_id'       => auth()->user()->id,
            'article_id'    => $article->id,
            'comment'       => $request->comment,
            'parent_id'     => $request->comment_reply_id
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'article komen berhasil disimpan',
            'result' => $comment
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article, ArticleComment $comment)
    {
     

        $comment->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'comment berhasil dihapus',
        ], 200);
    }
}
