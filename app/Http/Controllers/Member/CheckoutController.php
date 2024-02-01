<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\Checkout;
use App\CheckoutDetail;
use App\UserCourse;
use App\TheoryLock;
use App\Majors;
use App\MajorsSubject;
use App\Cart;

class CheckoutController extends Controller
{
    public function index()
    {
        // Initialize
        $carts      = Cart::where('user_id', auth()->user()->id)->latest()->get();
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');
        $total      = 0;

        // Check Exists Unique Code
        $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

        if ($checkoutExists) {
            for ($i= 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode = rand(100, 1000);
                $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

                if (!$checkoutExists) {
                    break;
                }
            }
        }

        foreach ($carts as $val) {
            if ($val->course->course_type != 2) {
                $total += $val->course->price_num;
            }
        }

        // Free Course
        if ($total == 0) {
            // Initialize
            $uniqueCode = 0;

            return view('member.checkout.index-free-course', compact('carts', 'total', 'uniqueCode'));
        }
        
        return view('member.checkout.index', compact('carts', 'uniqueCode', 'total'));
    }

    public function store()
    {
        return response()->json([
            'status'    => false,
            'message'   => 'Sistem dalam pengembangan, silahkan gunakan Aplikasi untuk melakukan Checkout.'
        ]);

        // Checkout Buy Now or Single Order
        if (request('course-id') && request('buy-now')) {
            // Initialize
            $course        = Course::where('id', request('course-id'))->first();
            $bank          = explode('|', request('bank'));
            $uniqueCode    = ($course->course_type == 2) ? 0 : request('uniqueCode');

            // Create Transaction
            $checkout = Checkout::create([
                'user_id'                => auth()->user()->id,
                'total_payment'          => ($course->price_num + $uniqueCode),
                'total_payment_original' => $course->price_num,
                'payment_type'           => request('paymentType'),
                'bank_name'              => $bank[0],
                'no_rek'                 => $bank[1],
                'unique_code'            => $uniqueCode,
                'status_transaction'     => ($course->course_type == 2) ? 1 : 0,
                'status_payment'         => ($course->course_type == 2) ? 1 : 0,
                'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                'buy_now'                => 1
            ]);

            if ($checkout) {
                // Initialize
                $expiredCourse = expiredDate($course->periode_type, $course->periode);
                
                // Create Detail Transaction
                $checkoutDetail = CheckoutDetail::create([
                    'course_transaction_id' => $checkout->id,
                    'user_id'               => auth()->user()->id,
                    'course_id'             => $course->id,
                    'course_name'           => $course->name,
                    'price_course'          => $course->price,
                    'original_price_course' => $course->price_num,
                    'course_periode_type'   => $course->periode_type,
                    'course_periode'        => $course->periode,
                    'course_type'           => $course->course_type,
                    'course_start'          => ($course->course_type == 2) ? date('Y-m-d H:i:s') : null,
                    'expired_course'        => ($course->course_type == 2) ? $expiredCourse : '',
                    'apps_commission'       => ($course->course_type == 2) ? 0 : 5
                ]);

                // Insert To More Table
                if ($checkoutDetail && $course->course_type == 2) {
                    // Initialize
                    $major         = Majors::where('IDCourse', $checkoutDetail->course_id)->take(1)->get();
                    $majorSubject  = MajorsSubject::where('major_id', $major[0]['ID'])->take(1)->get();

                    // User Course
                    UserCourse::create([
                        'user_id'        => auth()->user()->id,
                        'course_id'      => $checkoutDetail->course_id,
                        'course_start'   => date('Y-m-d H:i:s'),
                        'course_expired' => $expiredCourse
                    ]);

                    // Insert Theory Lock
                    TheoryLock::create([
                        'user_id'    => auth()->user()->id,
                        'course_id'  => $checkoutDetail->course_id,
                        'major_id'   => $major[0]['ID'],
                        'subject_id' => $majorSubject[0]['subject_id']
                    ]);
                }

                // Check In Cart
                $inCart = Cart::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();

                if ($inCart) {
                    $inCart->delete();
                }
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Transaksi Berhasil',
                'data'      => $checkout
            ]);

            die;
        }

        // Initialize
        $carts      = Cart::where('user_id', auth()->user()->id)->latest()->get();
        $bank       = explode('|', request('bank'));
        $uniqueCode = request('uniqueCode');
        $total      = 0;

        foreach ($carts as $val) {
            if ($val->course->course_type != 2) {
                $total += $val->course->price_num;
            }
        }

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                   => auth()->user()->id,
            'total_payment'             => ($total + $uniqueCode),
            'total_payment_original'    => $total,
            'payment_type'              => request('paymentType'),
            'bank_name'                 => $bank[0],
            'no_rek'                    => $bank[1],
            'unique_code'               => $uniqueCode,
            'status_transaction'        => 0,
            'status_payment'            => 0,
            'expired_transaction'       => date('Y-m-d H:i:s', strtotime('+22 hourse'))
        ]);

        if ($checkout) {
            foreach ($carts as $val) {
                // Initialize
                $expiredCourse = expiredDate($val->course->periode_type, $val->course->periode);

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
                    'course_start'          => ($val->course->course_type == 2) ? date('Y-m-d H:i:s') : null,
                    'expired_course'        => ($val->course->course_type == 2) ? $expiredCourse : '',
                    'apps_commission'       => ($val->course->course_type == 2) ? 0 : 5
                ]);

                // Insert To More Table
                if ($checkoutDetail && $val->course->course_type == 2) {
                    // Initialize
                    $major         = Majors::where('IDCourse', $val->course_id)->take(1)->get();
                    $majorSubject  = MajorsSubject::where('major_id', $major[0]['ID'])->take(1)->get();

                    // User Course
                    UserCourse::create([
                        'user_id'        => auth()->user()->id,
                        'course_id'      => $val->course_id,
                        'course_start'   => date('Y-m-d H:i:s'),
                        'course_expired' => $expiredCourse
                    ]);

                    // Insert Theory Lock
                    TheoryLock::create([
                        'user_id'    => auth()->user()->id,
                        'course_id'  => $val->course_id,
                        'major_id'   => $major[0]['ID'],
                        'subject_id' => $majorSubject[0]['subject_id']
                    ]);
                }
            }

            // Delete Data in Cart
            Cart::where('user_id', auth()->user()->id)->delete();
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Transaksi Berhasil',
            'data'      => $checkout
        ]);
    }

    public function buyNow($slug)
    {
        // Initialize
        $course     = Course::where('slug', $slug)->firstOrFail();
        $purchased  = CheckoutDetail::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');

        if ($purchased) {
            $lockBtn = Checkout::where(['user_id' => auth()->user()->id, 'id' => $purchased->course_transaction_id, 'status_transaction' => 0])->first();
        } else {
            $lockBtn = false;
        }

        // Check Exists Unique Code
        $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

        if ($checkoutExists) {
            for ($i= 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode = rand(100, 1000);
                $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

                if (!$checkoutExists) {
                    break;
                }
            }
        }

        if ($course->course_type == 2) {
            $uniqueCode = 0;

            // return view('member.checkout.buy-now.index-free-course', compact('course', 'lockBtn', 'purchased', 'uniqueCode', 'nowDate'));
        }

        return view('member.checkout.buy-now.index', compact('course', 'lockBtn', 'purchased', 'uniqueCode', 'nowDate'));
    }

    public function show(Checkout $checkout)
    {
        // Initialize
        $courseActive   = Course::where(['is_publish' => 1, 'course_type' => 2])->count();
        $checkoutDetail = CheckoutDetail::where('course_transaction_id', $checkout->id)->get();

        return view('member.checkout.show', compact('checkout', 'courseActive', 'checkoutDetail'));
    }
}
