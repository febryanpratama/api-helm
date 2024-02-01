<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Invoice;
use App\Transaction;
use App\Notifications\GlobalNotification;
use Notification;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $invoice = Invoice::where(['user_id' => auth()->user()->id, 'category_transaction' => 1])->latest()->get();
        $data         = [];

        // Custom Paginate
        $invoice = $this->paginate($invoice, 20, null, ['path' => $request->fullUrl()]);

        foreach ($invoice as $val) {
            // Initalize
            $nowDate                        = date('Y-m-d H:i:s');
            $row['id']                      = $val->id;
            $row['status']                  = statusPayment($val->status);
            $row['total_payment']           = $val->total_payment;
            $row['total_payment_original']  = $val->total_payment_original;
            $row['unique_code']             = $val->unique_code;
            $row['second_unique_code']      = $val->second_unique_code;
            $row['bank_name']               = $val->bank_name;
            $row['no_rek']                  = $val->no_rek;
            $row['expired_transaction']     = $val->expired_transaction;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Top Up.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $invoice->currentPage(),
                'from'              => 1,
                'last_page'         => $invoice->lastPage(),
                'next_page_url'     => $invoice->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $invoice->perPage(),
                'prev_page_url'     => $invoice->previousPageUrl(),
                'total'             => $invoice->total()
            ]
        ]);
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
        $nowDate    = date('Y-m-d H:i:s');
        $uniqueCode = rand(100, 1000);
        $bank       = explode('|', request('bank'));
        $nominal    = str_replace('.', '', request('nominal'));

        // Check Exists Unique Code
        $uniqueCodeExists = Invoice::where(['unique_code' => $uniqueCode, 'status' => 0])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode     = rand(100, 1000);
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
        $totals = ($nominal + $uniqueCode);

        // Create Transaction
        $invoice = Invoice::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $totals,
            'total_payment_original' => $nominal,
            'payment_type'           => request('payment_type'),
            'bank_name'              => $bank[0],
            'no_rek'                 => $bank[1],
            'unique_code'            => $uniqueCode,
            'second_unique_code'     => substr($totals, -3),
            'status'                 => 0,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'category_transaction'   => 1
        ]);

        if ($invoice) {
            // Create Transaction
            $transaction = Transaction::create([
                'store_id'      => null,
                'invoice_id'    => $invoice->id,
                'total_payment' => $totals
            ]);

            // Initialize For Notification
            $sender         = auth()->user();
            $receiverId     = auth()->user()->id;
            $title          = 'Top Up';
            $dateNotif      = date('d-m-Y H:i', strtotime('+22 hourse'));
            $code           = '04';
            $message        = 'Anda Telah melakukan permintaan Top Up sebesar '.rupiah(($nominal + $uniqueCode)).' segera lakukan pembayaran sebelum '.$dateNotif;
            $data           = [
                'transaction_id' => $transaction->id
            ];
            $icon           = '';

            Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $invoice
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
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data TopUp dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($invoice->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        $data = [
            'id'                        => $invoice->id,
            'total_payment'             => $invoice->total_payment,
            'total_payment_original'    => $invoice->total_payment_original,
            'payment_type'              => $invoice->payment_type,
            'bank_name'                 => $invoice->bank_name,
            'no_rek'                    => $invoice->no_rek,
            'unique_code'               => $invoice->unique_code,
            'expired_transaction'       => $invoice->expired_transaction,
            'status'                    => statusPayment($invoice->status)
        ];

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Top Up',
            'data'      => $data
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
