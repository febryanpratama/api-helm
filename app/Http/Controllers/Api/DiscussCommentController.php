<?php

namespace App\Http\Controllers\Api;

use App\Discuss;
use App\DiscussComment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class DiscussCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Discuss $discuss)
    {
        // Initialize
        $comment = DiscussComment::with('replies')->where('discuss_id', $discuss->id)->whereNull('parent_id')->paginate(10);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list discuss comments',
            'result' => $comment
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Discuss $discuss, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'comment_reply_id' => 'nullable|exists:discuss_comments,id',
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

        $comment = DiscussComment::create([
            'user_id'       => auth()->user()->id,
            'discuss_id'    => $discuss->id,
            'comment'       => $request->comment,
            'parent_id'     => $request->comment_reply_id
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'discuss komen berhasil disimpan',
            'result' => $comment
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Discuss $discuss, DiscussComment $comment)
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
    public function update(Discuss $discuss, DiscussComment $comment, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'comment_reply_id' => 'nullable|exists:discuss_comments,id',
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
            'discuss_id'    => $discuss->id,
            'comment'       => $request->comment,
            'parent_id'     => $request->comment_reply_id
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'discuss komen berhasil disimpan',
            'result' => $comment
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discuss $discuss, DiscussComment $comment)
    {
     

        $comment->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'comment berhasil dihapus',
        ], 200);
    }
}
