<?php

namespace App\Http\Controllers\Api\Seller\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\InventoryPurchases;
use App\InventoryPurchasesDetails;
use App\Cart;
use App\Account;
use App\Journal;
use App\BeginBalance;
use App\Course;
use Validator;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $invoices = InventoryPurchases::where(['user_id' => auth()->user()->id])->latest()->paginate(20);

        foreach ($invoices as $val) {
            $row['id']              = $val->id;
            $row['inv_code']        = $val->no_invoice;
            $row['customer_data']   = [
                                        'name'  => $val->customer_name,
                                        'email' => $val->customer_email,
                                        'phone' => $val->customer_telepon
                                    ];
            $row['transaction_nominal']         = $val->total_payment;
            $row['transaction_nominal_rupiah']  = rupiah($val->total_payment);
            $row['status']                      = $val->status;
            $row['status_details']              = statusPayment($val->status);
            $row['payment_type']                = paymentType($val->payment_type);
            $row['bank_name']                   = $val->bank_name;
            $row['payment_details_type']        = categoryTransaction($val->category_transaction);  

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
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
        $validator = Validator::make(request()->all(), [
            'payment_type'      => 'required|integer|in:6,9,10',
            'nomor_invoice'     => 'nullable',
            'proof_of_payment'  => 'nullable|mimes:jpg,png,jpeg',
            'path_file_receipt' => 'nullable|mimes:jpg,png,jpeg',
            'purchases_type'    => 'required|in:0,1',
            'is_termin'         => 'nullable|in:0,1'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        if (request('purchases_type') == 1) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'first_payment' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first()
                ];

                return response()->json($data);
            }
        }

        // Check Product From Cart
        $beginBalance   = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
        $carts          = Cart::where(['user_id' => auth()->user()->id, 'is_offline' => 1, 'is_inventory_purchases' => 1])->get();
        $pathPOP        = null;
        $pathReceipt    = null;
        $totals         = 0;

        if (!$beginBalance) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Begin Balance tidak tersedia untuk tanggal '.date('d F Y').' Silahkan buat data di fitur Begin Balance.'
            ]);
        }

        if (count($carts) == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Pilih minimal 1 Produk sebelum melakukan transaksi.'
            ]);
        }

        foreach($carts as $cart) {
            $product = Course::where('id', $cart->course_id)->first();

            if (!$product) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Produk dengan ID ('.$cart->course_id.') tidak ditemukan.'
                ]);

                break;
            }

            $totals += ($cart->course->price_num * $cart->qty);
        }

        // Check File Filled
        if (request('proof_of_payment')) {
            // Initialize
            $file = request()->file('proof_of_payment');
            $extF = $file->getClientOriginalExtension();

            // Check Max Size
            if ($file->getSize() > 5000000) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Max Size File ('.$file->getClientOriginalName().') 5 MB'
                ]);
            }

            $pathPOP = $file->store('uploads/inventory/purchases/'.auth()->user()->company->Name, 'public');
        }

        // Check File Filled
        if (request('path_file_receipt')) {
            // Initialize
            $file = request()->file('path_file_receipt');
            $extF = $file->getClientOriginalExtension();

            // Check Max Size
            if ($file->getSize() > 5000000) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Max Size File ('.$file->getClientOriginalName().') 5 MB'
                ]);
            }

            $pathReceipt = $file->store('uploads/inventory/purchases/'.auth()->user()->company->Name, 'public');
        }

        // Insert To Main Table
        try {
            // Inventory Purchases
            $inventoryPurchases = InventoryPurchases::create([
                'user_id'               => auth()->user()->id,
                'store_id'              => auth()->user()->company_id,
                'total_payment'         => $totals,
                'payment_type'          => request('payment_type'),
                'proof_of_payment'      => $pathPOP,
                'receipt'               => $pathReceipt,
                'nomor_invoice'         => request('nomor_invoice'),
                'status'                => 0,
                'is_termin'             => (request('is_termin')) ? request('is_termin') : 0,
                'customer_name'         => request('customer_name'),
                'customer_email'        => request('customer_email'),
                'customer_telepon'      => request('customer_telepon'),
                'total_pay'             => request('total_pay'),
                'publisher_name'        => request('publisher_name'),
                'card_nomor'            => request('card_nomor'),
                'is_termin'             => request('purchases_type'),
                'expired_transaction'   => date('Y-m-d H:i:s', strtotime('+6 hourse'))
            ]);

            if ($inventoryPurchases) {
                $totals = 0;

                foreach($carts as $val) {
                    // Get Product Details
                    $product = Course::where('id', $val->course_id)->first();

                    // Inventory Purchases Details
                    $inventoryPurchasesDetails = InventoryPurchasesDetails::create([
                        'inventory_purchases_id' => $inventoryPurchases->id,
                        'product_id'             => $val->course_id,
                        'product_details'        => json_encode($product),
                        'qty'                    => $val->qty
                    ]);

                    // Totals Payment
                    $totals += ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : $product->price_num;

                    // Save To Journals
                    $this->_manageDataJournal($beginBalance, $product, $inventoryPurchasesDetails, $val, $request);
                    // $val->delete();
                }

                $inventoryPurchases->update([
                    'total_payment' => $totals
                ]);

                $response = [
                    'status'    => 'success',
                    'message'   => 'Transaksi berhasil disimpan.',
                    'data'      => $inventoryPurchases
                ];
            }
        } catch (\Throwable $e) {
            // Initialize
            $response = [
                'status'    => 'error',
                'message'   => $e->getMessage().' At Line ('.$e->getLine().')'
            ];
        }
        
        return response()->json($response);
    }

    public function _manageDataJournal($beginBalance, $product, $inventoryPurchasesDetails, $val, $request) {
        // Initialize
        $data = null;

        if (request('purchases_type') == 0) {
            if ($beginBalance && $beginBalance->Method == 0 && $product->course_package_category == 0) { // Perpetual
                $this->directPerpetual($product, $inventoryPurchasesDetails, $val, $request);
            } elseif ($beginBalance && $beginBalance->Method == 1 && $product->course_package_category == 0) { // Periodik
                $this->directPeriodik($product, $inventoryPurchasesDetails, $val, $request);
            }
        } else {
            if ($beginBalance && $beginBalance->Method == 0 && $product->course_package_category == 0) { // Perpetual
                $this->indirectPerpetual($product, $inventoryPurchasesDetails, $val, $request);
            } elseif ($beginBalance && $beginBalance->Method == 1 && $product->course_package_category == 0) { // Periodik
                $this->indirectPeriodik($product, $inventoryPurchasesDetails, $val, $request);
            }
        }

        return $data;
    }

    private function directPerpetual($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Inventory RM')->first();
        $accountCredit1 = Account::where('CurrType', 'Cash in Hand')->first();
        $accountCredit2 = null;
        $priceDisc      = 0;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit2 = Account::where('CurrType', 'Purchase Discount')->first();
            $priceDisc      = ($product->price_num * $val->qty) - (discountFormula($product->discount, $product->price_num) * $val->qty);
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->price_num * $val->qty); // Purchase Price
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

            $debit_json = $debit1_json;

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = (($product->price_num * $val->qty) - $priceDisc); // Purchase Price - Discount
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Check Discount
            if ($accountCredit2) {
                $acc_credit2['id']    = $accountCredit2->ID;
                $acc_credit2['name']  = $accountCredit2->Name;
                $acc_credit2['code']  = $accountCredit2->Code;
                $acc_credit2['group'] = $accountCredit2->group;
                $acc_credit2['type']  = $accountCredit2->CurrType;

                $res_acc_credit2[] = $acc_credit2;

                $credit2['id']      = $accountCredit2->ID;
                $credit2['value']   = $priceDisc; // Total Discount
                $credit2['account'] = $res_acc_credit2;
                $credit2_json[]     = $credit2;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = $credit1_json;
            
            if (count($credit2_json) > 0) {
                $creditsJson = array_merge($credit1_json, $credit2_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembelian Kas Barang Langsung|'.$inventoryPurchasesDetails->id.'|'.$product->name,
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }
    }

    private function directPeriodik($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Purchase')->first();
        $accountCredit1 = Account::where('CurrType', 'Cash in Hand')->first();
        $accountCredit2 = null;
        $priceDisc      = 0;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit2 = Account::where('CurrType', 'Purchase Discount')->first();
            $priceDisc      = ($product->price_num * $val->qty) - (discountFormula($product->discount, $product->price_num) * $val->qty);
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->price_num * $val->qty); // Purchase Price
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

            $debit_json = $debit1_json;

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = (($product->price_num * $val->qty) - $priceDisc); // Purchase Price - Discount
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Check Discount
            if ($accountCredit2) {
                $acc_credit2['id']    = $accountCredit2->ID;
                $acc_credit2['name']  = $accountCredit2->Name;
                $acc_credit2['code']  = $accountCredit2->Code;
                $acc_credit2['group'] = $accountCredit2->group;
                $acc_credit2['type']  = $accountCredit2->CurrType;

                $res_acc_credit2[] = $acc_credit2;

                $credit2['id']      = $accountCredit2->ID;
                $credit2['value']   = $priceDisc; // Total Discount
                $credit2['account'] = $res_acc_credit2;
                $credit2_json[]     = $credit2;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = $credit1_json;
            
            if (count($credit2_json) > 0) {
                $creditsJson = array_merge($credit1_json, $credit2_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembelian Kas Barang Langsung|'.$inventoryPurchasesDetails->id.'|'.$product->name,
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }
    }

    private function indirectPerpetual($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Inventory RM')->first();
        $accountDebit2  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit2 = Account::where('CurrType', 'Cash In Hand')->first();
        $accountCredit3 = null;
        $priceDisc      = 0;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit3 = Account::where('CurrType', 'Purchase Discount')->first();
            $priceDisc      = ($product->price_num * $val->qty) - (discountFormula($product->discount, $product->price_num) * $val->qty);
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();
            $res_acc_credit3 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            $credit3_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->price_num * $val->qty); // Purchases Price
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

             // Debit 2
            $acc_debit2['id']       = $accountDebit2->ID;
            $acc_debit2['name']     = $accountDebit2->Name;
            $acc_debit2['code']     = $accountDebit2->Code;
            $acc_debit2['group']    = $accountDebit2->group;
            $acc_debit2['type']     = $accountDebit2->CurrType;
            $res_acc_debit2[]       = $acc_debit2;

            $debit2['id']           = $accountDebit2->ID;
            $debit2['value']        = $request->first_payment; // First Payment
            $debit2['account']      = $res_acc_debit2;
            $debit2_json[]          = $debit2;

            $debit_json = array_merge($debit1_json, $debit2_json);

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = (($product->price_num * $val->qty) - $priceDisc); // Purchase Price - Discount
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Credit 2
            $acc_credit2['id']      = $accountCredit2->ID;
            $acc_credit2['name']    = $accountCredit2->Name;
            $acc_credit2['code']    = $accountCredit2->Code;
            $acc_credit2['group']   = $accountCredit2->group;
            $acc_credit2['type']    = $accountCredit2->CurrType;

            $res_acc_credit2[] = $acc_credit2;

            $credit2['id']      = $accountCredit2->ID;
            $credit2['value']   = $request->first_payment; // First Payment
            $credit2['account'] = $res_acc_credit2;
            $credit2_json[]     = $credit2;

            // Check Discount
            if ($accountCredit3) {
                $acc_credit3['id']    = $accountCredit3->ID;
                $acc_credit3['name']  = $accountCredit3->Name;
                $acc_credit3['code']  = $accountCredit3->Code;
                $acc_credit3['group'] = $accountCredit3->group;
                $acc_credit3['type']  = $accountCredit3->CurrType;

                $res_acc_credit3[] = $acc_credit3;

                $credit3['id']      = $accountCredit3->ID;
                $credit3['value']   = $priceDisc; // Total Discount
                $credit3['account'] = $res_acc_credit3;
                $credit3_json[]     = $credit3;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = array_merge($credit1_json, $credit2_json);
            
            if (count($credit3_json) > 0) {
                $creditsJson = array_merge($credit_json, $credit3_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembayaran Pertama Kas Tidak Langsung|'.$inventoryPurchasesDetails->id.'|Pembayaran',
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }
    }

    public function indirectPeriodik($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Purchase')->first();
        $accountDebit2  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit2 = Account::where('CurrType', 'Cash in Hand')->first();
        $accountCredit3 = null;
        $priceDisc      = 0;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit3 = Account::where('CurrType', 'Purchase Discount')->first();
            $priceDisc      = ($product->price_num * $val->qty) - (discountFormula($product->discount, $product->price_num) * $val->qty);
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            $credit3_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->price_num * $val->qty); // Purchases Price
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

            // Debit 2
            $acc_debit2['id']       = $accountDebit2->ID;
            $acc_debit2['name']     = $accountDebit2->Name;
            $acc_debit2['code']     = $accountDebit2->Code;
            $acc_debit2['group']    = $accountDebit2->group;
            $acc_debit2['type']     = $accountDebit2->CurrType;
            $res_acc_debit2[]       = $acc_debit2;

            $debit2['id']           = $accountDebit2->ID;
            $debit2['value']        = $request->first_payment; // First Payment
            $debit2['account']      = $res_acc_debit2;
            $debit2_json[]          = $debit2;

            $debit_json = array_merge($debit1_json, $debit2_json);

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = ($product->price_num * $val->qty); // Purcahses Price
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Credit 2
            $acc_credit2['id']      = $accountCredit2->ID;
            $acc_credit2['name']    = $accountCredit2->Name;
            $acc_credit2['code']    = $accountCredit2->Code;
            $acc_credit2['group']   = $accountCredit2->group;
            $acc_credit2['type']    = $accountCredit2->CurrType;

            $res_acc_credit2[] = $acc_credit2;

            $credit2['id']      = $accountCredit2->ID;
            $credit2['value']   = $request->first_payment; // First Payment
            $credit2['account'] = $res_acc_credit2;
            $credit2_json[]     = $credit2;

            // Check Discount
            if ($accountCredit3) {
                $acc_credit3['id']    = $accountCredit3->ID;
                $acc_credit3['name']  = $accountCredit3->Name;
                $acc_credit3['code']  = $accountCredit3->Code;
                $acc_credit3['group'] = $accountCredit3->group;
                $acc_credit3['type']  = $accountCredit3->CurrType;

                $res_acc_credit3[] = $acc_credit3;

                $credit3['id']      = $accountCredit3->ID;
                $credit3['value']   = $priceDisc; // Total Discount
                $credit3['account'] = $res_acc_credit3;
                $credit2_json[]     = $credit3;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = array_merge($credit1_json, $credit2_json);
            
            if (count($credit3_json) > 0) {
                $creditsJson = array_merge($credit_json, $credit3_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembelian Kas Barang Tidak Langsung|'.$inventoryPurchasesDetails->id.'|'.$product->name,
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }
    }

    private function indirectPerpetualOLD($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Inventory RM')->first();
        $accountDebit2  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit2 = Account::where('CurrType', 'Cash In Hand')->first();
        $accountCredit3 = null;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit3 = Account::where('CurrType', 'Purchase Discount')->first();
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();
            $res_acc_credit3 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            $credit3_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->price_num * $val->qty); // Purchases Price
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

             // Debit 2
            $acc_debit2['id']       = $accountDebit2->ID;
            $acc_debit2['name']     = $accountDebit2->Name;
            $acc_debit2['code']     = $accountDebit2->Code;
            $acc_debit2['group']    = $accountDebit2->group;
            $acc_debit2['type']     = $accountDebit2->CurrType;
            $res_acc_debit2[]       = $acc_debit2;

            $debit2['id']           = $accountDebit2->ID;
            $debit2['value']        = ($product->discount && $product->discount > 0 ?
                                    discountFormula($product->discount, $product->price_num) :
                                    $product->price_num) * $val->qty;
            $debit2['account']      = $res_acc_debit2;
            $debit2_json[]          = $debit2;

            $debit_json = array_merge($debit1_json, $debit2_json);

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = $product->price_num * $val->qty;
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Credit 2
            $acc_credit2['id']      = $accountCredit2->ID;
            $acc_credit2['name']    = $accountCredit2->Name;
            $acc_credit2['code']    = $accountCredit2->Code;
            $acc_credit2['group']   = $accountCredit2->group;
            $acc_credit2['type']    = $accountCredit2->CurrType;

            $res_acc_credit2[] = $acc_credit2;

            $credit2['id']      = $accountCredit2->ID;
            $credit2['value']   = $product->price_num * $val->qty;
            $credit2['account'] = $res_acc_credit2;
            $credit2_json[]     = $credit2;

            // Check Discount
            if ($accountCredit3) {
                $acc_credit3['id']    = $accountCredit3->ID;
                $acc_credit3['name']  = $accountCredit3->Name;
                $acc_credit3['code']  = $accountCredit3->Code;
                $acc_credit3['group'] = $accountCredit3->group;
                $acc_credit3['type']  = $accountCredit3->CurrType;

                $res_acc_credit3[] = $acc_credit3;

                $credit3['id']      = $accountCredit3->ID;
                $credit3['value']   = ($product->discount && $product->discount > 0 ?
                                        discountFormula($product->discount, $product->price_num) :
                                        $product->price_num) * $val->qty;
                $credit3['account'] = $res_acc_credit3;
                $credit3_json[]     = $credit3;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = array_merge($credit1_json, $credit2_json);
            
            if (count($credit3_json) > 0) {
                $creditsJson = array_merge($credit_json, $credit3_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembelian Kas Barang Tidak Langsung|'.$inventoryPurchasesDetails->id.'|'.$product->name,
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }
    }

    private function indirectPeriodikOLD($product, $inventoryPurchasesDetails, $val, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Purchase')->first();
        $accountDebit2  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit2 = Account::where('CurrType', 'Cash in Hand')->first();
        $accountCredit3 = null;

        // Check Products Discount
        if ($product->discount && $product->discount > 0) {
            $accountCredit3 = Account::where('CurrType', 'Purchase Discount')->first();
        }

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $res_acc_debit2 = array();
            
            $debit1 = array();
            $debit2 = array();
            $debit3 = array();
            $debit4 = array();

            $debit_json  = array();
            $debit1_json = array();
            $debit2_json = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $res_acc_credit2 = array();

            $credit1 = array();
            $credit2 = array();
            $credit3 = array();
            $credit4 = array();

            $credit_json  = array();
            $credit1_json = array();
            $credit2_json = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($product->discount && $product->discount > 0 ?
                                    discountFormula($product->discount, $product->price_num) :
                                    $product->price_num) * $val->qty;
            $debit1['account']      = $res_acc_debit1;
            $debit1_json[]          = $debit1;

            // Debit 2
            $acc_debit2['id']       = $accountDebit2->ID;
            $acc_debit2['name']     = $accountDebit2->Name;
            $acc_debit2['code']     = $accountDebit2->Code;
            $acc_debit2['group']    = $accountDebit2->group;
            $acc_debit2['type']     = $accountDebit2->CurrType;
            $res_acc_debit2[]       = $acc_debit2;

            $debit2['id']           = $accountDebit2->ID;
            $debit2['value']        = ($product->discount && $product->discount > 0 ?
                                    discountFormula($product->discount, $product->price_num) :
                                    $product->price_num) * $val->qty;
            $debit2['account']      = $res_acc_debit2;
            $debit2_json[]          = $debit2;

            $debit_json = array_merge($debit1_json, $debit2_json);

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = $product->price_num * $val->qty;
            $credit1['account'] = $res_acc_credit1;
            $credit1_json[]     = $credit1;

            // Credit 2
            $acc_credit2['id']      = $accountCredit2->ID;
            $acc_credit2['name']    = $accountCredit2->Name;
            $acc_credit2['code']    = $accountCredit2->Code;
            $acc_credit2['group']   = $accountCredit2->group;
            $acc_credit2['type']    = $accountCredit2->CurrType;

            $res_acc_credit2[] = $acc_credit2;

            $credit2['id']      = $accountCredit2->ID;
            $credit2['value']   = $product->price_num * $val->qty;
            $credit2['account'] = $res_acc_credit2;
            $credit2_json[]     = $credit2;

            // Check Discount
            if ($accountCredit3) {
                $acc_credit3['id']    = $accountCredit3->ID;
                $acc_credit3['name']  = $accountCredit3->Name;
                $acc_credit3['code']  = $accountCredit3->Code;
                $acc_credit3['group'] = $accountCredit3->group;
                $acc_credit3['type']  = $accountCredit3->CurrType;

                $res_acc_credit3[] = $acc_credit3;

                $credit3['id']      = $accountCredit3->ID;
                $credit3['value']   = ($product->discount && $product->discount > 0 ?
                                        discountFormula($product->discount, $product->price_num) :
                                        $product->price_num) * $val->qty;
                $credit3['account'] = $res_acc_credit3;
                $credit2_json[]     = $credit3;
            }

            // Multi result bila banyak credit menggunakan merge
            $credit_json = array_merge($credit1_json, $credit2_json);
            
            if (count($credit3_json) > 0) {
                $creditsJson = array_merge($credit_json, $credit3_json);
                $credit_json = $creditsJson;
            }

            // Docs
            $doc['no']   = $inventoryPurchasesDetails->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembelian Kas Barang Tidak Langsung|'.$inventoryPurchasesDetails->id.'|'.$product->name,
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
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
        $purchase = InventoryPurchases::where('id', $id)->first();

        if (!$purchase) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan'
            ]);
        }

        // Initialize
        $purchaseDetails = [];

        foreach ($purchase->inventoryPurchasesDetails as $val) {
            // Initialize
            $row['id']                      = $val->id;
            $row['Inventory_purchases_id']  = $val->Inventory_purchases_id;
            $row['product_id']              = $val->product_id;
            $row['product_details']         = json_decode($val->product_details, true);
            $row['qty']                     = $val->qty;

            $purchaseDetails[] = $row;
        }

        $data = [
            'id'                                    => $purchase->id,
            'inv_code'                              => 'INV-'.$purchase->id,
            'user_id'                               => $purchase->user_id,
            'total_payment'                         => $purchase->total_payment,
            'total_payment_original'                => $purchase->total_payment_original,
            'total_payment_rupiah'                  => rupiah($purchase->total_payment),
            'total_payment_without_balance'         => null,
            'transaction_fees'                      => 0,
            'payment_type'                          => paymentType($purchase->payment_type),
            'bank_name'                             => null,
            'no_rek'                                => null,
            'status'                                => $purchase->status,
            'expired_transaction'                   => $purchase->expired_transaction,
            'customer_name'                         => $purchase->customer_name,
            'customer_email'                        => $purchase->customer_email,
            'customer_telepon'                      => $purchase->customer_telepon,
            'total_pay'                             => $purchase->total_pay,
            'change'                                => $purchase->change,
            'publisher_name'                        => $purchase->publisher_name,
            'card_nomor'                            => $purchase->card_nomor,
            'purchase_details'                      => $purchaseDetails,
            'created_at'                            => $purchase->created_at,
            'updated_at'                            => $purchase->updated_at
        ];

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }
}