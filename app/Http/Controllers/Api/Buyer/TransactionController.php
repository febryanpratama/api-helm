<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Company;
use App\Http\Controllers\Controller;
use App\Notifications\GlobalNotification;
use App\Transaction;
use App\User;
use Illuminate\Http\Request;
use App\Invoice;
use App\TransactionComplain;
use App\TransactionDetails;
use App\Wallet;
use App\TransactionJointBank;
use App\TransactionAdminCommission;
use App\CourseTerminSchedule;
use App\AgreementLetter;
use DB;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Validator;
use Notification;

class TransactionController extends Controller
{
    public function indexV2(Request $request)
    {
        // Check Tab Request
        $tab = 7;

        if (request('filter') || request('filter') == '0') {
            if (request('filter') != 7) {
                $tab = request('filter');
            } else {
                $tab = 7;
            }
        }

        if ($tab == 7) {
            $data = $this->_indexNotYetPaid($request);
        } else {
            $data = $this->_index($tab, $request);
        }

        return $data;
    }

    private function _indexNotYetPaid($request)
    {
        // Initialize
        $invoiceId = [];

        // Check Filter and Search
        if (request('search') != null) {
            // Initialize
            $data = DB::table('transaction')
                    ->leftJoin('transaction_details', 'transaction.id', '=', 'transaction_details.transaction_id')
                    ->select('transaction.id', 'transaction.invoice_id', 'transaction.status', 'transaction_details.course_name')
                    ->where('transaction.status', 0)
                    ->where('transaction_details.course_name', 'LIKE', '%'.request('search').'%')
                    ->get();

            foreach($data as $val) {
                array_push($invoiceId, $val->invoice_id);
            }
        }

        if (count($invoiceId) > 0) {
            // Initialize
            $invoice = Invoice::where(['user_id' => auth()->user()->id])
                        ->whereIn('id', $invoiceId)
                        ->where('category_transaction', '!=', 1)
                        ->where('transaction.status', 0)
                        ->orderBy('id', 'DESC')
                        ->get();
        } else {
            // Initialize
            $invoice = Invoice::where(['user_id' => auth()->user()->id])->where('status', 0)->orderBy('id', 'DESC')->get();
        }

        $listData = $this->paginate($invoice, 20, null, ['path' => $request->fullUrl()]);
        $data     = [];

        foreach($listData as $val) {
            // Check Status Invoice
            if ($val->is_service) {
                // Check Is Verified from seller
                $isVerified = 0;

                foreach($val->transaction as $transactionVal) {
                    if ($transactionVal->status != 0) {
                        $isVerified = 1;
                    }
                }

                if ($isVerified) {
                    $statusPayment = statusPayment($val->status);
                    $totalPay      = $val->total_payment;
                    $totalPayRp    = rupiah($val->total_payment);
                } else {
                    $statusPayment = '-';
                    $totalPay      = '-';
                    $totalPayRp    = '-';
                }
            } else {
                $statusPayment = statusPayment($val->status);
                $totalPay      = $val->total_payment;
                $totalPayRp    = rupiah($val->total_payment);
            }

            // Initialize
            $row['id']                              = $val->id;
            $row['invoice_id']                      = 'INV-'.$val->id;
            $row['status_payment']                  = $statusPayment;  
            $row['total_payment_by_invoice']        = $totalPay;
            $row['total_payment_by_invoice_rupiah'] = $totalPayRp;
            $row['expired_transaction']             = date('d F Y', strtotime($val->expired_transaction));  
            $row['payment_details_type']            = categoryTransaction($val->category_transaction);
            $row['invoice_type']                    = invoiceType($val->invoice_type);
            $row['queue']                           = $val->queue;
            $transactions                           = [];

            foreach($val->transaction as $transactionVal) {
                // Check Status Transaction
                if ($val->is_service) {
                    $statusTransaction = statusTransactionV2($transactionVal->status, $val->is_service);
                } else {
                    if ($val->status != 0) {
                        $statusTransaction = statusTransactionV2($transactionVal->status, $val->is_service);
                    } else {
                        $statusTransaction = '-';
                    }
                }

                if ($transactionVal->invoice->category_transaction != 0) {
                    $statusTransaction = '-';
                }

                $transaction['transaction_id']          = $transactionVal->id;
                $transaction['store_id']                = ($transactionVal->invoice->category_transaction == 0) ? $transactionVal->store_id : '-';
                $transaction['store_name']              = ($transactionVal->invoice->category_transaction == 0) ? $transactionVal->company->Name : '-';
                $transaction['total_payment']           = $transactionVal->total_payment;
                // $transaction['total_payment_rupiah']    = rupiah($transactionVal->invoice->total_payment);
                $transaction['total_payment_rupiah']    = rupiah($transactionVal->total_payment);
                $transaction['category_transaction']    = categoryTransaction($transactionVal->invoice->category_transaction);
                $transaction['status_transaction']      = $statusTransaction;
                $transaction['reasons_for_refusing']    = $transactionVal->reasons_for_refusing;
                $transaction['status_code']             = $transactionVal->status;
                $transaction['transaction_details']     = $transactionVal->transactionDetails;
                $transaction['total_items_purchased']   = count($transactionVal->transactionDetails).' barang';

                $transactions[] = $transaction;
            }

            $row['transactions'] = $transactions;
            $row['created_at']   = $val->created_at;
            $row['updated_at']   = $val->updated_at;
            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $listData->currentPage(),
                'from'              => 1,
                'last_page'         => $listData->lastPage(),
                'next_page_url'     => $listData->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $listData->perPage(),
                'prev_page_url'     => $listData->previousPageUrl(),
                'total'             => $listData->total()
            ]
        ]);
    }

    private function _index($tab, $request)
    {
        if ($tab <= 5) {
            // Get Data where status from tab
            $transactions = DB::table('transaction')
                            ->join('invoice', 'transaction.invoice_id', '=', 'invoice.id')
                            ->select('transaction.*',
                                'invoice.user_id',
                                'invoice.status as status_inv',
                                'invoice.total_payment as total_payment_inv',
                                'invoice.expired_transaction as expired_transaction_env',
                                'invoice.category_transaction as category_transaction_inv',
                                'invoice.invoice_type',
                            )
                            ->where('invoice.user_id', auth()->user()->id)
                            ->where('transaction.status', $tab)
                            ->orderBy('transaction.invoice_id', 'DESC')
                            ->paginate(20);

            $data = [];

            foreach($transactions as $val) {
                // Initialize
                $row['id']                              = $val->invoice_id;
                $row['invoice_id']                      = 'INV-'.$val->invoice_id;
                $row['status_payment']                  = statusPayment($val->status_inv); 
                $row['total_payment_by_invoice']        = $val->total_payment_inv;
                $row['total_payment_by_invoice_rupiah'] = rupiah($val->total_payment_inv);
                $row['expired_transaction']             = date('d F Y', strtotime($val->expired_transaction_env));  
                $row['payment_details_type']            = categoryTransaction($val->category_transaction_inv);
                $row['invoice_type']                    = invoiceType($val->invoice_type);

                // Initialize
                $storeDetails       = Company::where('ID', $val->store_id)->first();
                $transactionDetails = TransactionDetails::where('transaction_id', $val->id)->get();

                $row['transactions']                    = [
                                                                0 => [
                                                                    'transaction_id'        => $val->id,
                                                                    'store_id'              => ($val->category_transaction_inv == 0) ? $val->store_id : '-',
                                                                    'store_name'            => ($val->category_transaction_inv == 0) ? $storeDetails->Name : '-',
                                                                    'total_payment'         => $val->total_payment,
                                                                    'total_payment_rupiah'  => rupiah($val->total_payment),
                                                                    'category_transaction'  => categoryTransaction($val->category_transaction_inv),
                                                                    'status_transaction'    => statusTransactionV2($val->status),
                                                                    'reasons_for_refusing'  => $val->reasons_for_refusing,
                                                                    'status_code'           => $val->status,
                                                                    'transaction_details'   => $transactionDetails,
                                                                    'total_items_purchased' => count($transactionDetails).' barang',
                                                                    'created_at'            => $val->created_at,
                                                                    'updated_at'            => $val->updated_at
                                                                ]
                                                        ];

                
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
        } else {
            $data = response()->json([
                'status'    => 'success',
                'message'   => 'Tidak ada data.'
            ]);
        }

        return $data;
    }

    private function _example($value='')
    {
        if ($tab != 6) {
            // Initialize
            $invoice = Invoice::where(['user_id' => auth()->user()->id])->pluck('id');
        } else {
            // Initialize
            $invoice = Invoice::where(['user_id' => auth()->user()->id])->where('status', 1)->pluck('id');
        }

        if (request('search')) {
            // Initialize
            $transactionsId = Transaction::whereIn('invoice_id', $invoice)->pluck('id');
            $transactionsD  = TransactionDetails::where('course_name', 'LIKE', '%'.request('search').'%')
                                ->whereIn('transaction_id', $transactionsId)
                                ->latest()
                                ->pluck('transaction_id');
            $transactions = Transaction::whereIn('invoice_id', $invoice)->whereIn('id', $transactionsD)->where('status', $tab)->latest()->get();
        } else {
            // Initialize
            $transactions = Transaction::whereIn('invoice_id', $invoice)->where('status', $tab)->latest()->get();
        }

        // Initialize
        // 0 = Waiting Approve, 1 = Being Processed, 2 = Rejected, 3 = Sent, 4 = Recived, 5 = Cancel, 6 = Selesai
        $listData = $this->paginate($transactions, 20, null, ['path' => $request->fullUrl()]);
        $data     = [];

        foreach($listData as $val) {
            // Initialize
            $row['id']                              = $val->invoice->id;
            $row['invoice_id']                      = 'INV-'.$val->invoice->id;
            $row['status_payment']                  = statusPayment($val->invoice->status); 
            $row['total_payment_by_invoice']        = $val->invoice->total_payment;
            $row['total_payment_by_invoice_rupiah'] = rupiah($val->invoice->total_payment);
            $row['expired_transaction']             = date('d F Y', strtotime($val->invoice->expired_transaction));  
            $row['payment_details_type']            = categoryTransaction($val->invoice->category_transaction);;  
            $row['transactions']                    = [
                                                            0 => [
                                                                'transaction_id'        => $val->id,
                                                                'store_id'              => ($val->invoice->category_transaction == 0) ? $val->store_id : '-',
                                                                'store_name'            => ($val->invoice->category_transaction == 0) ? $val->company->Name : '-',
                                                                'total_payment'         => $val->total_payment,
                                                                'total_payment_rupiah'  => rupiah($val->total_payment),
                                                                'category_transaction'  => categoryTransaction($val->invoice->category_transaction),
                                                                'status_transaction'    => statusTransactionV2($val->status),
                                                                'reasons_for_refusing'  => $val->reasons_for_refusing,
                                                                'status_code'           => $val->status,
                                                                'transaction_details'   => $val->transactionDetails,
                                                                'total_items_purchased' => count($val->transactionDetails).' barang',
                                                                'created_at'            => $val->created_at,
                                                                'updated_at'            => $val->updated_at
                                                            ]
                                                    ];

            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $listData->currentPage(),
                'from'              => 1,
                'last_page'         => $listData->lastPage(),
                'next_page_url'     => $listData->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $listData->perPage(),
                'prev_page_url'     => $listData->previousPageUrl(),
                'total'             => $listData->total()
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $invoice = Invoice::where(['user_id' => auth()->user()->id])->pluck('id');

        if (request('filter') != null) {
            // Initialize
            $transactions = Transaction::whereIn('invoice_id', $invoice)->where('status', request('filter'))->latest()->get();
        } else if (request('search')) {
            // Initialize
            $transactionsId = Transaction::whereIn('invoice_id', $invoice)->pluck('id');
            $transactionsD  = TransactionDetails::where('course_name', 'LIKE', '%'.request('search').'%')
                                ->whereIn('transaction_id', $transactionsId)
                                ->latest()
                                ->pluck('transaction_id');
            $transactions = Transaction::whereIn('invoice_id', $invoice)->whereIn('id', $transactionsD)->latest()->get();
        } else {
            // Initialize
            $transactions = Transaction::whereIn('invoice_id', $invoice)->latest()->get();
        }

        $listData = $this->paginate($transactions, 20, null, ['path' => $request->fullUrl()]);
        $data     = [];

        foreach($listData as $val) {
            // Initialize
            $row['id']                              = $val->id;
            $row['invoice_id']                      = 'INV-'.$val->invoice_id;
            $row['store_id']                        = ($val->invoice->category_transaction == 0) ? $val->store_id : '-';
            $row['store_name']                      = ($val->invoice->category_transaction == 0) ? $val->company->Name : '-';
            $row['total_payment']                   = ($val->invoice->is_termin)
                                                        ? $val->invoice->total_payment
                                                        : ($val->total_payment + $val->shipping_cost + $val->unique_code);
            $row['total_payment_rupiah']            = ($val->invoice->is_termin)
                                                        ? rupiah($val->invoice->total_payment)
                                                        : rupiah($val->total_payment + $val->shipping_cost + $val->unique_code);
            $row['category_transacation']           = categoryTransaction($val->invoice->category_transaction);
            $row['status_payment']                  = statusPayment($val->invoice->status);  
            $row['status_transaction']              = ($val->invoice->category_transaction == 0) ? 
                                                        ($val->invoice->status != 2) ? statusTransactionV2($val->status) : '-'
                                                        : '-';
            $row['reasons_for_refusing']            = $val->reasons_for_refusing;
            $row['status_code']                     = $val->status;

            // $row['total_payment_by_invoice']        = ($val->invoice->is_termin)
            //                                             ? $val->invoice->total_payment
            //                                             : ($val->total_payment + $val->shipping_cost + $val->unique_code);
            // $row['total_payment_by_invoice_rupiah'] = ($val->invoice->is_termin)
            //                                             ? rupiah($val->invoice->total_payment)
            //                                             : rupiah($val->total_payment + $val->shipping_cost + $val->unique_code);

            $row['total_payment_by_invoice']        = $val->invoice->total_payment;
            $row['total_payment_by_invoice_rupiah'] = rupiah($val->invoice->total_payment);
            $row['expired_transaction']             = date('d F Y', strtotime($val->invoice->expired_transaction));

            // Products
            $transactionDetails = TransactionDetails::where('transaction_id', $val->id)->first();
            
            $row['transaction_details'] = [
                'item'                  => $transactionDetails,
                'total_items_purchased' => count($val->transactionDetails).' barang'
            ];

            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->updated_at;
            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $listData->currentPage(),
                'from'              => 1,
                'last_page'         => $listData->lastPage(),
                'next_page_url'     => $listData->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $listData->perPage(),
                'prev_page_url'     => $listData->previousPageUrl(),
                'total'             => $listData->total()
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
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak ditemukan.'
            ]);
        }

        // Check Category Invoice
        if ($invoice->category_transaction != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Hanya untuk kategori Belanja.'
            ]);
        }

        // Detail Invoice
        $row['no_invoice']           = 'INV-'.$invoice->id;
        $row['purchase_date']        = $invoice->created_at;
        $row['status_payment']       = statusPayment($invoice->status);  
        // $row['status_transaction']   = ($invoice->status != 2) ? statusTransactionV2($invoice->status) : '-';  
        $row['reasons_for_refusing'] = $invoice->reasons_for_canceling_the_transaction;
        $row['status_code']          = $invoice->status;
        $row['expired_transaction']  = $invoice->expired_transaction;  

        // Store Details
        $store              = [];
        $totalItemPurchased = 0;
        $totalShippingCost  = 0;

        foreach($invoice->transaction as $transaction) {
            // Initialize
            $storeDetails       = $transaction->company;
            $products           = [];
            $totalItemPurchased += count($transaction->transactionDetails);
            $totalShippingCost  += $transaction->shipping_cost;

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

                $attribute['custom_document_input']  = $dataCDI;
                $attribute['category_detail_inputs'] = ($val->category_detail_inputs) ? $val->category_detail_inputs : null;
                $attribute['is_immovable_object']    = $val->is_immovable_object;

                $products[] = $attribute;
            }

            // Shipping Information
            $address = $transaction->invoice->invoiceAddress;
            
            $shippingInformation = [
                'expedition'            => ($transaction->expedition != null || $transaction->expedition != '-')
                                            ? $transaction->expedition.' - '.$transaction->service
                                            : 'Armada',
                'receipt'               => $transaction->receipt,
                'address'               => $address->details_address.' <br> '.$address->district.' '.$address->city.' '.$address->province,
                'total_shipping_cost'   => $transaction->shipping_cost
            ];

            $data = [
                'store_details' => [
                    'store_id'    => $storeDetails->ID,
                    'store_name'  => $storeDetails->Name,
                    'logo'        => $storeDetails->Logo
                ],
                'status_transaction'                    => statusTransactionV2($transaction->status),
                'products'                              => $products,
                'shipping_information'                  => $shippingInformation,
                'total_payment_by_transaction'          => $transaction->total_payment,
                'total_payment_by_transaction_rupiah'   => rupiah($transaction->total_payment)
            ];

            $store[] = $data;
        }

        // Store - Transaction
        $row['store'] = $store;

        // Payment Details
        $row['payment_details'] = [
            // 'payment_method'                  => $invoice->bank_name,
            // 'sub_total_payment'               => ($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            // 'sub_total_payment_rupiah'        => rupiah($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            // 'total_payment'                   => $invoice->total_payment,
            // 'total_payment_rupiah'            => rupiah($invoice->total_payment),
            // 'total_items_purchased'           => $totalItemPurchased,
            // 'total_shipping_cost'             => $totalShippingCost,
            // 'total_shipping_cost_rupiah'      => rupiah($totalShippingCost),
            // 'unique_code'                     => $invoice->unique_code
            'payment_method'                        => paymentType($invoice->payment_type),
            'bank_name'                             => $invoice->bank_name,
            'no_rek'                                => $invoice->no_rek,
            'sub_total_payment'                     => ($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            'sub_total_payment_rupiah'              => rupiah($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            'total_payment'                         => $invoice->total_payment,
            'total_payment_rupiah'                  => rupiah($invoice->total_payment),
            'total_payment_without_balance'         => $invoice->total_payment_without_balance,
            'total_payment_without_balance_rupiah'  => rupiah($invoice->total_payment_without_balance),
            'total_payment_with_balance'            => ($invoice->total_payment_without_balance),
            'total_payment_with_balance_rupiah'     => rupiah($invoice->total_payment_without_balance),
            'total_items_purchased'                 => $totalItemPurchased,
            'total_shipping_cost'                   => $totalShippingCost,
            'total_shipping_cost_rupiah'            => rupiah($totalShippingCost),
            'unique_code'                           => ($invoice->second_unique_code) ? $invoice->second_unique_code : $invoice->unique_code,
            'original_unique_code'                  => $invoice->unique_code
        ];

        // Transaction Type
        $row['payment_details_type'] = categoryTransaction($invoice->category_transaction);
        
        // Initialize
        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function showByTransaction($id)
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
        $row['no_invoice']           = 'INV-'.$transaction->invoice->id;
        $row['purchase_date']        = $transaction->invoice->created_at;
        $row['status_payment']       = statusPayment($transaction->invoice->status);  
        $row['status_transaction']   = ($transaction->invoice->status != 2) ? statusTransactionV2($transaction->status) : '-';  
        $row['reasons_for_refusing'] = $transaction->invoice->reasons_for_refusing;
        $row['status_code']          = $transaction->invoice->status;
        $row['expired_transaction']  = $transaction->invoice->expired_transaction;  
        $row['transaction_id']       = $transaction->id;

        // Check Immovable Object True
        $immovableObject = false;

        foreach ($transaction->transactionDetails as $imo) {
            if ($imo->is_immovable_object == 1) {
                $immovableObject = true;
            }
        }

        $row['immovable_object'] = $immovableObject;

        // Store Details
        $store              = [];
        $totalItemPurchased = 0;
        $totalShippingCost  = 0;

        // Initialize
        $storeDetails       = $transaction->company;
        $products           = [];
        $totalItemPurchased += count($transaction->transactionDetails);
        $totalShippingCost  += $transaction->shipping_cost;

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

            $attribute['custom_document_input']  = $dataCDI;
            $attribute['category_detail_inputs'] = ($val->category_detail_inputs) ? $val->category_detail_inputs : null;
            $attribute['is_immovable_object']    = $val->is_immovable_object;

            $products[] = $attribute;
        }

        // Shipping Information
        $address = $transaction->invoice->invoiceAddress;
        
        $shippingInformation = [
            'expedition'            => ($transaction->expedition != null || $transaction->expedition != '-')
                                        ? $transaction->expedition.' - '.$transaction->service
                                        : 'Armada',
            'receipt'               => $transaction->receipt,
            'address'               => $address->details_address.' <br> '.$address->district.' '.$address->city.' '.$address->province,
            'total_shipping_cost'   => $transaction->shipping_cost
        ];

        $data = [
            'store_details' => [
                'store_id'    => $storeDetails->ID,
                'store_name'  => $storeDetails->Name,
                'logo'        => $storeDetails->Logo
            ],
            'products'                              => $products,
            'shipping_information'                  => $shippingInformation,
            'total_payment_by_transaction'          => $transaction->total_payment,
            'total_payment_by_transaction_rupiah'   => rupiah($transaction->total_payment)
        ];

        $store[] = $data;

        // Store - Transaction
        $row['store'] = $store;

        // Payment Details
        $row['payment_details'] = [
            'payment_method'                        => paymentType($transaction->invoice->payment_type),
            'bank_name'                             => $transaction->invoice->bank_name,
            'no_rek'                                => $transaction->invoice->no_rek,
            'sub_total_payment'                     => ($transaction->total_payment - $totalShippingCost - $transaction->invoice->unique_code),
            'sub_total_payment_rupiah'              => rupiah($transaction->total_payment - $totalShippingCost - $transaction->invoice->unique_code),
            'total_payment'                         => $transaction->total_payment,
            'total_payment_rupiah'                  => rupiah($transaction->total_payment),
            'total_payment_without_balance'         => $transaction->invoice->total_payment_without_balance,
            'total_payment_without_balance_rupiah'  => rupiah($transaction->invoice->total_payment_without_balance),
            'total_payment_with_balance'            => ($transaction->invoice->total_payment_without_balance),
            'total_payment_with_balance_rupiah'     => rupiah($transaction->invoice->total_payment_without_balance),
            'total_items_purchased'                 => $totalItemPurchased,
            'total_shipping_cost'                   => $totalShippingCost,
            'total_shipping_cost_rupiah'            => rupiah($totalShippingCost),
            // 'unique_code'                           => ($transaction->invoice->second_unique_code) ? $transaction->invoice->second_unique_code : $transaction->invoice->unique_code,
            // 'original_unique_code'                  => $transaction->invoice->unique_code
            'unique_code'                           => 0,
            'original_unique_code'                  => 0
        ];

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

    public function showV1($id)
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

            $attribute['custom_document_input']  = $dataCDI;
            $attribute['category_detail_inputs'] = ($val->category_detail_inputs) ? $val->category_detail_inputs : null;

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
            'total_payment_by_invoice_rupiah' => rupiah($transaction->total_payment + $transaction->shipping_cost + $transaction->invoice->unique_code),
            'unique_code'                     => ($transaction->invoice->second_unique_code && $transaction->invoice->second_unique_code != 0)
                                                    ? $transaction->invoice->second_unique_code
                                                    : $transaction->invoice->unique_code
        ];

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

    public function showTopUp($id)
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
        if ($transaction->invoice->category_transaction != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Hanya untuk kategori Top Up.'
            ]);
        }

        // Detail Invoice
        $row['no_invoice']          = 'INV-'.$transaction->invoice_id;
        $row['purchase_date']       = $transaction->created_at;
        $row['status_payment']      = statusPayment($transaction->invoice->status);  
        $row['status_code']         = $transaction->status;
        $row['expired_transaction'] = $transaction->invoice->expired_transaction;
        $row['total']               = $transaction->total_payment;
        $row['unique_code']         = ($transaction->invoice->second_unique_code && $transaction->invoice->second_unique_code != 0)
                                        ? $transaction->invoice->second_unique_code
                                        : $transaction->invoice->unique_code;
        $row['description']         = 'TopUp via -';
        $row['destination_bank']    = [
                                            'payment_type' => paymentType($transaction->invoice->payment_type),
                                            'bank_name'    => $transaction->invoice->bank_name,
                                            'no_rek'       => $transaction->invoice->no_rek
                                        ];
        $row['from_the_bank']       = [
                                            'bank_name' => '-',
                                            'no_rek'    => '-'
                                        ];

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
            'payment_method'                        => paymentType($transaction->invoice->payment_type),
            'bank_name'                             => $transaction->invoice->bank_name,
            'no_rek'                                => $transaction->invoice->no_rek,
            'sub_total_payment'                     => ($transaction->invoice->total_payment - $transaction->invoice->unique_code),
            'sub_total_payment_rupiah'              => rupiah($transaction->invoice->total_payment - $transaction->invoice->unique_code),
            'total_payment'                         => $transaction->invoice->total_payment,
            'total_payment_rupiah'                  => rupiah($transaction->invoice->total_payment),
            'total_payment_without_balance'         => $transaction->invoice->total_payment_without_balance,
            'total_payment_without_balance_rupiah'  => rupiah($transaction->invoice->total_payment_without_balance),
            'total_payment_with_balance'            => ($transaction->invoice->total_payment_without_balance - $transaction->invoice->total_payment),
            'total_payment_with_balance_rupiah'     => rupiah($transaction->invoice->total_payment_without_balance - $transaction->invoice->total_payment),
            'total_items_purchased'                 => 0,
            'total_shipping_cost'                   => 0,
            'total_shipping_cost_rupiah'            => rupiah(0),
            'unique_code'                           => ($transaction->invoice->second_unique_code) ? $transaction->invoice->second_unique_code : $transaction->invoice->unique_code,
            'original_unique_code'                  => $transaction->invoice->unique_code
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
            // 'payment_method'                  => paymentType($mainTransaction->invoice->payment_type),
            // 'bank_name'                       => $mainTransaction->invoice->bank_name,
            // 'no_rek'                          => $mainTransaction->invoice->no_rek,
            // 'total_price'                     => ($mainTransaction->total_payment + $mainTransaction->shipping_cost),
            // 'total_price_rupiah'              => rupiah($mainTransaction->total_payment + $mainTransaction->shipping_cost),
            // 'total_items_purchased'           => count($mainTransaction->transactionDetails),
            // 'total_shipping_cost'             => $mainTransaction->shipping_cost,
            // 'total_shipping_cost_rupiah'      => rupiah($mainTransaction->shipping_cost),
            // 'total_payment_by_invoice'        => ($mainTransaction->total_payment + $mainTransaction->shipping_cost + $mainTransaction->invoice->unique_code),
            // 'total_payment_by_invoice_rupiah' => rupiah($mainTransaction->total_payment + $mainTransaction->shipping_cost + $mainTransaction->invoice->unique_code)
            'payment_method'                        => paymentType($mainTransaction->invoice->payment_type),
            'bank_name'                             => $mainTransaction->invoice->bank_name,
            'no_rek'                                => $mainTransaction->invoice->no_rek,
            'sub_total_payment'                     => ($mainTransaction->invoice->total_payment - $mainTransaction->invoice->unique_code),
            'sub_total_payment_rupiah'              => rupiah($mainTransaction->invoice->total_payment - $mainTransaction->invoice->unique_code),
            'total_payment'                         => $mainTransaction->invoice->total_payment,
            'total_payment_rupiah'                  => rupiah($mainTransaction->invoice->total_payment),
            'total_payment_without_balance'         => $mainTransaction->invoice->total_payment_without_balance,
            'total_payment_without_balance_rupiah'  => rupiah($mainTransaction->invoice->total_payment_without_balance),
            'total_payment_with_balance'            => ($mainTransaction->invoice->total_payment_without_balance - $mainTransaction->invoice->total_payment),
            'total_payment_with_balance_rupiah'     => rupiah($mainTransaction->invoice->total_payment_without_balance - $mainTransaction->invoice->total_payment),
            'total_items_purchased'                 => 0,
            'total_shipping_cost'                   => 0,
            'total_shipping_cost_rupiah'            => rupiah(0),
            'unique_code'                           => ($mainTransaction->invoice->second_unique_code) ? $mainTransaction->invoice->second_unique_code : $mainTransaction->invoice->unique_code,
            'original_unique_code'                  => $mainTransaction->invoice->unique_code
        ];

        $row['main_transaction'] = [
            'store'                 => $store,
            'products'              => $products,
            'shipping_information'  => $shippingInformation,
            'payment_details'       => $paymentDetails
        ];

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

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function cancelTransactionAll($id, Request $request)
    {
        // Initialize
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($invoice->status != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak bisa dibatalkan.'
            ]);
        }

        // Update All Transaction
        $transaction = Transaction::where('invoice_id', $id)->update([
            'status' => 5
        ]);

        $invoice->update([
            'status'                                => 4,
            'reasons_for_canceling_the_transaction' => request('reasons_for_canceling_the_transaction')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Transaksi Berhasil dibatalkan.',
            'data'      => $invoice
        ]);
    }

    public function cancelTransaction(Transaction $transaction, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'reason'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->reason_cancel) {
            $data = [
                'status'    => 'error',
                'message'   =>'Anda telah mengajukan pembatalan transaksi ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check if transaaction pending
        if ($transaction->invoice->status == 0) {
            $transaction->update(['status' => '5', 'reason_cancel' => $request->reason]);

            $data = [
                'status'    => 'success',
                'message'   => 'Cancel berhasil',
                'code'      => 200
            ];
            return response()->json($data, 200);
        } else {
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
                'reason_cancel' => $request->reason
            ]);

            // Initialize For Notification - Seller
            $sender         = auth()->user();
            $receiverId     = User::where('company_id', $transaction->store_id)->first();
            $title          = 'Pengajuan Pembatalan Transaksi';
            $message        = 'Ada pengajuan pembatalan transaksi dengan alasan ' . $request->reason;
            $code           = '01';
            $data           = [
                'transaction_id' => $transaction->id
            ];
            $icon           = '';
    
            Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
    
            $data = [
                'status'    => 'success',
                'message'   => 'Pengajuan cancel berhasil, menunggu toko untuk verifikasi',
                'code'      => 200
            ];
    
            return response()->json($data, 200);
        }
    }

    public function finishTransaction(Transaction $transaction, Request $request)
    {
        if ($transaction->status == 6) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda Sudah menyelesaikan transaksi ini',
                'code'      => 400
            ];

            return response()->json($data);
        }

        if ($transaction->status <= 2) {
            $data = [
                'status'    => 'error',
                'message'   => 'Selesaikan transaksi gagal, transaksi bisa diselesaikan ketika barang sudah dikirim seller.',
                'code'      => 400
            ];

            return response()->json($data);
        }

        // if ($transaction->status != 4) {
        //     $data = [
        //         'status'    => 'error',
        //         'message'   => 'Selesaikan transaksi gagal transaksi ini bukan status nya Received (Sudah diterima)',
        //         'code'      => 400
        //     ];

        //     return response()->json($data);
        // }

        $transaction->update([
            'status' => 6,
        ]);

        $get_admin_store = User::where('company_id', $transaction->store_id)->first();

        if ($get_admin_store) {
            // get product bayar muka
            $check_transaction_detail = TransactionDetails::where('back_payment_status', 1)->where('transaction_id', $transaction->id)->get();
            // get product bayar belakang
            $check_transaction_detail_back = TransactionDetails::where('back_payment_status', 0)->where('transaction_id', $transaction->id)->get();

            if (count($check_transaction_detail) > 0) { // if true product bayar muka (ada data bayar dimuka)

                if (count($check_transaction_detail_back) > 0) { // check jika ada data product yg dibayar dibelakang
                    // init
                    $total = 0;
                    
                    foreach ($check_transaction_detail_back as $key => $value) {

                        $total_price = $value->price_course;
                        if ($value->discount) {
                            $total_price = discountFormula($value->discount, $value->price_course);
                        }
    
                        $total += ($total_price * $value->qty);
                    }

                    // init
                    $admin_platinum = null;

                    // check status vendor (commision for platinum)
                    $store = Company::find($transaction->store_id);
                    
                    // check vendor premium
                    if ($store && $store->status && $store->status == 2 && $store->city_id) {
                        
                        // check vendor platinum exists
                        $store_platinum = Company::where('city_id', $store->city_id)->where('status', 1)->first();

                        if ($store_platinum) {
                            $admin_platinum = User::where('company_id')->where('role_id', 1)->first();
                        }
                    }

                    if ($admin_platinum) {
                        // commision platinum (1%)
                        Wallet::create([
                            'user_id'           => $admin_platinum->id,
                            'is_verified'       => 1,
                            'balance_type'      => 0,
                            'apps_commission'   => 0,
                            'balance'           => ($total) - (0.01 * $total),
                            'original_balance'  => ($total) - (0.01 * $total),
                            'details'           => 'Commision Transaksi - (#INV-'.$transaction->invoice_id.')'
                        ]);

                        // Pembayaran saldo ke seller 
                        Wallet::create([
                            'user_id'           => $get_admin_store->id,
                            'is_verified'       => 1,
                            'balance_type'      => 0,
                            'apps_commission'   => 4,
                            'balance'           => ($total) - (0.04 * $total),
                            'original_balance'  => $total,
                            'details'           => 'Income Transaksi'
                        ]);

                        // Insert Joint Bank
                        TransactionJointBank::create([
                            'invoice_id'                    => $transaction->invoice_id,
                            'transaction_id'                => $transaction->id,
                            'total_payment_by_transaction'  => $total,
                            'apps_commission'               => 5,
                            'total_after_deduction'         => ($total) - (0.04 * $total),
                            'status'                        => 1
                        ]);

                        // Insert Admin Commission
                        TransactionAdminCommission::create([
                            'invoice_id'                    => $transaction->invoice_id,
                            'transaction_id'                => $transaction->id,
                            'total_payment_by_transaction'  => (0.04 * $total),
                            'apps_commission'               => 0,
                            'total_after_deduction'         => (0.04 * $total)
                        ]);
                    }

                    if (!$admin_platinum) { // if blum ada platinum & premium (5% commision)
                        // Insert Wallet
                        Wallet::create([
                            'user_id'           => $get_admin_store->id,
                            'is_verified'       => 1,
                            'balance_type'      => 0,
                            'apps_commission'   => 5,
                            'balance'           => ($total) - (0.05 * $total),
                            'original_balance'  => $total,
                            'details'           => 'Income Transaksi'
                        ]);

                        // Insert Joint Bank
                        TransactionJointBank::create([
                            'invoice_id'                    => $transaction->invoice_id,
                            'transaction_id'                => $transaction->id,
                            'total_payment_by_transaction'  => $total,
                            'apps_commission'               => 5,
                            'total_after_deduction'         => ($total) - (0.05 * $total),
                            'status'                        => 1
                        ]);

                        // Insert Admin Commission
                        TransactionAdminCommission::create([
                            'invoice_id'                    => $transaction->invoice_id,
                            'transaction_id'                => $transaction->id,
                            'total_payment_by_transaction'  => (0.05 * $total),
                            'apps_commission'               => 0,
                            'total_after_deduction'         => (0.05 * $total)
                        ]);
                    }

                    // Initialize
                    $totalDeduction = 0.05;

                    if ($admin_platinum) {
                        $totalDeduction = 0.04;
                    }

                    // Notification
                    $sender         = $transaction->company->user;
                    $receiverId     = $transaction->company->user->id;
                    $title          = 'Pembayaran Transaksi';
                    $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah((($total) - ($totalDeduction * $total))).' Dari '.rupiah($total).' Dipotong 5% untuk transaksi (Nomor Invoice #INV-'.$transaction->invoice_id.')';
                    $code           = '02';
                    $data           = [
                        'transaction_id' => $transaction->id
                    ];
                    $icon           = '';
                    
                    Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
    
                    $data = [
                        'status'    => 'success',
                        'message'   => 'Menyelesaikan transaksi berhasil',
                        'code'      => 200
                    ];
            
                    return response()->json($data, 200);
                }

                $data = [
                    'status'    => 'success',
                    'message'   => 'Menyelesaikan transaksi berhasil',
                    'code'      => 200
                ];
        
                return response()->json($data, 200);
            }

            if (count($check_transaction_detail) == 0) { // check jika tidak ada bayar didepan (hanya dibelakang)

                // init
                $admin_platinum = null;

                // check status vendor (commision for platinum)
                $store = Company::find($transaction->store_id);
                
                // check vendor premium
                if ($store && $store->status && $store->status == 2 && $store->city_id) {
                    
                    // check vendor platinum exists
                    $store_platinum = Company::where('city_id', $store->city_id)->where('status', 1)->first();

                    if ($store_platinum) {
                        $admin_platinum = User::where('company_id')->where('role_id', 1)->first();
                    }
                }

                if ($admin_platinum) {
                    // commision platinum (1%)
                    Wallet::create([
                        'user_id'           => $admin_platinum->id,
                        'is_verified'       => 1,
                        'balance_type'      => 0,
                        'apps_commission'   => 0,
                        'balance'           => ($transaction->total_payment) - (0.01 * $transaction->total_payment),
                        'original_balance'  => ($transaction->total_payment) - (0.01 * $transaction->total_payment),
                        'details'           => 'Commision Transaksi'
                    ]);

                    // Pembayaran saldo ke seller 
                    Wallet::create([
                        'user_id'           => $get_admin_store->id,
                        'is_verified'       => 1,
                        'balance_type'      => 0,
                        'apps_commission'   => 4,
                        'balance'           => ($transaction->total_payment) - (0.04 * $transaction->total_payment),
                        'original_balance'  => $transaction->total_payment,
                        'details'           => 'Income Transaksi'
                    ]);

                    // Insert Joint Bank
                    TransactionJointBank::create([
                        'invoice_id'                    => $transaction->invoice_id,
                        'transaction_id'                => $transaction->id,
                        'total_payment_by_transaction'  => $transaction->total_payment,
                        'apps_commission'               => 5,
                        'total_after_deduction'         => ($transaction->total_payment) - (0.04 * $transaction->total_payment),
                        'status'                        => 1
                    ]);

                    // Insert Admin Commission
                    TransactionAdminCommission::create([
                        'invoice_id'                    => $transaction->invoice_id,
                        'transaction_id'                => $transaction->id,
                        'total_payment_by_transaction'  => (0.04 * $transaction->total_payment),
                        'apps_commission'               => 0,
                        'total_after_deduction'         => (0.04 * $transaction->total_payment)
                    ]);
                }

                if (!$admin_platinum) { // if blum ada platinum & premium (5% commision)
                    // Insert Wallet
                    Wallet::create([
                        'user_id'           => $get_admin_store->id,
                        'is_verified'       => 1,
                        'balance_type'      => 0,
                        'apps_commission'   => 5,
                        'balance'           => ($transaction->total_payment) - (0.05 * $transaction->total_payment),
                        'original_balance'  => $transaction->total_payment,
                        'details'           => 'Income Transaksi'
                    ]);

                    // Insert Joint Bank
                    TransactionJointBank::create([
                        'invoice_id'                    => $transaction->invoice_id,
                        'transaction_id'                => $transaction->id,
                        'total_payment_by_transaction'  => $transaction->total_payment,
                        'apps_commission'               => 5,
                        'total_after_deduction'         => ($transaction->total_payment) - (0.05 * $transaction->total_payment),
                        'status'                        => 1
                    ]);

                    // Insert Admin Commission
                    TransactionAdminCommission::create([
                        'invoice_id'                    => $transaction->invoice_id,
                        'transaction_id'                => $transaction->id,
                        'total_payment_by_transaction'  => (0.05 * $transaction->total_payment),
                        'apps_commission'               => 0,
                        'total_after_deduction'         => (0.05 * $transaction->total_payment)
                    ]);
                }


                // Initialize
                $totalDeduction = 0.05;

                if ($admin_platinum) {
                    $totalDeduction = 0.04;
                }

                // Notification
                $sender         = $transaction->company->user;
                $receiverId     = $transaction->company->user->id;
                $title          = 'Pembayaran Transaksi';
                $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah((($transaction->total_payment) - ($totalDeduction * $transaction->total_payment))).' Dari '.rupiah($transaction->total_payment).' Dipotong 5% untuk transaksi (Nomor Invoice #INV-'.$transaction->invoice_id.')';
                $code           = '02';
                $data           = [
                    'transaction_id' => $transaction->id
                ];
                $icon           = '';
                
                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
        
                $data = [
                    'status'    => 'success',
                    'message'   => 'Menyelesaikan transaksi berhasil',
                    'code'      => 200
                ];
        
                return response()->json($data, 200);
            }
        }

        $data = [
            'status'    => 'error',
            'message'   => 'Menyelesaikan transaksi gagal',
            'code'      => 400
        ];

        return response()->json($data, 400);
    }

    // Cron Job
    public function expiredTransaction()
    {
        // Initialize
        $date    = date('Y-m-d H:i:s');
        $invoice = Invoice::whereDate('expired_transaction', '<', $date)
                    ->where('status', 0)
                    ->update([
                        'status' => 2
                    ]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function finishedTransactionAuto(Request $request)
    {
        $transaction = Transaction::where('status', 4)->get();

        if (count($transaction) > 0) {
            foreach ($transaction as $key => $value) {
                $date = new Carbon($value->time_received);

                if($date->diffIndays() >= 3) { // check 3 day

                    $value->update([
                        'status'    => 6,
                    ]);
            
                    $get_admin_store = User::where('company_id', $value->store_id)->first();
            
                    if ($get_admin_store) {

                        // get product bayar muka
                        $check_transaction_detail = TransactionDetails::where('back_payment_status', 1)->where('transaction_id', $value->id)->get();
                        // get product bayar belakang
                        $check_transaction_detail_back = TransactionDetails::where('back_payment_status', 0)->where('transaction_id', $value->id)->get();
            
            
                        if (count($check_transaction_detail) > 0) { // if true product bayar muka (ada data bayar dimuka)
            
                            $total = 0;
            
                            if (count($check_transaction_detail_back) > 0) { // check jika ada data product yg dibayar dibelakang
                                foreach ($check_transaction_detail_back as $key => $v) {
            
                                    $total_price = $v->price_course;
                                    if ($v->discount) {
                                        $total_price = discountFormula($v->discount, $v->price_course);
                                    }
                
                                    $total += ($total_price * $v->qty);
                
                                }

                                // init
                                $admin_platinum = null;

                                // check status vendor (commision for platinum)
                                $store = Company::find($value->store_id);
                                
                                // check vendor premium
                                if ($store && $store->status && $store->status == 2 && $store->city_id) {
                                    
                                    // check vendor platinum exists
                                    $store_platinum = Company::where('city_id', $store->city_id)->where('status', 1)->first();

                                    if ($store_platinum) {
                                        $admin_platinum = User::where('company_id')->where('role_id', 1)->first();
                                    }
                                }

                                if ($admin_platinum) {
                                    // commision platinum (1%)
                                    Wallet::create([
                                        'user_id'           => $admin_platinum->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 0,
                                        'apps_commission'   => 0,
                                        'balance'           => ($total) - (0.01 * $total),
                                        'original_balance'  => ($total) - (0.01 * $total),
                                        'details'           => 'Commision Transaksi - (#INV-'.$value->invoice_id.')'
                                    ]);

                                    // Pembayaran saldo ke seller 
                                    Wallet::create([
                                        'user_id'           => $get_admin_store->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 0,
                                        'apps_commission'   => 4,
                                        'balance'           => ($total) - (0.04 * $total),
                                        'original_balance'  => $total,
                                        'details'           => 'Income Transaksi - (#INV-'.$value->invoice_id.')'
                                    ]);

                                    // Insert Joint Bank
                                    TransactionJointBank::create([
                                        'invoice_id'                    => $value->invoice_id,
                                        'transaction_id'                => $value->id,
                                        'total_payment_by_transaction'  => $total,
                                        'apps_commission'               => 5,
                                        'total_after_deduction'         => ($total) - (0.04 * $total),
                                        'status'                        => 1
                                    ]);

                                    // Insert Admin Commission
                                    TransactionAdminCommission::create([
                                        'invoice_id'                    => $value->invoice_id,
                                        'transaction_id'                => $value->id,
                                        'total_payment_by_transaction'  => (0.04 * $total),
                                        'apps_commission'               => 0,
                                        'total_after_deduction'         => (0.04 * $total)
                                    ]);
                                }

                                if (!$admin_platinum) { // if blum ada platinum & premium (5% commision)
                                    // Insert Wallet
                                    Wallet::create([
                                        'user_id'           => $get_admin_store->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 0,
                                        'apps_commission'   => 5,
                                        'balance'           => ($total) - (0.05 * $total),
                                        'original_balance'  => $total,
                                        'details'           => 'Income Transaksi'
                                    ]);

                                    // Insert Joint Bank
                                    TransactionJointBank::create([
                                        'invoice_id'                    => $value->invoice_id,
                                        'transaction_id'                => $value->id,
                                        'total_payment_by_transaction'  => $total,
                                        'apps_commission'               => 5,
                                        'total_after_deduction'         => ($total) - (0.05 * $total),
                                        'status'                        => 1
                                    ]);

                                    // Insert Admin Commission
                                    TransactionAdminCommission::create([
                                        'invoice_id'                    => $value->invoice_id,
                                        'transaction_id'                => $value->id,
                                        'total_payment_by_transaction'  => (0.05 * $total),
                                        'apps_commission'               => 0,
                                        'total_after_deduction'         => (0.05 * $total)
                                    ]);
                                }

                                // Initialize
                                $totalDeduction = 0.05;

                                if ($admin_platinum) {
                                    $totalDeduction = 0.04;
                                }

                                // Notification
                                $sender         = $value->company->user;
                                $receiverId     = $value->company->user->id;
                                $title          = 'Pembayaran Transaksi';
                                $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah((($total) - ($totalDeduction * $total))).' Dari '.rupiah($total).' Dipotong 5% untuk transaksi (Nomor Invoice #INV-'.$value->invoice_id.')';
                                $code           = '02';
                                $data           = [
                                    'transaction_id' => $value->id
                                ];
                                $icon           = '';
                                
                                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
                
                            }
                        }
            
                        if (count($check_transaction_detail) == 0) { // check jika tidak ada bayar didepan (hanya dibelakang)

                            // init
                            $admin_platinum = null;

                            // check status vendor (commision for platinum)
                            $store = Company::find($value->store_id);
                            
                            // check vendor premium
                            if ($store && $store->status && $store->status == 2 && $store->city_id) {
                                
                                // check vendor platinum exists
                                $store_platinum = Company::where('city_id', $store->city_id)->where('status', 1)->first();

                                if ($store_platinum) {
                                    $admin_platinum = User::where('company_id')->where('role_id', 1)->first();
                                }
                            }

                            if ($admin_platinum) {
                                // commision platinum (1%)
                                Wallet::create([
                                    'user_id'           => $admin_platinum->id,
                                    'is_verified'       => 1,
                                    'balance_type'      => 0,
                                    'apps_commission'   => 0,
                                    'balance'           => ($value->total_payment) - (0.01 * $value->total_payment),
                                    'original_balance'  => ($value->total_payment) - (0.01 * $value->total_payment),
                                    'details'           => 'Commision Transaksi'
                                ]);

                                // Pembayaran saldo ke seller 
                                Wallet::create([
                                    'user_id'           => $get_admin_store->id,
                                    'is_verified'       => 1,
                                    'balance_type'      => 0,
                                    'apps_commission'   => 4,
                                    'balance'           => ($value->total_payment) - (0.04 * $value->total_payment),
                                    'original_balance'  => $value->total_payment,
                                    'details'           => 'Income Transaksi'
                                ]);

                                // Insert Joint Bank
                                TransactionJointBank::create([
                                    'invoice_id'                    => $value->invoice_id,
                                    'transaction_id'                => $value->id,
                                    'total_payment_by_transaction'  => $value->total_payment,
                                    'apps_commission'               => 5,
                                    'total_after_deduction'         => ($value->total_payment) - (0.04 * $value->total_payment),
                                    'status'                        => 1
                                ]);

                                // Insert Admin Commission
                                TransactionAdminCommission::create([
                                    'invoice_id'                    => $value->invoice_id,
                                    'transaction_id'                => $value->id,
                                    'total_payment_by_transaction'  => (0.04 * $value->total_payment),
                                    'apps_commission'               => 0,
                                    'total_after_deduction'         => (0.04 * $value->total_payment)
                                ]);
                            }

                            if (!$admin_platinum) { // if blum ada platinum & premium (5% commision)
                                // Insert Wallet
                                Wallet::create([
                                    'user_id'           => $get_admin_store->id,
                                    'is_verified'       => 1,
                                    'balance_type'      => 0,
                                    'apps_commission'   => 5,
                                    'balance'           => ($value->total_payment) - (0.05 * $value->total_payment),
                                    'original_balance'  => $value->total_payment,
                                    'details'           => 'Income Transaksi'
                                ]);

                                // Insert Joint Bank
                                TransactionJointBank::create([
                                    'invoice_id'                    => $value->invoice_id,
                                    'transaction_id'                => $value->id,
                                    'total_payment_by_transaction'  => $value->total_payment,
                                    'apps_commission'               => 5,
                                    'total_after_deduction'         => ($value->total_payment) - (0.05 * $value->total_payment),
                                    'status'                        => 1
                                ]);

                                // Insert Admin Commission
                                TransactionAdminCommission::create([
                                    'invoice_id'                    => $value->invoice_id,
                                    'transaction_id'                => $value->id,
                                    'total_payment_by_transaction'  => (0.05 * $value->total_payment),
                                    'apps_commission'               => 0,
                                    'total_after_deduction'         => (0.05 * $value->total_payment)
                                ]);
                            }


                            // Initialize
                            $totalDeduction = 0.05;

                            if ($admin_platinum) {
                                $totalDeduction = 0.04;
                            }

                            // Notification
                            $sender         = $value->company->user;
                            $receiverId     = $value->company->user->id;
                            $title          = 'Pembayaran Transaksi';
                            $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah((($value->total_payment) - ($totalDeduction * $value->total_payment))).' Dari '.rupiah($value->total_payment).' Dipotong 5% untuk transaksi (Nomor Invoice #INV-'.$value->invoice_id.')';
                            $code           = '02';
                            $data           = [
                                'transaction_id' => $value->id
                            ];
                            $icon           = '';
                            
                            Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
                        }
                    }
                }
            }
        }

        return "success";
    }

    public function complain(Transaction $transaction, Request $request)
    {
        if ($transaction->status == 7) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda Sudah mengajukan komplain transaksi ini',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }


        if ($transaction->status != 4) {
            $data = [
                'status'    => 'error',
                'message'   => 'Selesaikan transaksi gagal transaksi ini bukan status nya Received (Sudah diterima)',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        $validator = Validator::make(request()->all(), [
            'reason'    => 'required|string',
            'file'      => 'required|mimes:jpeg,png,jpg,mp4|max:10240'
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
            'status' => 7
        ]);

        $path = null;
        if ($request->file('file') != '') {
            $path = $request->file('file')->store('uploads/transaction/complain/'.$transaction->id.'/', 'public');
            $path = env('SITE_URL'). '/storage/'. $path;
        }

        $complain = TransactionComplain::create([
            'transaction_id'    => $transaction->id,
            'reason'            => $request->reason,
            'file'              => $path
        ]);
        
        $data = [
            'status'    => 'success',
            'message'   => 'Mengajukan komplain berhasil',
            'code'      => 200
        ];

        return response()->json($data, 200);
    }

    public function paymentStep($id)
    {
        // Check Invoice
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Invoice
        $totalShippingCost  = 0;
        $totalItemPurchased = 0;

        foreach($invoice->transaction as $transaction) {
            // Initialize
            $totalShippingCost += $transaction->shipping_cost;
            $totalItemPurchased += count($transaction->transactionDetails);
        }

        // Initialize
        $row['payment_details'] = [
            'payment_method'                        => paymentType($invoice->payment_type),
            'bank_name'                             => $invoice->bank_name,
            'no_rek'                                => $invoice->no_rek,
            'sub_total_payment'                     => ($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            'sub_total_payment_rupiah'              => rupiah($invoice->total_payment - $totalShippingCost - $invoice->unique_code),
            'total_payment'                         => $invoice->total_payment,
            'total_payment_rupiah'                  => rupiah($invoice->total_payment),
            'total_payment_without_balance'         => $invoice->total_payment_without_balance,
            'total_payment_without_balance_rupiah'  => rupiah($invoice->total_payment_without_balance),
            'total_payment_with_balance'            => ($invoice->total_payment_without_balance - $invoice->total_payment),
            'total_payment_with_balance_rupiah'     => rupiah($invoice->total_payment_without_balance - $invoice->total_payment),
            'total_items_purchased'                 => $totalItemPurchased,
            'total_shipping_cost'                   => $totalShippingCost,
            'total_shipping_cost_rupiah'            => rupiah($totalShippingCost),
            'unique_code'                           => ($invoice->second_unique_code) ? $invoice->second_unique_code : $invoice->unique_code,
            'original_unique_code'                  => $invoice->unique_code
        ];

        // Transaction Type
        $row['payment_details_type'] = categoryTransaction($invoice->category_transaction);

        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function updateStatusImmovableObject($id)
    {
        // Check Transaction
        $transaction = Transaction::where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Check Transaction In Store
        if (auth()->user()->role_id == 1) {
            if ($transaction->store_id != auth()->user()->company_id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Transaksi ini bukan dari toko anda.'
                ]);
            }
        }

        // Check Transaction in account
        if (auth()->user()->role_id == 6) {
            if ($transaction->invoice->user_id != auth()->user()->id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Transaksi ini bukan dari akun anda.'
                ]);
            }
        }

        // Check Transaction is have a immovable_object
        $totalsImmovable = 0;
        $totalsMovable   = 0;

        foreach ($transaction->transactionDetails as $val) {
            if ($val->is_immovable_object == 1) {
                $totalsImmovable += 1;
            } else {
                $totalsMovable += 1;
            }
        }

        if ($totalsImmovable == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Transaksi tidak memiliki Produk Tanpa Pengiriman.'
            ]);
        }

        // Check Transaction is have movable_object
        if ($totalsMovable == 0) {
            $status = 4;
        } else {
            $status = $transaction->status;
        }

        $transaction->update([
            'status'                  => $status,
            'status_immovable_object' => 1
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil update data.',
            'data'      => $transaction
        ]);
    }
}
