<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TopUpRequest;
use App\Checkout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Notifications\GlobalNotification;
use Notification;

class TopUpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $transactions = Checkout::where(['user_id' => auth()->user()->id,'is_topup' => 1])->latest()->get();
        $data         = [];

        // Custom Paginate
        $transactions = $this->paginate($transactions, 20, null, ['path' => $request->fullUrl()]);

        foreach ($transactions as $val) {
            // Initalize
            $nowDate   = date('Y-m-d H:i:s');
            $row['id'] = $val->id;

            if ($val->status_payment == 0 && $nowDate <= $val->expired_transaction) {
                $row['status_transaction'] = statusTransaction($val->status_payment);
            } elseif ($val->status_payment == 1) {
                $row['status_transaction'] = statusTransaction($val->status_payment);
            } else {
                $row['status_transaction'] = statusTransaction(2);
            }

            $row['total_payment']       = $val->total_payment;
            $row['unique_code']         = $val->unique_code;
            $row['second_unique_code']  = $val->second_unique_code;
            $row['bank_name']           = $val->bank_name;
            $row['no_rek']              = $val->no_rek;
            $row['expired_transaction'] = $val->expired_transaction;
            $row['buy_now']             = $val->buy_now;

            if ($val->payment_type == 1) {
                $row['payment_type']    = 'Bank Transfer';
            } elseif ($val->payment_type == 2) {
                $row['payment_type']    = 'E-Money';
            } else {
                $row['payment_type']    = $val->payment_type;
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data topup.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $transactions->currentPage(),
                'from'              => 1,
                'last_page'         => $transactions->lastPage(),
                'next_page_url'     => $transactions->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $transactions->perPage(),
                'prev_page_url'     => $transactions->previousPageUrl(),
                'total'             => $transactions->total()
            ]
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
    public function store(TopUpRequest $request)
    {
        // Initialize
        $nowDate    = date('Y-m-d H:i:s');
        $uniqueCode = rand(100, 1000);
        $bank       = explode('|', request('bank'));
        $nominal    = str_replace('.', '', request('nominal'));

        // Check Exists Unique Code
        $uniqueCodeExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode     = rand(100, 1000);
                $uniqueCodeExists = Checkout::where([
                                                    'unique_code'        => $uniqueCode,
                                                    'status_transaction' => 0
                                                ])
                                                ->whereDate('expired_transaction', '>=', $nowDate)
                                                ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => ($nominal + $uniqueCode),
            'total_payment_original' => $nominal,
            'payment_type'           => request('payment_type'),
            'bank_name'              => $bank[0],
            'no_rek'                 => $bank[1],
            'unique_code'            => $uniqueCode,
            'status_transaction'     => 0,
            'status_payment'         => 0,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'is_topup'               => 1
        ]);

        if ($checkout) {
            // Initialize For Notification
            $sender         = auth()->user();
            $receiverId     = auth()->user()->id;
            $title          = 'Top Up';
            $dateNotif      = date('d-m-Y H:i', strtotime('+22 hourse'));
            $code           = '04';
            $message        = 'Anda Telah melakukan permintaan Top Up sebesar '.rupiah(($nominal + $uniqueCode)).' segera lakukan pembayaran sebelum '.$dateNotif;
            $data           = [
                'transaction_id' => $checkout->id
            ];
            $icon           = '';

            Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $checkout
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
        $transaction = Checkout::where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data TopUp dengan ID ('.$id.')tidak ditemukan.'
            ]);
        }

        if ($transaction->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        $data = [
            'id'                        => $transaction->id,
            'total_payment'             => $transaction->total_payment,
            'total_payment_original'    => $transaction->total_payment_original,
            'payment_type'              => $transaction->payment_type,
            'bank_name'                 => $transaction->bank_name,
            'no_rek'                    => $transaction->no_rek,
            'unique_code'               => $transaction->unique_code,
            'expired_transaction'       => $transaction->expired_transaction,
            // 'status_transaction'        => statusTransaction($transaction->status_transaction),
            'status_payment'            => statusTransaction($transaction->status_payment)
        ];

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data TopUp',
            'data'      => $data
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

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
