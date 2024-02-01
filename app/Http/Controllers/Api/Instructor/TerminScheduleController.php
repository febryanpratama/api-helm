<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TransactionTerminRequest;
use App\CourseTermin;
use App\CourseTerminSchedule;
use App\CourseTransactionTerminPayment;

class TerminScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($courseId)
    {
        // Initialize
        $termin         = CourseTermin::where('course_id', $courseId)->first();
        $terminSchedule = null;

        if ($termin) {
            // Initialize
            $terminSchedule = CourseTerminSchedule::with('course','user','transaction')->where('course_termin_id', $termin->id)->orderBy('id', 'DESC')->get();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $terminSchedule
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
    public function store(TransactionTerminRequest $request)
    {
        // Initialize
        $terminS    = CourseTerminSchedule::where(['id' => $request->course_termin_schedule_id, 'user_id' => auth()->user()->id])->first();
        $bank       = explode('|', request('bank'));
        $nowDate    = date('Y-m-d H:i:s');
        $uniqueCode = rand(100, 1000);

        if (!$terminS) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Id ('.$request->course_termin_schedule_id.') tidak ditemukan.'
            ]);
        }

        // Check Exists Unique Code
        $uniqueCodeExists = CourseTransactionTerminPayment::where(['unique_code' => $uniqueCode, 'status' => 0])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode       = rand(100, 1000);
                $uniqueCodeExists = CourseTransactionTerminPayment::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])
                                    ->whereDate('expired_transaction', '>=', $nowDate)
                                    ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        // Check Exists Data
        $payment = CourseTransactionTerminPayment::where('course_termin_schedule_id', $request->course_termin_schedule_id)
                    ->whereDate('expired_transaction', '>=', $nowDate)
                    ->first();

        if ($payment) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan Transaksi ini sebelumnya.'
            ]);
        }

        $newPayment = CourseTransactionTerminPayment::create([
            'course_termin_schedule_id' => $terminS->id,
            'total_payment'             => ($terminS->value + $uniqueCode),
            'total_payment_original'    => $terminS->value,
            'payment_type'              => $request->payment_type,
            'bank_name'                 => $bank[0],
            'no_rek'                    => $bank[1],
            'unique_code'               => $uniqueCode,
            'second_unique_code'        => substr(($terminS->value + $uniqueCode), -3),
            'expired_transaction'       => date('Y-m-d H:i:s', strtotime('+22 hourse'))
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Transaksi berhasil',
            'data'      => $newPayment
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
        $terminSchedule = CourseTerminSchedule::with('course','user','transaction')->where('id', $id)->first();

        if (!$terminSchedule) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $terminSchedule
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
