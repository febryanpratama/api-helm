<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WholesalePrice;

class WholesalePriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $data = WholesalePrice::where('course_id', request('course_id'))->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan Harga Grosir.',
            'data'      => $data
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
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'course_id' => 'required',
            'qty'       => 'required',
            'price'     => 'required'
        ]);

        $data = WholesalePrice::create([
            'course_id' => request('course_id'),
            'price'     => request('price'),
            'qty'       => request('qty')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan Harga Grosir.',
            'data'      => $data
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
        // Initialize
        $data = WholesalePrice::with('course')->where('id', $id)->first();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan Harga Grosir.',
            'data'      => $data
        ]);
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
        // Validation
        $validated = $request->validate([
            'qty'   => 'required',
            'price' => 'required'
        ]);

        // Initialize
        $data = WholesalePrice::with('course')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        if ($data->course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        $data->update([
            'qty'   => request('qty'),
            'price' => request('price')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $data
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Initialize
        $data = WholesalePrice::with('course')->where('id', $id)->first();
        
        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        if (!$data->course) {
            $data->delete();

            return response()->json([
                'status'    => 'error',
                'message'   => 'Produk tidak tersedia.'
            ]);
        }

        if ($data->course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        $data->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $id
            ]
        ]);
    }
}
