<?php

namespace App\Http\Controllers\Api;

use App\Discuss;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class DiscussController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $discuss = Discuss::latest()->paginate(10);

        if (request()->search) {
            $discuss = Discuss::where('title', 'like', '%' . request()->search . '%')->paginate(10);
        }

        // if (request()->is_popular) {
        //     $discuss = Discuss::where('is_popular', 1)->inRandomOrder()->paginate(10);
        // }

        // if (request()->search && request()->is_popular) {
        //     $discuss = Discuss::where('title', 'like', '%' . request()->search . '%')->where('is_popular', 1)->inRandomOrder()->paginate(10);
        // }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list discuss',
            'result' => $discuss
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
            'topic' => 'required',
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
        $file = null;

        if (request()->file('file')) {
            // Initialize
            $path       = request()->file('file')->store('uploads/discuss', 'public');
            $file       = env('SITE_URL').'/storage/'.$path;
        }

        $discuss = Discuss::create([
            'user_id'       => auth()->user()->id,
            'title'         => request('title'),
            'topic'         => request('topic'),
            'description'   => request('description'),
            'file'          => $file,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'discuss berhasil disimpan',
            'result' => $discuss
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Discuss $discuss)
    {
        return response()->json([
            'status'    => true,
            'code' => 200,
            'message'   => 'Data tersedia',
            'result'      => $discuss
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discuss $discuss)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'topic' => 'required',
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

        // Initialize
        $file = $discuss->file;

        if (request()->file('file')) {
            // Initialize
            $path      = request()->file('file')->store('uploads/discuss', 'public');
            $file = env('SITE_URL').'/storage/'.$path;

            // Check file
            if ($discuss->file) {
                $explodePath = explode('/', $discuss->file);

                @unlink('storage/uploads/discuss/'.$explodePath[6]);
            }
        }

        $discuss->update([
            'title'         => request('title'),
            'topic'         => request('topic'),
            'description'   => request('description'),
            'file'          => $file,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'discuss berhasil disimpan',
            'result' => $discuss
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discuss $discuss)
    {
        // Check file
        if ($discuss->file) {
            $explodePath = explode('/', $discuss->file);

            @unlink('storage/uploads/discuss/'.$explodePath[6]);
        }

        $discuss->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'discuss berhasil dihapus',
        ], 200);
    }
}
