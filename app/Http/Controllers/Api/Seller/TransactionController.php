<?php

namespace App\Http\Controllers\Api\Seller;

use App\Account;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\GlobalNotification;
use App\Transaction;
use App\TransactionComplain;
use App\TransactionDetails;
use App\Wallet;
use App\AgreementLetter;
use App\BeginBalance;
use App\Invoice;
use App\InvoiceAddress;
use App\PendingWalletTransaction;
use App\TransactionJointBank;
use App\CourseTerminSchedule;
use App\Journal;
use Validator;
use Notification;
use App\User;
use DB;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (request('filter')) {
            if (request('filter') != 7) {
                $status = request('filter');
            } else {
                $status = request('filter');
            }
        } else {
            $status = request('filter');
        }

        if ($status != 7) {
            if ($status == 0) {
                // New Query
                $transactions = DB::table('transaction')
                    ->join('invoice', 'invoice.id', '=', 'transaction.invoice_id')
                    ->join('company', 'company.ID', '=', 'transaction.store_id')
                    ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
                    ->select(
                        'transaction.*',
                        'invoice.total_payment',
                        'invoice.status as inv_status',
                        'invoice.category_transaction as inv_category_transaction',
                        'invoice.expired_transaction as inv_expired_transaction',
                        'invoice.user_id as inv_user_id',
                        'invoice.invoice_type as invoice_type',
                        'company.Name as company_name',
                        'transaction_details.course_name',
                        'invoice.is_offline_transaction'
                    )
                    ->orderBy('transaction.id', 'DESC')
                    ->where('invoice.status', 1)
                    ->where('invoice.is_offline_transaction', 0)
                    ->where('transaction.store_id', auth()->user()->company_id)
                    ->where('transaction.status', $status)
                    ->where('transaction_details.course_name', 'LIKE', request('search'))
                    ->groupBy('transaction.id')
                    ->paginate(20);
            } else {
                // New Query
                $transactions = DB::table('transaction')
                    ->join('invoice', 'invoice.id', '=', 'transaction.invoice_id')
                    ->join('company', 'company.ID', '=', 'transaction.store_id')
                    ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
                    ->select(
                        'transaction.*',
                        'invoice.total_payment',
                        'invoice.status as inv_status',
                        'invoice.category_transaction as inv_category_transaction',
                        'invoice.expired_transaction as inv_expired_transaction',
                        'invoice.user_id as inv_user_id',
                        'invoice.invoice_type as invoice_type',
                        'company.Name as company_name',
                        'transaction_details.course_name',
                        'invoice.is_offline_transaction'
                    )
                    ->orderBy('transaction.id', 'DESC')
                    ->where('invoice.is_offline_transaction', 0)
                    ->where('transaction.store_id', auth()->user()->company_id)
                    ->where('transaction.status', $status)
                    ->where('transaction_details.course_name', 'LIKE', request('search'))
                    ->groupBy('transaction.id')
                    ->paginate(20);
            }
        } else {
            // New Query
            $transactions = DB::table('transaction')
                        ->join('invoice', 'invoice.id', '=', 'transaction.invoice_id')
                        ->join('company', 'company.ID', '=', 'transaction.store_id')
                        ->join('transaction_details', 'transaction_details.transaction_id', '=', 'transaction.id')
                        ->select(
                            'transaction.*',
                            'invoice.total_payment',
                            'invoice.status as inv_status',
                            'invoice.category_transaction as inv_category_transaction',
                            'invoice.expired_transaction as inv_expired_transaction',
                            'invoice.user_id as inv_user_id',
                            'invoice.invoice_type as invoice_type',
                            'company.Name as company_name',
                            'transaction_details.course_name',
                            'invoice.is_offline_transaction'
                        )
                        ->orderBy('transaction.id', 'DESC')
                        ->where('invoice.is_offline_transaction', 0)
                        ->where('store_id', auth()->user()->company_id)
                        ->where('invoice.status', 0)
                        ->where('transaction_details.course_name', 'LIKE', request('search'))
                        ->groupBy('transaction.id')
                        ->paginate(20);
        }

        $data = [];

        foreach($transactions as $val) {
            $row['id']                              = $val->id;
            $row['invoice_id']                      = 'INV-'.$val->invoice_id;
            $row['store_id']                        = $val->store_id;
            $row['store_name']                      = $val->company_name;
            $row['total_payment']                   = ($val->total_payment);
            $row['total_payment_rupiah']            = rupiah($val->total_payment);
            $row['category_transacation']           = categoryTransaction($val->inv_category_transaction);
            $row['status_payment']                  = statusPayment($val->inv_status);
            $row['status_transaction']              = statusTransactionV2($val->status);
            $row['reasons_for_refusing']            = $val->reasons_for_refusing;
            $row['status_code']                     = $val->status;
            $row['total_payment_by_invoice']        = ($val->total_payment);
            $row['total_payment_by_invoice_rupiah'] = rupiah($val->total_payment);
            $row['expired_transaction']             = date('d F Y', strtotime($val->inv_expired_transaction));
            $row['payment_details_type']            = categoryTransaction($val->inv_category_transaction);
            $row['invoice_type']                    = invoiceType($val->invoice_type);
            $row['queue']                           = $val->queue;

            // Products
            $transactionDetails = TransactionDetails::where('transaction_id', $val->id)->first();
            $totalItemPurchased = TransactionDetails::where('transaction_id', $val->id)->count();

            $row['transaction_details'] = [
                'item'                  => $transactionDetails,
                'total_items_purchased' => $totalItemPurchased.' barang'
            ];

            // User Details
            $user = User::where('id', $val->inv_user_id)->first();

            $row['user']        = $user;
            $row['created_at']  = $val->created_at;
            $row['updated_at']  = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Initialize
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        // Check Category Invoice
        if ($transaction->invoice->category_transaction != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Hanya untuk kategori Belanja.'
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
        $row['invoice_type']         = invoiceType($transaction->invoice->invoice_type);
        $row['queue']                = $transaction->queue;
        
        // Check Immovable Object True
        $immovableObject = false;

        foreach ($transaction->transactionDetails as $imo) {
            if ($imo->is_immovable_object == 1) {
                $immovableObject = true;
            }
        }

        $row['immovable_object'] = $immovableObject;

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
            $attribute['is_immovable_object']           = $val->is_immovable_object;
            $attribute['immovable_object_received']     = ($val->is_immovable_object == 1) ?
                                                            ($val->transaction->status_immovable_object) ? true : false
                                                          : false;

            // Check Is Termin or No
            $isTermin = CourseTerminSchedule::where('course_transaction_detail_id', $val->id)->count();

            if ($isTermin >= 1) {
                $attribute['is_termin'] = 1;
            } else {
                $attribute['is_termin'] = 0;
            }

            // Initialize
            $agreementLetter = AgreementLetter::where('transaction_details_id', $val->id)->first();

            $attribute['agreement_letter']  = ($agreementLetter) ? $agreementLetter : '-';
            $attribute['create_at']         = $val->created_at;
            $attribute['update_at']         = $val->updated_at;

            // Custom Document Input
            $dataCDI = [];

            if ($val->transactionDetailsCustomDocumentInput) {
                foreach($val->transactionDetailsCustomDocumentInput as $valCDI) {
                    // Initialize
                    $dataCDI = [
                        'id'                     => $valCDI->id,
                        'transaction_details_id' => $valCDI->transaction_details_id,
                        'value'                  => json_decode($valCDI->value, true)
                    ];
                }
            }

            $attribute['custom_document_input'] = $dataCDI;
            $attribute['category_detail_inputs']= ($val->category_detail_inputs) ? $val->category_detail_inputs : null;

            $products[] = $attribute;
        }

        $row['products'] = $products;
        $address         = $transaction->invoice->invoiceAddress;

        // Shipping Information
        $row['shipping_information'] = [
            'expedition'                => ($transaction->expedition != null || $transaction->expedition != '-') ? $transaction->expedition.' - '.$transaction->service : 'Armada',
            'receipt'                   => $transaction->receipt,
            'address'                   => $address->details_address.' <br> '.$address->district.' '.$address->city.' '.$address->province
        ];

        // Payment Details
        $row['payment_details'] = [
            'payment_method'                  => $transaction->invoice->bank_name,
            'total_price'                     => ($transaction->total_payment - $transaction->shipping_cost),
            'total_price_rupiah'              => rupiah($transaction->total_payment - $transaction->shipping_cost),
            'total_items_purchased'           => count($transaction->transactionDetails),
            'total_shipping_cost'             => $transaction->shipping_cost,
            'total_shipping_cost_rupiah'      => rupiah($transaction->shipping_cost),
            'total_transaction'               => ($transaction->total_payment),
            'total_transaction_rupiah'        => rupiah($transaction->total_payment),
            'total_payment_by_invoice'        => ($transaction->total_payment + $transaction->invoice->unique_code),
            'total_payment_by_invoice_rupiah' => rupiah($transaction->total_payment + $transaction->invoice->unique_code),
            'unique_code'                     => ($transaction->invoice->second_unique_code && $transaction->invoice->second_unique_code != 0)
                                                    ? $transaction->invoice->second_unique_code
                                                    : $transaction->invoice->unique_code
        ];

        // User Details
        $user = $transaction->invoice->user;
        
        $row['user'] = $user;
        
        // Transaction Type
        $row['payment_details_type'] = categoryTransaction($transaction->invoice->category_transaction);

        // Initialize
        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function showTermin($id)
    {
        // Initialize
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        // Check Category Invoice
        if ($transaction->invoice->category_transaction != 2) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Hanya untuk kategori Termin.'
            ]);
        }

        // Detail Invoice
        $row['no_invoice']              = 'INV-'.$transaction->invoice_id;
        $row['payment_date']            = $transaction->created_at;
        $row['status_payment']          = statusPayment($transaction->invoice->status);  
        $row['expired_transaction']     = $transaction->invoice->expired_transaction;

        // Payment Details
        $row['payment_details'] = [
            'payment_method'                  => $transaction->invoice->bank_name,
            'total_price'                     => ($transaction->invoice->total_payment),
            'total_price_rupiah'              => rupiah($transaction->invoice->total_payment),
            'total_shipping_cost'             => $transaction->shipping_cost,
            'total_shipping_cost_rupiah'      => rupiah($transaction->shipping_cost),
            'total_payment_by_invoice'        => ($transaction->total_payment + $transaction->shipping_cost + $transaction->invoice->unique_code),
            'total_payment_by_invoice_rupiah' => rupiah($transaction->total_payment + $transaction->shipping_cost + $transaction->invoice->unique_code),
            'unique_code'                     => ($transaction->invoice->second_unique_code && $transaction->invoice->second_unique_code != 0)
                                                    ? $transaction->invoice->second_unique_code
                                                    : $transaction->invoice->unique_code,
            'total_items_purchased'           => count($transaction->transactionDetails)
        ];

        // Termin Schedule
        $row['termin_schedule'] = $transaction->invoice->invoiceTerminSchedule->terminSchedule;

        // === Details Transaction
        // Store Details
        $storeDetails = $transaction->company;
        
        $store = [
            'store_id'    => $storeDetails->ID,
            'store_name'  => $storeDetails->Name,
            'logo'        => $storeDetails->Logo
        ];

        // Items
        $mainTransaction = Transaction::where('id', $transaction->invoice->invoiceTerminSchedule->main_transaction_id)->first();
        $products = [];

        foreach ($mainTransaction->transactionDetails as $val) {
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
            $attribute['category_detail_inputs']        = ($val->category_detail_inputs) ? $val->category_detail_inputs : null;

            // Initialize
            $agreementLetter = AgreementLetter::where('transaction_details_id', $val->id)->first();

            $attribute['agreement_letter']  = ($agreementLetter) ? $agreementLetter : '-';
            $attribute['create_at']         = $val->created_at;
            $attribute['update_at']         = $val->updated_at;

            $products[] = $attribute;
        }

        // Address
        $address = $mainTransaction->invoice->invoiceAddress;

        // Shipping Information
        $shippingInformation = [
            'expedition' => ($mainTransaction->expedition != null || $mainTransaction->expedition != '-')
                            ? $mainTransaction->expedition.' - '.$mainTransaction->service
                            : 'Armada',
            'receipt'    => $mainTransaction->receipt,
            'address'    => $address->details_address.' <br> '.$address->district.' '.$address->city.' '.$address->province
        ];

        // Payment Details
        $paymentDetails = [
            'payment_method'                  => $mainTransaction->invoice->bank_name,
            'total_price'                     => ($mainTransaction->total_payment + $mainTransaction->shipping_cost),
            'total_price_rupiah'              => rupiah($mainTransaction->total_payment + $mainTransaction->shipping_cost),
            'total_items_purchased'           => count($mainTransaction->transactionDetails),
            'total_shipping_cost'             => $mainTransaction->shipping_cost,
            'total_shipping_cost_rupiah'      => rupiah($mainTransaction->shipping_cost),
            'total_payment_by_invoice'        => ($mainTransaction->total_payment + $mainTransaction->shipping_cost + $mainTransaction->invoice->unique_code),
            'total_payment_by_invoice_rupiah' => rupiah($mainTransaction->total_payment + $mainTransaction->shipping_cost + $mainTransaction->invoice->unique_code)
        ];

        $row['main_transaction'] = [
            'store'                 => $store,
            'products'              => $products,
            'shipping_information'  => $shippingInformation,
            'payment_details'       => $paymentDetails
        ];

        // User Details
        $user = $transaction->invoice->user;
        
        $row['user'] = $user;

        // Transaction Type
        $row['payment_details_type'] = categoryTransaction($transaction->invoice->category_transaction);
        
        // Initialize
        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
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
        // Check Validation
        $validator = Validator::make(request()->all(), [
            'status_transaction'  => 'required|in:1,2,4'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request('status_transaction') == 2) {
            $validator = Validator::make(request()->all(), [
                'reasons_for_refusing' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        // Initialize
        $transaction = Transaction::where('id', $id)->first();

        // Check Expired Transaction
        $nowDate = date('Y-m-d H:i:s');

        if ($transaction->invoice->expired_transaction < $nowDate && $transaction->invoice->status == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi sudah kadaluarsa.'
            ]);
        }

        // Check Type
        if ($transaction->invoice->invoice_type == 0 && $transaction->invoice->status == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi belum Dibayar.'
            ]);
        }

        // Check Product Received
        if ($transaction->status == 6) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi sudah diselesaikan buyer.'
            ]);
        }

        if (request('status_transaction') != 2) {
            // Update Transaction
            $transaction->update([
                'status'                => request('status_transaction'),
                'reasons_for_refusing'  => null
            ]);

            if (request('status_transaction') != 4) {
                // Check Store Total in one invoice
                $storeTotal         = Transaction::where('invoice_id', $transaction->invoice_id)->count();
                $totalStoreApprove  = Transaction::where(['invoice_id' => $transaction->invoice_id, 'status' => 1])->count();
                $totalStoreRejected = Transaction::where(['invoice_id' => $transaction->invoice_id, 'status' => 2])->count();
                $totalVerify        = ($storeTotal - $totalStoreRejected);

                if ($totalVerify == $totalStoreApprove) {
                    // Notification
                    $this->approveNotification($transaction);

                    // Update Pending Wallet
                    $pendingWalletTransaction = PendingWalletTransaction::where('invoice_id', $transaction->invoice_id)->first();

                    if ($pendingWalletTransaction) {
                        $pendingWalletTransaction->update(['status' => 1]);
                    }
                }
            }
        } else {
            if ($transaction->is_refund_balance == 0 && $transaction->invoice->status == 1) {
                // Insert to wallet (Refund Balance)
                Wallet::create([
                    'user_id'           => $transaction->invoice->user_id,
                    'balance'           => $transaction->total_payment,
                    'is_verified'       => 1,
                    'balance_type'      => 3,
                    'apps_commission'   => 0,
                    'original_balance'  => $transaction->total_payment,
                    'details'           => 'Pengembalian Dana'
                ]);
            }
            
            $transaction->update([
                'status'                => request('status_transaction'),
                'reasons_for_refusing'  => request('reasons_for_refusing'),
                'is_refund_balance'     => 1
            ]);

            // Update Total Invoice
            $invoice = Invoice::where('id', $transaction->invoice_id)->first();

            if ($invoice) {
                // Check Store Total in one invoice
                $storeTotal         = Transaction::where('invoice_id', $transaction->invoice_id)->count();
                $totalStoreRejected = Transaction::where(['invoice_id' => $transaction->invoice_id, 'status' => 2])->count();

                if ($storeTotal > 1) {
                    // Formula
                    $totalPay = $invoice->total_payment;

                    if ($invoice->total_payment_without_balance) {
                        $totalPayWithoutBalance = ($invoice->total_payment_without_balance - $transaction->total_payment);

                        // Check Pending Wallet Transaction
                        $pendingWalletTransaction = PendingWalletTransaction::where('invoice_id', $invoice->id)->first();

                        if ($pendingWalletTransaction) {
                            $totalPay = ($totalPayWithoutBalance - $pendingWalletTransaction->total);
                        }
                    } else {
                        $totalPay = ($invoice->total_payment - $invoice->second_unique_code) - $transaction->total_payment;
                    }

                    $invoice->update([
                        'total_payment'         => ($totalPay),
                        'second_unique_code'    => substr($totalPay, -3)
                    ]);
                }

                if ($totalStoreRejected == $storeTotal) {
                    // Insert to wallet (Refund Balance Unique Code)
                    Wallet::create([
                        'user_id'           => $transaction->invoice->user_id,
                        'balance'           => $transaction->invoice->unique_code,
                        'is_verified'       => 1,
                        'balance_type'      => 3,
                        'apps_commission'   => 0,
                        'original_balance'  => $transaction->invoice->unique_code,
                        'details'           => 'Pengembalian Dana (Unique Code)'
                    ]);
                }
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $transaction
        ]);
    }

    private function approveNotification($transaction)
    {
        $dateNotif  = date('d-m-Y H:i', strtotime('+22 hourse'));
        $receiverId = '0';
        $title      = 'Konfirmasi Pesanan';
        $code       = '100';
        $data       = [
                        'transaction_id' => $transaction->id
                      ];
        $icon       = '';
        $recipient  = $transaction->invoice->user;

        if ($transaction->invoice->is_service && $transaction->invoice->status == 0) {
            // Initialize
            $message = 'Pesanan anda dengan no Invoice (#INV-'.$transaction->invoice_id.') telah di Konfirmasi Seller, silahkan melakukan pembayaran sebelum '.$dateNotif;
        } else {
            $message = 'Pesanan anda dengan no Invoice (#INV-'.$transaction->invoice_id.') telah di Konfirmasi Seller, pesanan sedang Diproses.';
        }

        Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
    }

    // resi & armada
    public function waybillStore(Transaction $transaction, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'type'  => 'required|in:1,2',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data);
        }

        if ($transaction->service_date) {
            $data = [
                'status'    => 'error',
                'message'   => 'Transaksi Jasa tidak bisa dikirim (hanya barang)',
                'code'      => 400
            ];
            
            return response()->json($data);
        }

        if ($request->type == '1') { // expedition
            
            $validator = Validator::make(request()->all(), [
                'receipt'           => 'required|string',
                'driver_number'     => 'nullable|numeric',
                'number_plat'       => 'nullable|string',
            ]);
    
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
                
                return response()->json($data);
            }
        } else { // fleet
            $validator = Validator::make(request()->all(), [
                'receipt'           => 'nullable|string',
                'driver_number'     => 'required|numeric',
                'number_plat'       => 'required|string',
                'total_product'     => 'required|integer',
                'total_price'       => 'required|integer',
                'status'            => 'required|in:1,2'
            ]);
    
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data);
            }
        }

        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }

        if ($transaction->status != '1') {
            $data = [
                'status'    => 'error',
                'message'   => 'Update pengiriman gagal status transaksi harus dalam Being Processed (Dalam Proses)',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $transaction->update([
            'status'                => 3,
            'receipt'               => $request->receipt,
            'driver_number'         => $request->driver_number,
            'number_plat'           => $request->number_plat,
            'total_product_fleet'   => $request->total_product,
            'total_price_fleet'     => $request->total_price,
            'status_fleet'          => $request->status
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Update pengiriman berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    public function arriveAtDestinationFleet(Transaction $transaction, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'receiver_name'  => 'required|string',
            'receiver_phone' => 'nullable|numeric',
            'receiver_photo' => 'required|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check owner
        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }

        // check status
        if ($transaction->status != '3') {
            $data = [
                'status'    => 'error',
                'message'   => 'Update pengiriman gagal status transaksi harus dalam Sent (dikirim)',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check transaction if expedition
        if ($transaction->receipt) {
            $data = [
                'status'    => 'error',
                'message'   => 'Update pengiriman gagal, transaksi ini dikirim menggunakan ekspedisi (bukan armada toko)',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $path = null;
        if ($request->file('receiver_photo') != '') {
            $path = $request->file('receiver_photo')->store('uploads/transaction/receiver/'.$transaction->id.'/', 'public');
            $path = env('SITE_URL'). '/storage/'. $path;
        }

        $transaction->update([
            'status'   => 4,
            'receiver_name'     => $request->receiver_name,
            'receiver_phone'    => $request->receiver_phone,
            'receiver_photo'    => $path,
            'time_received'     => date('Y-m-d H:i:s')
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Update pengiriman berhasil',
            'code'      => 200
        ];
        return response()->json($data, 200);
    }

    public function approveCancelTransaction(Transaction $transaction, Request $request)
    {
        // check if transaaction pending
        if ($transaction->invoice->status == 0) {
            $data = [
                'status'    => 'error',
                'message'   => 'approve gagal transaksi ini belum dibayar oleh pembeli',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check owner
        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }

        // check if cancel not exists
        if (!$transaction->reason_cancel) {
            $data = [
                'status'    => 'error',
                'message'   => 'approve gagal, pembeli tidak melakukan pembatalan transaksi',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->reason_cancel_reject) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda telah melakukan reject untuk pembatalan transaksi ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $transaction->update([
            'status' => 5,
            'is_refund_balance' => 1
        ]);

        // save journal
        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
        $this->journal($begin_balance, $transaction, $request);

        // Insert Wallet
        Wallet::create([
            'user_id'           => $transaction->invoice->user_id,
            'is_verified'       => 1,
            'balance_type'      => 0,
            'balance'           => $transaction->total_payment,
            'original_balance'  => $transaction->total_payment,
            'details'           => 'Refund for cancel transaction'
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Approve berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    private function journal($begin_balance, $transaction, $request) {
        // Journal method beginbalance = 0 atau 1
        if ($begin_balance) {
            $account_debit_1 = Account::where('CurrType', 'Allowance for Bad Debts')->first();
            $account_credit_1 = Account::where('CurrType', 'Account Receivable')->first();
            $account_debit_2 = null;


            if ($account_debit_1 && $account_credit_1) {
                // init 

                // DEBIT
                $res_acc_debit1 = array();
                $res_acc_debit2 = array();
                
                $debit1 = array();
                $debit2 = array();
                $debit3 = array();
                $debit4 = array();

                $debit_json = array();
                $debit1_json = array();
                $debit2_json = array();

                // CREDIT
                $res_acc_credit1 = array();

                $credit1 = array();
                $credit2 = array();
                $credit3 = array();
                $credit4 = array();

                $credit_json = array();
                $credit1_json = array();

                $res_doc = array();

                // debit 1
                $acc_debit1['id'] = $account_debit_1->ID;
                $acc_debit1['name'] = $account_debit_1->Name;
                $acc_debit1['code'] = $account_debit_1->Code;
                $acc_debit1['group'] = $account_debit_1->group;
                $acc_debit1['type'] = $account_debit_1->CurrType;
                $res_acc_debit1[] = $acc_debit1;

                $debit1['id'] = $account_debit_1->ID;
                $debit1['value'] = $transaction->total_payment;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;


                // multi result bila banyak debit menggunakan merge
                $debit_json = $debit1_json;

                // credit 1
                $acc_credit1['id'] = $account_credit_1->ID;
                $acc_credit1['name'] = $account_credit_1->Name;
                $acc_credit1['code'] = $account_credit_1->Code;
                $acc_credit1['group'] = $account_credit_1->group;
                $acc_credit1['type'] = $account_credit_1->CurrType;

                $res_acc_credit1[] = $acc_credit1;

                $credit1['id'] = $account_credit_1->ID;
                $credit1['value'] = $transaction->total_payment;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => $transaction->store_id,
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Saat Buyer Cancel|' . $transaction->id,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => auth()->user()->id,
                    'AddedByIP'             => $request->ip()
                ]);
            }
        }
    }

    public function rejectCancelTransaction(Transaction $transaction, Request $request)
    {
        // check if transaaction pending
        if ($transaction->invoice->status == 0) {
            $data = [
                'status'    => 'error',
                'message'   => 'Reject gagal transaksi ini belum dibayar oleh pembeli',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check owner
        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }

        // check if cancel not exists
        if (!$transaction->reason_cancel) {
            $data = [
                'status'    => 'error',
                'message'   => 'Reject gagal, pembeli tidak melakukan pembatalan transaksi',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->reason_cancel_reject) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda telah melakukan reject untuk pembatalan transaksi ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $validator = Validator::make(request()->all(), [
            'reason'  => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $transaction->update([
            'reason_cancel_reject' => $request->reason
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Reject berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    // Cancel Transaction
    public function cancelTransaction(Transaction $transaction, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'reason'  => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check if transaaction pending
        if ($transaction->invoice->status == 0) {
            $data = [
                'status'    => 'error',
                'message'   => 'cancel gagal transaksi ini belum dibayar oleh pembeli',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check if cancel not exists
        if ($transaction->reason_cancel) {
            $data = [
                'status'    => 'error',
                'message'   => 'cancel gagal, Anda telah pembatalan transaksi',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->status == 5) {
            $data = [
                'status'    => 'error',
                'message'   => 'cancel gagal, Transaksi sudah dicancel',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $transaction->update([
            'status' => 5,
            'is_refund_balance' => 1
        ]);

        // Insert Wallet
        Wallet::create([
            'user_id'           => $transaction->invoice->user_id,
            'is_verified'       => 1,
            'balance_type'      => 0,
            'balance'           => $transaction->total_payment,
            'original_balance'  => $transaction->total_payment,
            'details'           => 'Refund for cancel transaction'
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'cancel berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
    // Initialize
    $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
    $items = $items instanceof Collection ? $items : Collection::make($items);
    
    return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function approveComplain(Transaction $transaction, Request $request)
    {
        if ($transaction->status != 7) {
            $data = [
                'status'    => 'error',
                'message'   => 'approve komplain transaksi gagal transaksi ini bukan status nya Complain (Ajukan Komplain)',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $validator = Validator::make(request()->all(), [
            'solution'    => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $complain = TransactionComplain::where('transaction_id', $transaction->id)->first();

        if ($complain->is_reject == 1) {
            $data = [
                'status'    => 'error',
                'message'   => 'approve komplain transaksi gagal, Anda sudah melakukan reject untuk komplain ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($complain->is_approve == 1) {
            $data = [
                'status'    => 'error',
                'message'   => 'approve komplain transaksi gagal, Anda sudah melakukan approve untuk komplain ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $complain->update([
            'is_approve' => 1,
            'solution' => $request->solution,
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Approve komplain berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }
    
    public function detailComplain(Transaction $transaction, Request $request)
    {
        if ($transaction->complain) {
            $data = [
                'status'    => 'success',
                'message'   => 'Detail complain',
                'code'      => 200,
                'data'      => $transaction->complain
            ];
    
            return response()->json($data, 200);
        }

        $data = [
            'status'    => 'error',
            'message'   => 'Detail komplain tidak ditemukan',
            'code'      => 404
        ];

        return response()->json($data, 404);
    }

    public function rejectComplain(Transaction $transaction, Request $request)
    {
        if ($transaction->status != 7) {
            $data = [
                'status'    => 'error',
                'message'   => 'Reject komplain transaksi gagal transaksi ini bukan status nya Complain (Ajukan Komplain)',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $validator = Validator::make(request()->all(), [
            'reason_reject'    => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $complain = TransactionComplain::where('transaction_id', $transaction->id)->first();

        if ($complain->is_reject == 1) {
            $data = [
                'status'    => 'error',
                'message'   => 'reject komplain transaksi gagal, Anda sudah melakukan reject untuk komplain ini',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if ($complain->is_approve == 1) {
            $data = [
                'status'    => 'error',
                'message'   => 'reject komplain transaksi gagal, Anda sudah melakukan approve untuk komplain ini',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $complain->update([
            'is_reject' => 1,
            'reason_reject' => $request->reason_reject,
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Reject komplain berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    // EDIT Datetime JASA
    public function editDateService(Transaction $transaction)
    {
        if ($transaction->status != 0) { // check if transaction not waiting approve
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi harus dalam status menunggu konfirmasi (waiting approve).'
            ]);
        }

        if (!$transaction->service_date) { // check if transaction not waiting approve
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi harus Jasa.'
            ]);
        }

        $validator = Validator::make(request()->all(), [
            'date'  => 'required',
            'time'  => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $transaction->update([
            'service_date' => date('Y-m-d H:i:s', strtotime(request()->date . ' ' . request()->time))
        ]);

        $data = [
            'status'    => 'success',
            'message'   => 'Berhasil dirubah',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    public function rejectByProduct($id_transaction)
    {
        $validator = Validator::make(request()->all(), [
            'reasons_for_refusing' => 'required',
            'course_id' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $transaction = Transaction::where('id', $id_transaction)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        $transaction_detail = TransactionDetails::where('transaction_id', $transaction->id)->where('course_id', request()->course_id)->first();

        if ($transaction_detail) {
            $get_all_product = TransactionDetails::where('transaction_id', $transaction->id)->get();
            if (count($get_all_product) > 1) { // lebih dari 1
                // init
                $original_invoice = Invoice::find($transaction->invoice_id);
                $original_invoice_address = InvoiceAddress::where('invoice_id', $transaction->invoice_id)->first();

                // Input new invoice
                $invoice = Invoice::create([
                    'user_id' => $original_invoice->user_id,
                    'total_payment' => ($transaction_detail->price_course * $transaction_detail->qty),
                    'total_payment_original' => ($transaction_detail->price_course * $transaction_detail->qty),
                    'payment_type' => $original_invoice->payment_type,
                    'bank_name' => $original_invoice->bank_name,
                    'no_rek' => $original_invoice->no_rek,
                    'status' => 2, // set expired
                    'is_termin' => $original_invoice->is_termin,
                    'total_payment_termin' => $original_invoice->total_payment_termin,
                    'expired_transaction' => date('Y-m-d H:i'),
                ]);

                $invoice_address = InvoiceAddress::create([
                    'invoice_id' => $original_invoice_address->invoice_id,
                    'address_id' => $original_invoice_address->address_id,
                    'province' => $original_invoice_address->province,
                    'city' => $original_invoice_address->city,
                    'district' => $original_invoice_address->district,
                    'address_type' => $original_invoice_address->address_type,
                    'details_address' => $original_invoice_address->details_address,
                ]);

                // Input new transaction
                $new_transaction = Transaction::create([
                    'store_id' => $transaction->store_id,
                    'invoice_id' => $invoice->id,
                    'total_payment' => $invoice->total_payment,
                    'status' => 3, // REJECTED
                    'reasons_for_refusing' =>  request('reasons_for_refusing'),
                    'receipt' => $transaction->receipt,
                    'expedition' => $transaction->expedition,
                    'service' => $transaction->service,
                    'service_description' => $transaction->service_description,
                    'shipping_cost' => $transaction->shipping_cost,
                    'etd' => $transaction->etd,
                    'driver_number' => $transaction->driver_number,
                    'number_plat' => $transaction->number_plat,
                    'apps_commission' => $transaction->apps_commission,
                    'receiver_name' => $transaction->receiver_name,
                    'receiver_phone' => $transaction->receiver_phone,
                    'receiver_photo' => $transaction->receiver_photo,
                    'category_transaction' => $transaction->category_transaction,
                    'reason_cancel' => $transaction->reason_cancel,
                    'reason_cancel_reject' => $transaction->reason_cancel_reject,
                    'service_date' => $transaction->service_date,
                    'is_refund_balance' => $transaction->is_refund_balance,
                    'time_received' => $transaction->time_received,
                ]);

                $new_transaction_detail = TransactionDetails::create([
                    'transaction_id' => $new_transaction->id,
                    'course_id' => $transaction_detail->course_id,
                    'course_name' => $transaction_detail->course_name,
                    'course_detail' => $transaction_detail->course_detail,
                    'price_course' => $transaction_detail->price_course,
                    'qty' => $transaction_detail->qty,
                    'weight' => $transaction_detail->weight,
                    'discount' => $transaction_detail->discount,
                    'price_course_after_discount' => $transaction_detail->price_course_after_discount,
                    'back_payment_status' => $transaction_detail->back_payment_status,
                    'category_detail_inputs' => $transaction_detail->category_detail_inputs,
                ]);


                // UPDATE TOTAL invoice & transaction
                $original_invoice->update([
                    'total_payment' => ($original_invoice->total_payment - $invoice->total_payment),
                    'total_payment_original' => ($original_invoice->total_payment_original - $invoice->total_payment),
                ]);

                $transaction->update([
                    'total_payment' => ($transaction->total_payment - $new_transaction->total_payment)
                ]);

                // REFUND
                if ($transaction->is_refund_balance == 0 && $transaction->invoice->status == 1) {
                    // Insert to wallet (Refund Balance)
                    Wallet::create([
                        'user_id'           => $transaction->invoice->user_id,
                        'balance'           => $new_transaction->total_payment,
                        'is_verified'       => 1,
                        'balance_type'      => 3,
                        'apps_commission'   => 0,
                        'original_balance'  => $new_transaction->total_payment,
                        'details'           => 'Pengembalian Dana'
                    ]);
                }



                // DELETE TRANSACTION DETAIL
                $transaction_detail->delete();

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Berhasil mengubah data.',
                    'data'      => $transaction
                ]);
                
            } else { // Jika hanya 1 product, REJECT SEPERTI BIASA
                if ($transaction->is_refund_balance == 0 && $transaction->invoice->status == 1) {
                    // Insert to wallet (Refund Balance)
                    Wallet::create([
                        'user_id'           => $transaction->invoice->user_id,
                        'balance'           => ($transaction->total_payment + $transaction->shipping_cost + $transaction->unique_code),
                        'is_verified'       => 1,
                        'balance_type'      => 3,
                        'apps_commission'   => 0,
                        'original_balance'  => ($transaction->total_payment + $transaction->shipping_cost + $transaction->unique_code),
                        'details'           => 'Pengembalian Dana'
                    ]);
                }
                
                $transaction->update([
                    'status'                => 2,
                    'reasons_for_refusing'  => request('reasons_for_refusing'),
                    'is_refund_balance'     => 1
                ]);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Berhasil mengubah data.',
                    'data'      => $transaction
                ]);
            }
        }


        return response()->json([
            'status'    => 'error',
            'message'   => 'Transaksi tidak ditemukan.'
        ]);
    }

    public function updateTransactionQueue($id)
    {
        $validator = Validator::make(request()->all(), [
            'no_queue'  => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data);
        }

        $transaction = Transaction::find($id);

        if (!$transaction) {
            $data = [
                'status'    => 'error',
                'message'   => 'transaksi tidak ditemukan',
                'code'      => 400
            ];

            return response()->json($data);
        }

        $transaction->update(['queue' => request()->no_queue]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $transaction
        ]);
    }
}
