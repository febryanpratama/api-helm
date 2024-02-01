<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cart;
use App\Checkout;
use App\CheckoutDetail;

class OfflineTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $getItems = Cart::where(['user_id' => auth()->user()->id, 'is_offline' => '1'])->get();
        $bankName = null;
        $noRek    = null;

        if (request('payment_type') == 1 || request('payment_type') == 2) {
            // Initialize
            $bank     = explode('|', request('bank'));
            $bankName = $bank[0];
            $noRek    = $bank[1];
        }

        // INV Code
        $invCode = '#INV'.date('Y').auth()->user()->company->ID.date('dHI');

        // Check Data Course Package Free
        $totalInv = 0;

        foreach ($getItems as $item) {
            $totalInv += $item->course->price;
        }

        if ($totalInv == 0 && count($getItems) > 0) {
            // Initialize
            $statusTransaction = 1;
            $statusPayment     = 1;
        } else {
            // Initialize
            $statusTransaction = 2;
            $statusPayment     = 2;
        }

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => request('total_payment'),
            'total_payment_original' => request('total_payment'),
            'payment_type'           => request('paymentType'),
            'bank_name'              => $bankName,
            'no_rek'                 => $noRek,
            'unique_code'            => null,
            'status_transaction'     => $statusTransaction,
            'status_payment'         => $statusPayment,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+6 hourse')),
            'buy_now'                => 0,
            'inv_code'               => $invCode,
            'is_offline'             => 1,
            'customer_name'          => request('customer_name'),
            'customer_email'         => request('customer_email'),
            'customer_telepon'       => request('customer_telepon'),
            'publisher_name'         => request('publisher_name'),
            'card_nomor'             => request('card_nomor')
        ]);

        if ($checkout) {
            foreach ($getItems as $val) {
                // Create Detail Transaction
                $checkoutDetail = CheckoutDetail::create([
                    'course_transaction_id' => $checkout->id,
                    'user_id'               => auth()->user()->id,
                    'course_id'             => $val->course_id,
                    'course_name'           => $val->course->name,
                    'price_course'          => $val->course->price,
                    'original_price_course' => $val->course->price_num,
                    'course_periode_type'   => $val->course->periode_type,
                    'course_periode'        => $val->course->periode,
                    'course_type'           => $val->course->course_type,
                    'course_start'          => null,
                    'expired_course'        => null,
                    'apps_commission'       => 0,
                    'qty'                   => $val->qty
                ]);
            }

            // Delete Data in Cart
            Cart::where(['user_id' => auth()->user()->id, 'is_offline' => 1])->delete();
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Transaksi berhasil disimpan',
            'data'      => $checkout
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Checkout $checkout)
    {
        // Validate
        if ($checkout->user_id != auth()->user()->id) {
            return redirect()->back();
        }

        return view('course-transaction.show', compact('checkout'));
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
    public function update(Request $request, Checkout $checkout)
    {
        $checkout->update([
            'status_order'       => request('statusPayment'),
            'status_transaction' => request('statusPayment'),
            'status_payment'     => request('statusPayment'),
            'total_pay'          => str_replace('.', '', request('totalPay')),
            'change'             => str_replace('.', '', request('change'))
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil diperbaharui'
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
        //
    }
}
