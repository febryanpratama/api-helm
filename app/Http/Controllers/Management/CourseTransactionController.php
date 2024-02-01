<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Partner;
use App\CoursePartner;
use App\Course;
use App\CourseUserPartner;
use App\CheckoutDetail;
use App\Checkout;
use App\CourseTransactionPartner;
use DB;

class CourseTransactionController extends Controller
{
    public function index()
    {
        // Initialize
        $partner        = Partner::where('user_id', auth()->user()->id)->first();
        $coursePartner  = CoursePartner::where('partner_id', $partner->id)->pluck('course_id');
        $courses        = Course::whereIn('id', $coursePartner)->latest()->get();

        foreach ($courses as $val) {
            $row['id']              = $val->id;
            $row['name']            = $val->name;
            $row['slug']            = $val->slug;
            $row['price']           = $val->price;
            $row['instructor']      = $val->user->name;
            $row['company']         = $val->user->company->Name;
            $row['partner_id']      = auth()->user()->partner->id;
            $row['members']         = CourseUserPartner::where(['partner_id' => auth()->user()->partner->id, 'course_id' => $val->id])->count();
            $row['periode']         = $val->periode;
            $row['periode_type']    = $val->periode_type;

            // Initialize
            $users                  = CourseUserPartner::where(['partner_id' => $partner->id, 'course_id' => $val->id])->pluck('user_id');
            $checkoutDt             = CheckoutDetail::whereIn('user_id', $users)->where('course_id', $val->id)->pluck('course_transaction_id');
            $checkout               = Checkout::whereIn('id', $checkoutDt)->sum('total_payment');
            $row['totalInvoice']    = $checkout;

            $data[] = $row;
        }
        
        $courses = $data;

        return view('management.transaction.index', compact('courses'));
    }

    public function show(Course $course, Partner $partner)
    {
        // Initialize
        $courseUP   = CourseUserPartner::where(['course_id' => $course->id, 'partner_id' => $partner->id])->pluck('user_id');
        $checkout   = DB::table('course_transaction')
                        ->leftJoin('course_transaction_detail', 'course_transaction_detail.course_transaction_id', '=', 'course_transaction.id')
                        ->leftJoin('users', 'users.id', '=', 'course_transaction.user_id')
                        ->select('course_transaction.*',
                                'course_transaction_detail.course_id',
                                'course_transaction_detail.price_course',
                                'users.name as username',
                                'users.email as usermail')
                        ->where('course_transaction_detail.course_id', $course->id)
                        ->whereIn('course_transaction_detail.user_id', $courseUP)
                        ->orderBy('username', 'ASC')
                        ->get();

        $paidStatus = CourseTransactionPartner::where(['course_id' => $course->id, 'partner_id' => $partner->id])->first();

        return view('management.transaction.show', compact('checkout', 'course', 'paidStatus'));
    }
}
