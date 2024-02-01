<?php

namespace App\Http\Controllers\Api;

use App\CourseTerminSchedule;
use App\Http\Controllers\Controller;
use App\Transaction;
use App\TransactionDetails;
use App\AgreementLetter;
use App\Invoice;
use App\InvoiceTerminSchedule;
use Illuminate\Http\Request;
use Validator;
use Notification;
use App\Notifications\GlobalNotification;

class TransactionTerminController extends Controller
{
    public function listTransactionTerminSchedule($id)
    {
        // Initialize
        $transaction_detail = TransactionDetails::where('id', $id)->first();

        if (!$transaction_detail) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan id ('.$id.') tidak ditemukan.'
            ]);
        }

        $schedule = CourseTerminSchedule::where('course_transaction_detail_id', $transaction_detail->id)->get();
        $data     = [];

        foreach ($schedule as $key => $value) {
            // Initialize
            $row['id']                              = $value->id;
            $row['transaction_id']                  = $value->transactionDetails->transaction_id;
            $row['transaction_details_id']          = $value->transactionDetails->id;
            $row['course_id']                       = $value->course_id;
            $row['description']                     = $value->description;
            $row['value']                           = rupiah($value->value);
            $row['value_num']                       = $value->value;
            $row['interest']                        = $value->interest;
            $row['due_date']                        = $value->due_date;
            $row['termin_percentage']               = $value->termin_percentage;
            $row['completion_percentage']           = $value->completion_percentage;
            $row['completion_percentage_detail']    = $value->completion_percentage_detail;
            $row['due_date_description']            = $value->due_date_description;
            $row['duedate_number']                  = $value->duedate_number;
            $row['duedate_name']                    = $value->duedate_name;
            $row['is_verified']                     = $value->is_verified;

            // Check Status Payment
            $statusPayment = 'Belum melakukan pengajuan pembayaran';

            if ($value->description == 'Uang Muka') {
                // Initialize
                $statusPayment = 'Dibayar';
            } else if ($value->invoiceTerminSchedule) {
                // Initialize
                $statusPayment = statusPayment($value->invoiceTerminSchedule->invoice->status);
            }

            $row['status_payment'] = $statusPayment;

            $data[] = $row;
        }

        $data = [
            'status'    => 'success',
            'message'   => 'list termin schedule',
            'code'      => 200,
            'data'      => $data
        ];

        return response()->json($data, 200);
    }

    public function show($id)
    {
        // Initialize
        $data = CourseTerminSchedule::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan id ('.$id.') tidak ditemukan.'
            ]);
        }

        if (!$data->transactionDetails) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice belum tersedia.'
            ]);
        }

        // Initialize
        $transaction = Transaction::where('id', $data->transactionDetails->transaction_id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        // Detail Invoice
        $row['no_invoice']           = 'INV-'.$transaction->invoice_id;
        $row['purchase_date']        = $transaction->created_at;
        $row['status_payment']       = statusPayment($transaction->invoice->status);  
        $row['status_transaction']   = ($transaction->invoice->status != 2) ? statusTransactionV2($transaction->status) : '-';  
        $row['reasons_for_refusing'] = $transaction->reasons_for_refusing;
        $row['status_code']          = $transaction->status;
        $row['expired_transaction']  = $transaction->invoice->expired_transaction;  

        // Store Details
        $store = $transaction->company;
        
        $row['store'] = [
            'store_id'    => $store->ID,
            'store_name'  => $store->Name,
            'logo'        => $store->Logo
        ];

        // Items
        $products = [];

        foreach ($transaction->transactionDetails as $val) {
            // Initialize
            $attribute['id']                            = $val->id;
            $attribute['transaction_id']                = $val->transaction_id;
            $attribute['course_id']                     = $val->course_id;
            $attribute['course_name']                   = $val->course_name;
            $attribute['course_detail']                 = $val->course_detail;
            $attribute['price_course']                  = $val->price_course;
            $attribute['qty']                           = $val->qty;
            $attribute['weight']                        = $val->weight;
            $attribute['discount']                      = $val->discount;
            $attribute['price_course_after_discount']   = $val->price_course_after_discount;
            $attribute['back_payment_status']           = $val->back_payment_status;

            // Initialize
            $agreementLetter = AgreementLetter::where('transaction_details_id', $val->id)->first();

            $attribute['agreement_letter']  = ($agreementLetter) ? $agreementLetter : '-';
            $attribute['create_at']         = $val->created_at;
            $attribute['update_at']         = $val->updated_at;

            $products[] = $attribute;
        }

        $row['products'] = $products;
        $address         = $transaction->invoice->invoiceAddress;

        // Shipping Information
        $row['shipping_information'] = [
            'expedition' => ($transaction->expedition != null || $transaction->expedition != '-') ? $transaction->expedition.' - '.$transaction->service : 'Armada',
            'receipt'    => $transaction->receipt,
            'address'    => $address->details_address.' <br> '.$address->district.' '.$address->city.' '.$address->province
        ];

        // Payment Details
        $row['payment_details'] = [
            'payment_method'                  => $transaction->invoice->bank_name,
            'total_price'                     => ($transaction->total_payment + $transaction->shipping_cost),
            'total_price_rupiah'              => rupiah($transaction->total_payment + $transaction->shipping_cost),
            'total_items_purchased'           => count($transaction->transactionDetails),
            'total_shipping_cost'             => $transaction->shipping_cost,
            'total_shipping_cost_rupiah'      => rupiah($transaction->shipping_cost),
            'total_payment_by_invoice'        => ($transaction->total_payment + $transaction->shipping_cost + $transaction->invoice->unique_code),
            'total_payment_by_invoice_rupiah' => rupiah($transaction->total_payment + $transaction->shipping_cost + $transaction->invoice->unique_code)
        ];

        $row['payment_termin_details'] = [
            'termin' => $data
        ];

        // Initialize
        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function payInstallment()
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'termin_chedule_id' => 'required',
            'payment_type'      => 'required',
            'bank_name'         => 'required',
            'no_rek'            => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $terminScheduleId = request('termin_chedule_id');
        $data             = CourseTerminSchedule::where('id', $terminScheduleId)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan id ('.$terminScheduleId.') tidak ditemukan.'
            ]);
        }

        // Check Invoice
        $invoice = InvoiceTerminSchedule::where([
                        'termin_schedule_id' => $terminScheduleId
                    ])
                    ->first();

        if ($invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan pembayaran untuk termin ini.'
            ]);
        }

        // Initialize
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');

        // Check Exists Unique Code
        $uniqueCodeExists = Invoice::where([
                            'unique_code' => $uniqueCode,
                            'status'      => 0
                        ])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode       = rand(100, 1000);
                $uniqueCodeExists = Invoice::where([
                                        'unique_code' => $uniqueCode,
                                        'status'      => 0
                                    ])
                                    ->whereDate('expired_transaction', '>=', $nowDate)
                                    ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        // Initialize
        $totalPay         = ($data->value + $uniqueCode);
        $totalPayOriginal = ($data->value);
        $paymentType      = request('payment_type');
        $bankName         = request('bank_name');
        $noRek            = request('no_rek');

        $invoice = Invoice::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $totalPay,
            'total_payment_original' => $totalPayOriginal,
            'payment_type'           => $paymentType,
            'total_shipping_cost'    => null,
            'transaction_fees'       => null,
            'bank_name'              => $bankName,
            'no_rek'                 => $noRek,
            'unique_code'            => $uniqueCode,
            'second_unique_code'     => substr($totalPay, -3),
            'status'                 => 0,
            'category_transaction'   => 2,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse'))
        ]);

        if ($invoice) {
            // Initialize
            InvoiceTerminSchedule::create([
                'invoice_id'            => $invoice->id,
                'termin_schedule_id'    => $terminScheduleId,
                'main_transaction_id'   => $data->transactionDetails->transaction_id
            ]);

            $transaction = Transaction::create([
                'store_id'      => $data->transactionDetails->transaction->store_id,
                'invoice_id'    => $invoice->id,
                'total_payment' => $totalPayOriginal
            ]);

            // Notification
            $dateNotif = date('d-m-Y H:i', strtotime('+22 hourse'));
            $message   = 'Anda telah melakukan Transaksi (Termin) dengan Nomor Invoice (#INV-'.$invoice->id.') segera lakukan pembayaran sebelum '.$dateNotif;

            // Initialize
            $recipient      = auth()->user();
            $receiverId     = '0';
            $title          = 'Transaksi Baru';
            $code           = '03';
            $data           = [
                                'transaction_id' => $transaction->id
                            ];
            $icon           = '';

            Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil ditambahkan.',
            'invoice'   => $invoice
        ]);
    }


    // EDIT DUE DATE TERMIN SCHEDULE
    public function editTermin($id)
    {
        // Initialize
        $data = CourseTerminSchedule::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan id ('.$id.') tidak ditemukan.'
            ]);
        }

        if (!$data->transactionDetails) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice belum tersedia.'
            ]);
        }

        // Initialize
        $transaction = Transaction::where('id', $data->transactionDetails->transaction_id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        if ($transaction->status != 0) { // check if transaction not waiting approve
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi harus dalam status menunggu konfirmasi (waiting approve).'
            ]);
        }

        $validator = Validator::make(request()->all(), [
            'date'  => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $data->update([
            'due_date' => date('Y-m-d', strtotime(request()->date))
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan.',
            'code'      => 200,
            'data'      => $data
        ], 200);
    }

    // EDIT VALUE TERMIN SCHEDULE
    public function editTerminSchedule(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'termin_schedule_id'              => 'required|array',
            'termin_schedule_id.*'            => 'required|integer|exists:course_termin_schedule,id',
            'value'                           => 'required|array',
            'value.*'                         => 'required|numeric',
            'completion_percentage_detail'    => 'required|array',
            'completion_percentage_detail.*'  => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }


        // VALIDATION
        foreach ($request->termin_schedule_id as $key => $value) {

            // INIT
            $schedule = CourseTerminSchedule::where('id', $value)->first();
            $transaction = Transaction::where('id', $schedule->transactionDetails->transaction_id)->first();

            $get_total_val = $schedule->checkTotalValue($schedule->course_transaction_detail_id);
        
            if (array_sum($request->value) < $get_total_val) {
                $data = [
                    'status'    => 'error',
                    'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_val) . ' total yang anda input adalah ' . rupiah(array_sum($request->value)),
                    'code'      => 400
                ];
        
                return response()->json($data, 400);
            }
        
            if (array_sum($request->value) > $get_total_val) {
                $data = [
                    'status'    => 'error',
                    'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_val) . ' total yang anda input adalah ' . rupiah(array_sum($request->value)),
                    'code'      => 400
                ];
        
                return response()->json($data, 400);
            }

            if ($transaction->status != 0) { // check if transaction not waiting approve
                return response()->json([
                    'status'    => 'error',
                    'code'      => 400,
                    'message'   => 'Transaksi harus dalam status menunggu konfirmasi (waiting approve).'
                ], 400);
            }
        }


        // UPDATE
        for ($i=0; $i < count($request->termin_schedule_id); $i++) { 
            $schedule = CourseTerminSchedule::where('id', $request->termin_schedule_id[$i])->first();
            $get_total_val = $schedule->checkTotalValue($schedule->course_transaction_detail_id);

            $termin_percentage = null;
            if ($schedule->is_percentage == 0) {
                $termin_percentage = rupiah($request->value[$i]);
            }

            if ($schedule->is_percentage == 1) {
                $termin_percentage = ($request->value[$i]/$get_total_val) * 100  . '%';
            }

            if ($i == 0) { // update total pay (invoice & transaction) DP
                $get_transaction = Transaction::where('id', $schedule->transactionDetails->transaction_id)->first();
                $invoice = Invoice::find($get_transaction->invoice_id);
                $invoice->update([
                    'total_payment'             => ($invoice->total_payment - $schedule->value) + $request->value[$i],
                    'total_payment_original'    => ($invoice->total_payment_original - $schedule->value) + $request->value[$i],
                ]);

                $get_transaction->update([
                    'total_payment'             => ($get_transaction->total_payment - $schedule->value) + $request->value[$i],
                ]);
            }


            $schedule->update([
                'value'                         => $request->value[$i],
                'termin_percentage'             => ($schedule->is_percentage == 1) ? round($termin_percentage) . '%' : $termin_percentage,
                'completion_percentage_detail'  => request('completion_percentage_detail')[$i]
            ]);
        }

        $get_schedule = CourseTerminSchedule::where('course_transaction_detail_id', $schedule->course_transaction_detail_id)->get();
        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan.',
            'code'      => 200,
            'data'      => $get_schedule
        ], 200);
    }
}
