<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Rating;

class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data = Rating::with('user')->where('course_id', request('course_id'))->latest()->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Rating::create([
            'user_id'       => auth()->user()->id,
            'course_id'     => request('courseId'),
            'rating'        => request('rating'),
            'description'   => request('description')
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Terimakasih sudah memberikan ulasan'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, Rating $rating)
    {
        $rating->update([
            'rating'      => request('rating'),
            'description' => request('description')
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Ulasan berhasil diperbaharui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rating $rating)
    {
        $rating->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Ulasan berhasil dihapus'
        ]);
    }
}
