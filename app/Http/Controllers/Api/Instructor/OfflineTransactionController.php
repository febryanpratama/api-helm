<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Account;
use App\BeginBalance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\OfflineTransactionRequest;
use App\Cart;
use App\Checkout;
use App\CheckoutDetail;
use App\Invoice;
use App\Transaction;
use App\TransactionDetails;
use App\Course;
use App\Journal;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class OfflineTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $invoice = Invoice::where(['user_id' => auth()->user()->id, 'is_offline_transaction' => 1])->latest()->get();

        // Custom Paginate
        $invoices = $this->paginate($invoice, 20, null, ['path' => $request->fullUrl()]);
        $data    = [];

        foreach($invoices as $val) {
            $row['id']              = $val->id;
            $row['inv_code']        = 'INV-'.$val->id;
            $row['customer_data']   = [
                                        'name'  => $val->customer_name_offline_transaction,
                                        'email' => $val->customer_email_offline_transaction,
                                        'phone' => $val->customer_telepon_offline_transaction
                                    ];
            $row['transaction_nominal']         = $val->total_payment;
            $row['transaction_nominal_rupiah']  = rupiah($val->total_payment);
            $row['status']                      = $val->status;
            $row['status_details']              = statusPayment($val->status);
            $row['payment_type']                = paymentType($val->payment_type);
            $row['bank_name']                   = $val->bank_name;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Transaksi.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $invoices->currentPage(),
                'from'              => 1,
                'last_page'         => $invoices->lastPage(),
                'next_page_url'     => $invoices->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $invoices->perPage(),
                'prev_page_url'     => $invoices->previousPageUrl(),
                'total'             => $invoices->total()
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
        // Validation
        $validated = Validator::make(request()->all(), [
            'payment_type' => 'required|in:1,2,6,7,8'
        ]);

        if ($validated->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validated->errors()->first()
            ];

            return response()->json($data);
        }

        // check begin balance
        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();

        // Initialize
        $getItems = Cart::where(['user_id' => auth()->user()->id, 'is_offline' => '1'])->get();
        $bankName = null;
        $noRek    = null;

        if (count($getItems) < 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data keranjang kosong.'
            ]);
        }

        // Initialize
        $totals = 0;

        foreach ($getItems as $val) {
            $totals += ($val->course->price_num * $val->qty);
        }

        // Check Total Pay
        if (request('total_pay')) {
            if (str_replace('.', '', request('total_pay')) < $totals) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Total Bayar kurang.'
                ]);
            }
        }

        if (request('payment_type') == 1 || request('payment_type') == 2) {
            // Initialize
            $bank     = explode('|', request('bank'));
            $bankName = $bank[0];
            $noRek    = $bank[1];
        }

        if ($totals == 0 && count($getItems) != 0) {
            // Initialize
            $statusTransaction  = 1;
            $statusPayment      = 1;
        } elseif ($totals == 0 && count($getItems) == 0) {
            // Initialize
            $statusTransaction  = 2;
            $statusPayment      = 2;
        } else {
            // Initialize
            $statusTransaction  = (request('status_payment')) ? request('status_payment') : 2;
            $statusPayment      = (request('status_payment')) ? request('status_payment') : 2;
        }

        // INV Code
        $invCode = '#INV'.date('Y').auth()->user()->company->ID.date('dHI');

        try {
            // Insert to Invoice
            $invoice = Invoice::create([
                'user_id'                               => auth()->user()->id,
                'total_payment'                         => $totals,
                'total_payment_original'                => $totals,
                'payment_type'                          => request('payment_type'),
                'total_shipping_cost'                   => 0,
                'transaction_fees'                      => 0,
                'bank_name'                             => $bankName,
                'no_rek'                                => $noRek,
                'unique_code'                           => null,
                'status'                                => 0,
                'expired_transaction'                   => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                'is_offline_transaction'                => 1,
                'customer_name_offline_transaction'     => request('customer_name'),
                'customer_email_offline_transaction'    => request('customer_email'),
                'customer_telepon_offline_transaction'  => request('customer_telepon'),
                'total_pay_offline_transaction'         => request('total_pay'),
                'change_offline_transaction'            => request('change'),
                'publisher_name_offline_transaction'    => request('publisher_name'),
                'card_nomor_offline_transaction'        => request('card_nomor')
            ]);

            if ($invoice) {
                try {
                    $transaction = Transaction::create([
                        'store_id'              => auth()->user()->company_id,
                        'invoice_id'            => $invoice->id,
                        'total_payment'         => $totals,
                        'expedition'            => null,
                        'service'               => null,
                        'service_description'   => null,
                        'shipping_cost'         => null,
                        'etd'                   => null,
                        'service_date'          => null
                    ]);

                    if ($transaction) {
                        try {
                            foreach ($getItems as $val) {
                                // Get Product Details
                                $product = Course::where('id', $val->course->id)->first();

                                // Initialize
                                $transactionDetails = TransactionDetails::create([
                                    'transaction_id'              => $transaction->id,
                                    'course_id'                   => $product->id,
                                    'course_name'                 => $product->name,
                                    'course_detail'               => $product->description,
                                    'thumbnail'                   => $product->thumbnail,
                                    'thumbnail_path'              => $product->thumbnail_path,
                                    'price_course'                => $product->price_num,
                                    'discount'                    => $product->discount,
                                    'slug'                        => $product->slug,
                                    'course_package_category'     => $product->course_package_category,
                                    'period_day'                  => $product->period_day,
                                    'start_time_min'              => $product->start_time_min,
                                    'end_time_min'                => $product->end_time_min,
                                    'back_payment_status'         => $product->back_payment_status,
                                    'is_immovable_object'         => $product->is_immovable_object,
                                    'course_category'             => ($product->courseCategory) ? $product->courseCategory->category_id : null,
                                    'price_course_after_discount' => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                                    'qty'                         => $val->qty,
                                    'weight'                      => $product->weight,
                                    'back_payment_status'         => $product->back_payment_status,
                                    'category_detail_inputs'      => null,
                                    'service_date'                => null
                                ]);

                                // save journal
                                $this->journal($begin_balance, $product, $transaction, $val, $request);


                                $val->delete();
                            }

                            // Initialize
                            $detailsInvoice = Invoice::with('transaction')->where('id', $invoice->id)->first();

                            return response()->json([
                                'status'    => 'success',
                                'message'   => 'Transaksi berhasil diproses.',
                                'data'      => $detailsInvoice
                            ]);
                        } catch (\Throwable $e) {
                            $response = $e->getMessage();
                        }
                    }
                } catch (\Throwable $e) {
                    $response = $e->getMessage();
                }
            }   
        } catch (\Throwable $e) {
            $response = $e->getMessage();
        }

        return response()->json([
            'status'    => 'error',
            'message'   => $response
        ]);
    }

    private function journal($begin_balance, $product, $transaction, $val, $request) {
        // Journal method beginbalance = 1  type (barang)
        if ($begin_balance && $begin_balance->Method == 1 && $product->course_package_category == 0) {
            $account_debit_1 = Account::where('CurrType', 'Cash In Hand')->first();
            $account_credit_1 = Account::where('CurrType', 'Sales Inventory')->first();
            $account_debit_2 = null;

            if ($product->discount && $product->discount > 0) {
                $account_debit_2 = Account::where('CurrType', 'Sales Discount')->first();
            }

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
                $debit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val->qty;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                // DEBIT 2
                if ($account_debit_2) { // check diskon
                    $acc_debit2['id'] = $account_debit_2->ID;
                    $acc_debit2['name'] = $account_debit_2->Name;
                    $acc_debit2['code'] = $account_debit_2->Code;
                    $acc_debit2['group'] = $account_debit_2->group;
                    $acc_debit2['type'] = $account_debit_2->CurrType;

                    $res_acc_debit2[] = $acc_debit2;


                    $debit2['id'] = $account_debit_2->ID;
                    $debit2['value'] = (($product->discount/100) * $product->price_num) * $val->qty;
                    $debit2['account'] = $res_acc_debit2;
                    $debit2_json[] = $debit2;
                    
                }

                // multi result bila banyak debit menggunakan merge
                $debit_json = $debit1_json;
                if (count($debit2_json) > 0) {
                    $debit_json1 = array_merge($debit1_json, $debit2_json);
                    $debit_json = $debit_json1;
                }

                // credit 1
                $acc_credit1['id'] = $account_credit_1->ID;
                $acc_credit1['name'] = $account_credit_1->Name;
                $acc_credit1['code'] = $account_credit_1->Code;
                $acc_credit1['group'] = $account_credit_1->group;
                $acc_credit1['type'] = $account_credit_1->CurrType;

                $res_acc_credit1[] = $acc_credit1;

                $credit1['id'] = $account_credit_1->ID;
                $credit1['value'] = $product->price_num * $val->qty;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => auth()->user()->company_id,
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Kas Barang Offline|' . $transaction->id . '|' . $product->name,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => auth()->user()->id,
                    'AddedByIP'             => $request->ip()
                ]);
            }
        }

        // Journal method beginbalance = 0  type (barang) && check product HPP
        if ($begin_balance && $begin_balance->Method == 0 && $product->course_package_category == 0 && $product->hpp) {
            $account_debit_1 = Account::where('CurrType', 'Cash In Hand')->first();
            $account_debit_2 = null;
            $account_debit_3 = Account::where('CurrType', 'COGS Inventory')->first();
            $account_credit_1 = Account::where('CurrType', 'Sales Inventory')->first();
            $account_credit_2 = Account::where('CurrType', 'Inventory RM')->first();

            if ($product->discount && $product->discount > 0) {
                $account_debit_2 = Account::where('CurrType', 'Sales Discount')->first();
            }

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
                $res_acc_credit2 = array();

                $credit1 = array();
                $credit2 = array();
                $credit3 = array();
                $credit4 = array();

                $credit_json = array();
                $credit1_json = array();
                $credit2_json = array();

                $res_doc = array();

                // debit 1
                $acc_debit1['id'] = $account_debit_1->ID;
                $acc_debit1['name'] = $account_debit_1->Name;
                $acc_debit1['code'] = $account_debit_1->Code;
                $acc_debit1['group'] = $account_debit_1->group;
                $acc_debit1['type'] = $account_debit_1->CurrType;
                $res_acc_debit1[] = $acc_debit1;

                $debit1['id'] = $account_debit_1->ID;
                $debit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val->qty;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                // DEBIT 2
                if ($account_debit_2) { // check diskon
                    $acc_debit2['id'] = $account_debit_2->ID;
                    $acc_debit2['name'] = $account_debit_2->Name;
                    $acc_debit2['code'] = $account_debit_2->Code;
                    $acc_debit2['group'] = $account_debit_2->group;
                    $acc_debit2['type'] = $account_debit_2->CurrType;

                    $res_acc_debit2[] = $acc_debit2;


                    $debit2['id'] = $account_debit_2->ID;
                    $debit2['value'] = (($product->discount/100) * $product->price_num) * $val->qty;
                    $debit2['account'] = $res_acc_debit2;
                    $debit2_json[] = $debit2;
                }

                // DEBIT 3
                $acc_debit3['id'] = $account_debit_3->ID;
                $acc_debit3['name'] = $account_debit_3->Name;
                $acc_debit3['code'] = $account_debit_3->Code;
                $acc_debit3['group'] = $account_debit_3->group;
                $acc_debit3['type'] = $account_debit_3->CurrType;
                $res_acc_debit3[] = $acc_debit3;

                $debit3['id'] = $account_debit_3->ID;
                $debit3['value'] = $product->hpp * $val->qty;
                $debit3['account'] = $res_acc_debit3;
                $debit3_json[] = $debit3;

                // multi result bila banyak debit menggunakan merge
                $debit_json = $debit1_json;
                if (count($debit2_json) > 0) {
                    $debit_json1 = array_merge($debit1_json, $debit2_json);
                    $debit_json = $debit_json1;
                }

                if (count($debit3_json) > 0) {
                    $debit_json2 = array_merge($debit_json, $debit3_json);
                    $debit_json = $debit_json2;
                }


                // credit 1
                $acc_credit1['id'] = $account_credit_1->ID;
                $acc_credit1['name'] = $account_credit_1->Name;
                $acc_credit1['code'] = $account_credit_1->Code;
                $acc_credit1['group'] = $account_credit_1->group;
                $acc_credit1['type'] = $account_credit_1->CurrType;

                $res_acc_credit1[] = $acc_credit1;

                $credit1['id'] = $account_credit_1->ID;
                $credit1['value'] = $product->price_num * $val->qty;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // credit 2 (HPP)
                $acc_credit2['id'] = $account_credit_2->ID;
                $acc_credit2['name'] = $account_credit_2->Name;
                $acc_credit2['code'] = $account_credit_2->Code;
                $acc_credit2['group'] = $account_credit_2->group;
                $acc_credit2['type'] = $account_credit_2->CurrType;

                $res_acc_credit2[] = $acc_credit2;

                $credit2['id'] = $account_credit_2->ID;
                $credit2['value'] = $product->hpp * $val->qty;
                $credit2['account'] = $res_acc_credit2;
                $credit2_json[] = $credit2;

                // multi result bila banyak credit menggunakan merge
                $credit_json = array_merge($credit1_json, $credit2_json);

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => auth()->user()->company_id,
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Kas Barang Offline|' . $transaction->id . '|' . $product->name,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => auth()->user()->id,
                    'AddedByIP'             => $request->ip()
                ]);
            }
        }

        // Journal method beginbalance = 0 atau 1 (sama aja)  type (jasa)
        if ($begin_balance && $product->course_package_category == 1) {
            $account_debit_1 = Account::where('CurrType', 'Cash In Hand')->first();
            $account_credit_1 = Account::where('CurrType', 'Income')->first();
            $account_debit_2 = null;

            // if ($product->discount && $product->discount > 0) {
            //     $account_debit_2 = Account::where('CurrType', 'Sales Discount')->first();
            // }

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
                $debit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val->qty;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                // DEBIT 2
                if ($account_debit_2) { // check diskon
                    $acc_debit2['id'] = $account_debit_2->ID;
                    $acc_debit2['name'] = $account_debit_2->Name;
                    $acc_debit2['code'] = $account_debit_2->Code;
                    $acc_debit2['group'] = $account_debit_2->group;
                    $acc_debit2['type'] = $account_debit_2->CurrType;

                    $res_acc_debit2[] = $acc_debit2;


                    $debit2['id'] = $account_debit_2->ID;
                    $debit2['value'] = (($product->discount/100) * $product->price_num) * $val->qty;
                    $debit2['account'] = $res_acc_debit2;
                    $debit2_json[] = $debit2;
                    
                }

                // multi result bila banyak debit menggunakan merge
                $debit_json = $debit1_json;
                if (count($debit2_json) > 0) {
                    $debit_json1 = array_merge($debit1_json, $debit2_json);
                    $debit_json = $debit_json1;
                }

                // credit 1
                $acc_credit1['id'] = $account_credit_1->ID;
                $acc_credit1['name'] = $account_credit_1->Name;
                $acc_credit1['code'] = $account_credit_1->Code;
                $acc_credit1['group'] = $account_credit_1->group;
                $acc_credit1['type'] = $account_credit_1->CurrType;

                $res_acc_credit1[] = $acc_credit1;

                $credit1['id'] = $account_credit_1->ID;
                $credit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val->qty;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => auth()->user()->company_id,
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Kas Jasa Offline|' . $transaction->id . '|' . $product->name,
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Initialize
        $invoice = Invoice::with('transaction')->where(['user_id' => auth()->user()->id, 'id' => $id])->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Get Transaction
        $transaction = Transaction::with('transactionDetails')->where('invoice_id', $invoice->id)->get();

        $data = [
            'id'                                    => $invoice->id,
            'inv_code'                              => 'INV-'.$invoice->id,
            'user_id'                               => $invoice->user_id,
            'total_payment'                         => $invoice->total_payment,
            'total_payment_original'                => $invoice->total_payment_original,
            'total_payment_rupiah'                  => rupiah($invoice->total_payment),
            'total_payment_without_balance'         => null,
            'total_shipping_cost'                   => $invoice->total_shipping_cost,
            'transaction_fees'                      => 0,
            'payment_type'                          => paymentType($invoice->payment_type),
            'bank_name'                             => null,
            'no_rek'                                => null,
            'unique_code'                           => null,
            'second_unique_code'                    => null,
            'status'                                => $invoice->status_payment,
            'status_details'                        => statusPayment($invoice->status_payment),
            'expired_transaction'                   => $invoice->expired_transaction,
            'category_transaction'                  => categoryTransaction($invoice->category_transaction),
            'is_offline_transaction'                => 1,
            'customer_name_offline_transaction'     => $invoice->customer_name_offline_transaction,
            'customer_email_offline_transaction'    => $invoice->customer_email_offline_transaction,
            'customer_telepon_offline_transaction'  => $invoice->customer_telepon_offline_transaction,
            'total_pay_offline_transaction'         => $invoice->total_pay_offline_transaction,
            'change_offline_transaction'            => $invoice->change_offline_transaction,
            'publisher_name_offline_transaction'    => $invoice->publisher_name_offline_transaction,
            'card_nomor_offline_transaction'        => $invoice->card_nomor_offline_transaction,
            'created_at'                            => $invoice->created_at,
            'updated_at'                            => $invoice->updated_at,
            'transaction'                           => $transaction
        ];

        return response()->json([
            'status'    => 'success',
            'message'   => 'Invoice tersedia',
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
    public function update(OfflineTransactionRequest $request, $id)
    {
        // Initialize
        $invoice = Invoice::where('id', $id)->first();

        if (!$invoice) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Invoice dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if (request('total_pay') < $invoice->total_payment) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Total Bayar kurang dari Total Tagihan.',
                'data'      => [
                    'total_tagihan'         => rupiah($invoice->total_payment),
                    'total_uang_dibayarkan' => rupiah(request('total_pay')),
                    'total_uang_kurang'     => rupiah(($invoice->total_payment - request('total_pay')))
                ]
            ]);
        }

        foreach($invoice->transaction as $val) {
            $val->update(['status' => 1]);
        }

        $invoice->update([
            'status'                        => 1,
            'total_pay_offline_transaction' => str_replace('.', '', request('total_pay')),
            'change_offline_transaction'    => ($invoice->total_payment - request('total_pay'))
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Transaksi berhasil diperbaharui.',
            'data'      => $invoice
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
