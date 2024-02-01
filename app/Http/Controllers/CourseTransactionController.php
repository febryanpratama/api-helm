<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Checkout;
use App\CheckoutDetail;
use App\Majors;
use App\MajorsSubject;

class CourseTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role_id == 1) {
            // Initialize
            $nowDate      = date('Y-m-d H:i:s');
            $courses      = Course::where(['user_id' => auth()->user()->id])->pluck('id');

            if (request('tipe') == 'offline') {
                // Initialize
                $transactions = Checkout::where(['user_id' => auth()->user()->id, 'is_offline' => 1])->latest()->paginate(10);

                return view('course-transaction.index-offline', compact('nowDate', 'transactions'));
            }
            
            // Initialize
            $transactions = CheckoutDetail::whereIn('course_id', $courses)->latest()->paginate(10);

            return view('course-transaction.index', compact('nowDate', 'transactions'));
        } else {
            // Initialize
            $nowDate        = date('Y-m-d H:i:s');
            $waitingPayment = Checkout::where(['user_id' => auth()->user()->id, 'status_payment' => 0])->whereDate('expired_transaction', '>=', $nowDate)->count();

            // Search
            if (request('from_date') || request('till_date')) {
                if (request('from_date') && request('till_date')) {
                    // Initialze
                    $transactions = Checkout::with('user')->where('user_id', auth()->user()->id)->whereDate('created_at', '>=', request('from_date'))->whereDate('created_at', '<=', request('till_date'))->latest()->paginate(10);
                } else if (request('from_date')) {
                    // Initialize
                    $transactions = Checkout::with('user')->where('user_id', auth()->user()->id)->whereDate('created_at', '>=', request('from_date'))->latest()->paginate(10);
                } else {
                    // Initialize
                    $transactions = Checkout::with('user')->where('user_id', auth()->user()->id)->whereDate('created_at', '<=', request('till_date'))->latest()->paginate(10);
                }
            } else {
                // Initialze
                $transactions = Checkout::with('user')->where('user_id', auth()->user()->id)->latest()->paginate(10);
            }

            return view('member.course-transaction.index', compact('nowDate', 'transactions', 'waitingPayment'));
        }
    }

    private function _manageData($transactions)
    {
        // Initialize
        $data = [];
   
        // Loop Data
        foreach ($transactions as $val) {
            $row['id']           = $val->id;
            $row['user_name']    = $val->user->name;
            $row['course_name']  = $val->course->name;
            $row['course_slug']  = $val->course->slug;
            $row['company_name'] = $val->course->user->company->Name;

            // Get Session
            $session = Majors::where('IDCourse', $val->course->id)->pluck('id');
            $theory  = MajorsSubject::whereIn('major_id', $session)->count();
            $nowDate = date('Y-m-d H:i:s');

            $row['totalTheory']         = $theory;
            $row['nominal_transaction'] = rupiah($val->price_course);
            $row['active_course_date']  = ($val->course_start != '') ? date('d F Y', strtotime($val->course_start)) : '-';
            $row['end_course_date']     = ($val->expired_course != '0000-00-00 00:00:00') ? date('d F Y', strtotime($val->expired_course)) : '-';
            $row['active_period']       = $val->course_periode.' '.coursePeriode($val->course_periode_type);
            $row['is_exp']              = false;
            $row['exp_date']            = date('d F Y', strtotime($val->expired_transaction));

            if (($nowDate >= $val->expired_transaction) && $val->status_payment == 1) {
                $row['status_payment']  = '<span class="badge badge-success text-white">'.statusTransaction($val->status_payment).'</span>';
            } elseif (($nowDate <= $val->expired_transaction) && $val->status_payment == 0) {
                $row['status_payment']  = '<span class="badge badge-info text-white">'.statusTransaction($val->status_payment).'</span>';
            } elseif (($nowDate <= $val->expired_transaction) && $val->status_payment == 1) {
                $row['status_payment']  = '<span class="badge badge-success text-white">'.statusTransaction($val->status_payment).'</span>';
            } else {
                $row['is_exp']          = true;
                $row['status_payment']  = '<span class="badge badge-danger text-white">'.statusTransaction(2).'</span>';
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('course-transaction.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function destroy($id)
    {
        //
    }

    public function search()
    {
        // Initialize
        $courses = Course::where('name', 'LIKE', '%'.request('q').'%')->where(['is_publish' => '1', 'user_id' => auth()->user()->id])->orderBy('name', 'ASC')->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'items'     => $courses
        ]);
    }
}
