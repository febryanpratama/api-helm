<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\CourseUserPartner;
use App\Checkout;
use App\CheckoutDetail;
use App\CourseTransactionPartner;
use DB;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Course $course)
    {
        // Initialize
        // $uniqueCode = rand(100, 1000);
        $uniqueCode = 0;
        $nowDate    = date('Y-m-d H:i:s');
        $total      = 0;
        $members    = CourseUserPartner::where(['course_id' => $course->id, 'partner_id' => auth()->user()->partner->id])->count();
        $membersId  = CourseUserPartner::where(['course_id' => $course->id, 'partner_id' => auth()->user()->partner->id])->pluck('user_id');
        $cd         = CheckoutDetail::whereIn('user_id', $membersId)->where('course_id', $course->id)->pluck('course_transaction_id');
        $totalPay   = Checkout::whereIn('id', $cd)->sum('total_payment');

        return view('management.checkout.index', compact('course', 'uniqueCode', 'members', 'totalPay'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
            Notes : 
                * Insert to table course_transaction_partner
         */
        
        // Check Exists Data
        $existsData = CourseTransactionPartner::where(['course_id' => request('course-id'), 'partner_id' => auth()->user()->partner->id])->first();

        if ($existsData) {
            return response()->json([
                'status'    => false,
                'message'   => 'Anda sudah menyelesaikan pembayaran.'
            ]);
        }
        
        // Initialize
        $course     = Course::where('id', request('course-id'))->first();
        $userJoined = CourseUserPartner::where(['course_id' => $course->id, 'partner_id' => auth()->user()->partner->id])->pluck('user_id');
        $bank       = explode('|', request('bank'));

        // Initialize
        $checkoutUserByPartner  = DB::table('course_transaction')
                                    ->join('course_transaction_detail', 'course_transaction.id', '=', 'course_transaction_detail.course_transaction_id')
                                    ->select('course_transaction.*', 'course_transaction.user_id as uId')
                                    ->whereIn('course_transaction_detail.user_id', $userJoined)
                                    ->where('course_transaction_detail.course_id', $course->id)
                                    ->get();
        $totalCommission = 0;
        $commission      = 5;
        $totalUserJoined = CourseUserPartner::where(['course_id' => $course->id, 'partner_id' => auth()->user()->partner->id])->count();

        if ($course->commission_type == 1) {
            // Initialize
            $minUserJoined = $course->min_user_joined;
            $maxUserJoined = $course->max_user_joined;

            if ($totalUserJoined <= $minUserJoined) {
                $commission = $course->commission_min_user_joined;
            } else {
                $commission = $course->commission_max_user_joined;
            }
        }

        foreach ($checkoutUserByPartner as $val) {
            // Initialize
            $commissionFormula = $course->price_num - ($val->total_payment - (($commission/100) * $val->total_payment));
            $totalCommission   += $commissionFormula;
        }
        
        // Create Transaction
        $checkout = CourseTransactionPartner::create([
            'partner_id'                    => auth()->user()->partner->id,
            'course_id'                     => $course->id,
            'course_name'                   => $course->name,
            'price_course'                  => $course->price,
            'total_users'                   => $totalUserJoined,
            'total_payment'                 => request('totalPayment'),
            'payment_type'                  => request('paymentType'),
            'bank_name'                     => $bank[0],
            'no_rek'                        => $bank[1],
            'unique_code'                   => substr(request('totalPayment'), -3),
            'status'                        => 0,
            'total_income_instructor'       => (request('totalPayment') - intval($totalCommission)),
            'commission_type'               => $course->commission_type,
            'total_commission_for_system'   => intval($totalCommission)
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Transaksi Berhasil',
            'data'      => $checkout
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(CourseTransactionPartner $coursetransactionpartner)
    {
        // Initialize
        $checkout = $coursetransactionpartner;

        return view('management.checkout.show', compact('checkout'));
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
    public function destroy($id)
    {
        //
    }
}
