<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cart;
use App\Course;
use App\UserCourse;
use App\HintWidget;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $carts       = Cart::where('user_id', auth()->user()->id)->latest()->get();
        $checkoutBtn = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'checkout-btn'])->first();
        $total       = 0;

        foreach ($carts as $val) {
            if ($val->course->course_type != 2) {
                $total += $val->course->price_num;
            }
        }

        // Initialize
        $total = rupiah($total);

        return view('member.cart.index', compact('carts', 'total', 'checkoutBtn'));
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
        // Initialize
        // $nowDate    = date('Y-m-d H:i:s');
        // $userCourse = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => request('courseId')])->whereDate('course_expired', '>=', $nowDate)->first();

        // Check Exists Course in My Course
        // if ($userCourse) {
        //     return response()->json([
        //         'status'    => false,
        //         'message'   => 'Kamu sudah memiliki Paket Kursus ini'
        //     ]);

        //     die;
        // }

        // if (!$cartExists) {
        //     Cart::create([
        //         'user_id'   => auth()->user()->id,
        //         'course_id' => request('courseId')
        //     ]);

        //     // Initialize
        //     $course     = Course::where('id', request('courseId'))->first();
        //     $totalCart  = Cart::where('user_id', auth()->user()->id)->count();

        //     return response()->json([
        //         'status'        => true,
        //         'message'       => 'Ditambahkan ke keranjang',
        //         'data'          => $course,
        //         'total_cart'    => $totalCart
        //     ]);

        //     die;
        // }
        
        // Check Cart Exists
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
                // 'is_offline' => 1,
                'qty'        => (request('qty')) ? request('qty') : 1
            ]);
        }

        // Initialize
        $totalCart = Cart::where('user_id', auth()->user()->id)->count();
        
        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil ditambahkan',
            'data'      =>  [
                'total_cart' => $totalCart
            ]
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
        $cart->delete();

        // Initialize
        $carts = Cart::where('user_id', auth()->user()->id)->latest()->get();
        $total = 0;

        foreach ($carts as $val) {
            $total += $val->course->price_num;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus',
            'total'     => rupiah($total)
        ]);
    }
}
