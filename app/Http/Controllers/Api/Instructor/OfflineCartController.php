<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\OfflineCartRequest;
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
        $inventoryPurchase = 0;

        if (request('is_inventory_purchases')) {
            // Initialize
            $inventoryPurchase = request('is_inventory_purchases');
        }

        $carts  = Cart::with('course')->where(['user_id' => auth()->user()->id, 'is_inventory_purchases' => $inventoryPurchase])->get();
        $qty    = Cart::with('course')->where(['user_id' => auth()->user()->id])->sum('qty');
        $total  = DB::table('cart')
                    ->leftJoin('course', 'course.id', '=', 'cart.course_id')
                    ->select(DB::raw('SUM(course.price_num * cart.qty) as total_payment'))
                    ->where('cart.user_id', auth()->user()->id)
                    ->get();
        $totals = (count($total) > 0) ? $total[0]->total_payment : 0;

        return response()->json([
            'status'     => 'success',
            'message'    => 'Berhasil mendapatkan data Keranjang.',
            'data'       => $carts,
            'qty'        => $qty,
            'totals'     => rupiah($totals),
            'totals_num' => $totals
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
    public function store(OfflineCartRequest $request)
    {
        // Check Exists Data
        $cart = Cart::where(['user_id' => auth()->user()->id, 'course_id' => request('course_id')])->first();

        if ($cart) {
            $cart->update([
                'qty'                       => (request('qty')),
                'store_id'                  => auth()->user()->company_id,
                'is_inventory_purchases'    => (request('is_inventory_purchases')) ? request('is_inventory_purchases') : 0
            ]);
        } else {
            // Initialize
            $cart = Cart::create([
                'user_id'                   => auth()->user()->id,
                'course_id'                 => request('course_id'),
                'is_offline'                => 1,
                'qty'                       => (request('qty')) ? request('qty') : 1,
                'store_id'                  => auth()->user()->company_id,
                'is_inventory_purchases'    => (request('is_inventory_purchases')) ? request('is_inventory_purchases') : 0
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Keranjang.',
            'data'      => $cart
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
        $cart = Cart::where(['id' => $id, 'user_id' => auth()->user()->id])->first();

        // Delete Data
        $cart->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Item berhasil dihapus'
        ]);
    }

    public function searchCourse()
    {
        // Initialize
        $data = Course::where('name', 'LIKE', '%'.request('search').'%')->where(['is_publish' => '1', 'user_id' => auth()->user()->id])->latest()->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $data
        ]);
    }
}
