<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cart;
use App\Course;
use DB;

class OfflineCartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $carts    = Cart::with('course')->where(['user_id' => auth()->user()->id, 'is_offline' => 1])->get();
        $qty      = Cart::with('course')->where(['user_id' => auth()->user()->id])->sum('qty');
        $total    = DB::table('cart')
                    ->leftJoin('course', 'course.id', '=', 'cart.course_id')
                    ->select(DB::raw('SUM(course.price_num * cart.qty) as total_payment'))
                    ->get();
        $totals   = (count($total) > 0) ? $total[0]->total_payment : 0;
        $disabled = ($total[0]->total_payment != 0) ? false : true;

        if (count($carts) > 0) {
            $disabled = false;
        }

        return response()->json([
            'status'     => true,
            'message'    => 'Data tersedia',
            'data'       => $carts,
            'qty'        => $qty,
            'totals'     => rupiah($totals),
            'totals_num' => $totals,
            'isDisabled' => $disabled
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
        // Check Exists Data
        $cartExists = Cart::where(['user_id' => auth()->user()->id, 'course_id' => request('courseId')])->first();

        if ($cartExists) {
            $cartExists->update([
                'qty' => (request('qty') + $cartExists->qty)
            ]);
        } else {
            // Initialize
            Cart::create([
                'user_id'    => auth()->user()->id,
                'course_id'  => request('courseId'),
                'is_offline' => 1,
                'qty'        => (request('qty')) ? request('qty') : 1
            ]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil ditambahkan'
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        // Delete Data
        $cart->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Item berhasil dihapus'
        ]);
    }

    public function searchCourse()
    {
        // Initialize
        $data = Course::where('name', 'LIKE', '%'.request('search').'%')->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $data
        ]);
    }
}
