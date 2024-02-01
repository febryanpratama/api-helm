<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Address;
use App\MasterLocation;
use App\Course;
use App\CourseTermin;
use App\Wallet;
use App\Invoice;
use App\InvoiceAddress;
use App\Transaction;
use App\TransactionDetails;
use App\Cart;
use App\AgreementLetter;
use App\BeginBalance;
use App\CourseTerminSchedule;
use App\WholesalePrice;
use App\QuestionDetailsTransaction;
use App\PendingWalletTransaction;
// use App\CourseTransactionTerminPayment;
use App\TransactionDetailsCustomDocumentInput;
use App\Http\Resources\CheckoutStoreResource;
use App\Journal;
use Carbon\Carbon;
use Notification;
use App\Notifications\GlobalNotification;
use App\User;

class CheckoutControllerDev extends Controller
{
    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /*
            Notes :
            1. Check Address
            2. Check Master Address
            3. Check Unique Code
            4. Insert to invoice table (Rekup Data Transaction)
            5. Insert to transaction table (Insert rekup data by store)
            6. Insert to transaction_details table (Insert products by store)
        */
        
        // Initialize
        $requestData     = request()->all();
        $isWalletPayment = 'unfulfilled';

        $auth_user = auth()->user();

        // validasi jika tanpa login
        if (isset($requestData['not_login'])) {
            $auth_user = User::where('email', $requestData['not_login']['email'])->first();

            if (!$auth_user) {
                $auth_user = User::create([
                    'email' => $requestData['not_login']['email'],
                    'name' => $requestData['not_login']['name'],
                    'role_id' => 6,
                    'password' => bcrypt(rand(1111,9999)),
                    'is_active' => 'y',
                    'referral_code' => $this->generateRandomString(6),
                ]);
            }

            $address_user = Address::where('details_address', $requestData['not_login']['address_detail'])->where('district_id', $requestData['not_login']['address_district_id'])->where('user_id', $auth_user->id)->first();

            if (!$address_user) {
                $address_user = Address::create([
                    'user_id' => $auth_user->id,
                    'district_id' => $requestData['not_login']['address_district_id'],
                    'details_address' => $requestData['not_login']['address_detail'],
                ]);
            }

            $requestData['address_id'] = $address_user->id;
        }

        // Validation
        if ($requestData['payment']['payment_type'] == null && $requestData['use_balance'] != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tipe Pembayaran harus diisi. (payment_type or use_balance)'
            ]);
        }

        // Check Wallet if use_balance true
        if ($requestData['payment']['payment_type'] == null && $requestData['use_balance'] == 1) {
            // Initialize
            $totalPay = $this->_totalFromAllProduct($requestData);

            if (isset($totalPay['status'])) {
                return response()->json($totalPay);
            }

            $wallet = Wallet::where('user_id', $auth_user->id)->sum('balance');

            if ($wallet < $totalPay) {
                return response()->json([
                   'status'    => 'error',
                   'message'   => 'Saldo tidak mencukupi untuk melakukan transaksi ini. Total Transaksi ('.rupiah($totalPay).') Saldo anda ('.rupiah($wallet).')'
                ]);
            }

           $isWalletPayment = 'fulfilled';
        }

        // Check Address
        $checkAddress = $this->_checkAddress($requestData);

        if (isset($requestData['product_type'])) {
            // Checking Data
            if ($requestData['product_type']['service']['termin']['store'] && count($requestData['product_type']['service']['termin']['store']) > 0) {
                $checkTerminService = $this->_checkTerminService($requestData);

                if (isset($checkTerminService['status']) && $checkTerminService['status'] == 'error') {
                    return response()->json($checkTerminService);
                }
            }

            // Product Type - Cash
            if ($requestData['product_type']['product']['cash']['store'] && count($requestData['product_type']['product']['cash']['store']) > 0) {
                // Initialize
                $checkCashProducts = $this->_checkCashProducts($requestData);

                if ($checkCashProducts['status'] == 'error') {
                    return response()->json($checkCashProducts);
                }

                $totals            = $checkCashProducts['totals'];
                $shippingCost      = $checkCashProducts['shipping_cost'];

                // Get Config Payment
                $configPayment      = $this->_configPayment($requestData, $totals, $shippingCost);
                $insertCashProducts = $this->_insertCashProducts($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user);
            }

            // Product Type - Termin
            if ($requestData['product_type']['product']['termin']['store'] && count($requestData['product_type']['product']['termin']['store']) > 0) {
                // Initialize
                $checkTerminProducts = $this->_checkTerminProducts($requestData);

                if ($checkTerminProducts['status'] == 'error') {
                    return response()->json($checkTerminProducts);
                }

                $totals              = $checkTerminProducts['totals'];
                $shippingCost        = $checkTerminProducts['shipping_cost'];

                // Get Config Payment
                $configPayment        = $this->_configPayment($requestData, $totals, $shippingCost);
                $insertTerminProducts = $this->_insertTerminProducts($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user);
            }
            
            // Service Type - Cash
            if ($requestData['product_type']['service']['cash']['store'] && count($requestData['product_type']['service']['cash']['store']) > 0) {
                // Initialize
                $checkCashService  = $this->_checkCashService($requestData);

                if ($checkCashService['status'] == 'error') {
                    return response()->json($checkCashService);
                }

                $totals            = $checkCashService['totals'];
                $shippingCost      = $checkCashService['shipping_cost'];

                // Get Config Payment
                $configPayment      = $this->_configPayment($requestData, $totals, $shippingCost);
                $insertCashService  = $this->_insertCashService($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user);
            }
            
            // Service Type - Termin
            if ($requestData['product_type']['service']['termin']['store'] && count($requestData['product_type']['service']['termin']['store']) > 0) {
                // Initialize
                $checkTerminService = $this->_checkTerminService($requestData);

                if ($checkTerminService['status'] == 'error') {
                    return response()->json($checkTerminService);
                }

                $totals             = $checkTerminService['totals'];
                $shippingCost       = $checkTerminService['shipping_cost'];

                // Get Config Payment
                $configPayment       = $this->_configPayment($requestData, $totals, $shippingCost);
                $insertTerminService = $this->_insertTerminService($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil melakukan checkout.'
        ]);
    }

    private function _checkAddress($requestData)
    {
        $address = Address::where('id', $requestData['address_id'])->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat pengiriman tidak ditemukan.'
            ]);
        }

        // Check Master Location
        $masterLocation = MasterLocation::where('id', $address->district_id)->first();

        return [
            'address'         => $address,
            'master_location' => $masterLocation
        ];
    }

    private function _totalFromAllProduct($requestData)
    {
        // Initialize
        $totals       = 0;
        $shippingCost = 0;

        if (isset($requestData['product_type'])) {
            if ($requestData['product_type']['product']['cash']['store'] && count($requestData['product_type']['product']['cash']['store']) > 0) {
                $checkCashProducts = $this->_checkCashProducts($requestData);

                if ($checkCashProducts['status'] == 'error') {
                    return $checkCashProducts;
                }

                $totals       += $checkCashProducts['totals'];
                $shippingCost += $checkCashProducts['shipping_cost'];
            }

            if ($requestData['product_type']['product']['termin']['store'] && count($requestData['product_type']['product']['termin']['store']) > 0) {
                $checkTerminProducts = $this->_checkTerminProducts($requestData);

                if ($checkTerminProducts['status'] == 'error') {
                    return $checkTerminProducts;
                }

                $totals       += $checkTerminProducts['totals'];
                $shippingCost += $checkTerminProducts['shipping_cost'];
            }

            if ($requestData['product_type']['service']['cash']['store'] && count($requestData['product_type']['service']['cash']['store']) > 0) {
                $checkCashService  = $this->_checkCashService($requestData);

                if ($checkCashService['status'] == 'error') {
                    return $checkCashService;
                }

                $totals       += $checkCashService['totals'];
                $shippingCost += $checkCashService['shipping_cost'];
            }

            if ($requestData['product_type']['service']['termin']['store'] && count($requestData['product_type']['service']['termin']['store']) > 0) {
                $checkTerminService = $this->_checkTerminService($requestData);

                if ($checkTerminService['status'] == 'error') {
                    return $checkTerminService;
                }

                $totals       += $checkTerminService['totals'];
                $shippingCost += $checkTerminService['shipping_cost'];
            }
        }

        return ($totals + $shippingCost);
    }

    private function _configPayment($requestData, $totals, $shippingCost)
    {
        // Initialize - Payment
        $bank               = $requestData['payment']['bank_name'];
        $noRek              = $requestData['payment']['no_rek'];
        $uniqueCode         = $this->checkUniqueCode();
        $status             = 0;
        $totalPay           = ($totals + $shippingCost + $uniqueCode) + $requestData['transaction_fees'];
        $totalPayOriginal   = ($totals + $shippingCost);
        $paymentType        = $requestData['payment']['payment_type'];
        $transactionFees    = $requestData['transaction_fees'];
        $totalShipping      = $shippingCost;
        
        return [
            'bank'               => $bank,
            'no_rek'             => $noRek,
            'total_pay'          => $totalPay,
            'total_pay_original' => $totalPayOriginal,
            'payment_type'       => $paymentType,
            'transaction_fees'   => $transactionFees,
            'total_shipping'     => $totalShipping,
            'unique_code'        => $uniqueCode,
            'status'             => $status
        ];
    }

    private function checkUniqueCode()
    {
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

        return $uniqueCode;
    }

    private function _invoice($configPayment, $invoiceType = '', $auth_user)
    {
        // Initialize
        $invoice = Invoice::create([
            'user_id'                => $auth_user->id,
            'total_payment'          => $configPayment['total_pay'],
            'total_payment_original' => $configPayment['total_pay_original'],
            'payment_type'           => $configPayment['payment_type'],
            'total_shipping_cost'    => $configPayment['total_shipping'],
            'transaction_fees'       => $configPayment['transaction_fees'],
            'bank_name'              => $configPayment['bank'],
            'no_rek'                 => $configPayment['no_rek'],
            'unique_code'            => $configPayment['unique_code'],
            'status'                 => $configPayment['status'],
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'invoice_type'           => $invoiceType
        ]);

        return $invoice;
    }

    private function _invoiceAddress($invoice, $checkAddress)
    {
        // Initialize
        $invoiceAddress = InvoiceAddress::create([
            'invoice_id'        => $invoice->id,
            'address_id'        => $checkAddress['address']['id'],
            'province'          => $checkAddress['master_location']['provinsi'],
            'city'              => $checkAddress['master_location']['kota'],
            'district'          => $checkAddress['master_location']['kecamatan'],
            'address_type'      => $checkAddress['master_location']['type'],
            'details_address'   => $checkAddress['address']['details_address']
        ]);

        return $invoiceAddress;
    }

    private function insertTransactionDetails($transaction, $qty, $product, $checkCart = '', $serviceDate = null)
    {
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
            'qty'                         => $qty,
            'weight'                      => $product->weight,
            'back_payment_status'         => $product->back_payment_status,
            'category_detail_inputs'      => ($checkCart) ? $checkCart->category_detail_inputs : null,
            'service_date'                => $serviceDate
        ]);

        return $transactionDetails;
    }

    // Cash Products
    private function _checkCashProducts($requestData)
    {
        /*
            Notes :
            1. Check Product Details (there or not)
            2. Check Stock Product
            3. Check Comparison of stock and qty
            4. Check Wholesale Price
                * Check Discount in Wholesale Price
            5. Check Discount if Wholesale Price not exists 
        */
        
        // Initialize
        $totals         = 0;
        $shippingCost   = 0;

        // Check Data
        foreach($requestData['product_type']['product']['cash']['store'] as $store) {
            // Initialize
            $shippingCost += ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;

            foreach($store['products'] as $productCheck) {
                // Initialize
                $product = Course::where('id', $productCheck['course_id'])->first();

                if (!$product) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk dengan ID ('.$productCheck['course_id'].') tidak ditemukan.'
                    ];

                    break;
                }

                // Check Stock
                if ($product->user_quota_join <= 0) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk '.$product->name.' sudah habis.'
                    ];

                    break;
                }

                // Check Stock and QTY
                if ($product->user_quota_join < $productCheck['qty']) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk ('.$product->name.') melebihi batas pembelian. Stok ('.$product->user_quota_join.') QTY ('.$productCheck['qty'].')'
                    ];

                    break;
                }

                // Check Wholesale
                if ($product->wholesalePrice) {
                    // Initialize
                    $dataMaxQtyArray     = [];
                    $latestMaxQty        = WholesalePrice::where('course_id', $product->id)->latest()->first();
                    $totalWholesalePrice = 0;

                    foreach($product->wholesalePrice as $key => $whos) {
                        if ($key > 0) {
                            array_push($dataMaxQtyArray, $whos->qty);
                        }
                    }

                    foreach($product->wholesalePrice as $key => $wholesale) {
                        // Check Totals Purchase
                        $minQtyReset = $product->wholesalePrice[$key]->qty;

                        if ($key > 0) {
                            $maxQtyReset = $dataMaxQtyArray[$key - 1];
                        } else {
                            $maxQtyReset = $dataMaxQtyArray[$key];
                        }

                        $minQty = $wholesale->qty;

                        if ($productCheck['qty'] >= $minQtyReset && $productCheck['qty'] <= $maxQtyReset) {
                            $totalWholesalePrice = $wholesale->price;

                            break;
                        } else if ($productCheck['qty'] >= $latestMaxQty->qty) {
                            $totalWholesalePrice = $latestMaxQty->price;

                            break;
                        }
                    }

                    // Check Discount in Wholesale Price
                    if ($totalWholesalePrice > 0) {
                        if ($product->discount > 0) {
                            // Initialize
                            $priceAfterDisc = discountFormula($product->discount, $totalWholesalePrice);
                            
                            $totals += ($priceAfterDisc * $productCheck['qty']);
                        } else {
                            // Initialize
                            $totals += ($totalWholesalePrice * $productCheck['qty']);
                        }
                    } else {
                        if ($product->discount > 0) {
                            // Initialize
                            $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                            $totals += ($priceAfterDisc * $productCheck['qty']);
                        } else {
                            // Initialize
                            $totals += ($product->price_num * $productCheck['qty']);
                        }
                    }
                } else {
                    // Check Discount
                    if ($product->discount > 0) {
                        // Initialize
                        $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                        $totals += ($priceAfterDisc * $productCheck['qty']);
                    } else {
                        $totals += ($product->price_num * $productCheck['qty']);
                    }
                }
            }
        }

        return [
            'status'        => 'success',
            'totals'        => $totals,
            'shipping_cost' => $shippingCost
        ];
    }

    private function _insertCashProducts($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user)
    {
        // Initialize
        $invoice = $this->_invoice($configPayment, '0', $auth_user);

        if ($invoice) {
            // Initialize
            $invoiceAddress = $this->_invoiceAddress($invoice, $checkAddress);
            $totalsPay      = ($configPayment['total_shipping'] + $configPayment['unique_code']);

            foreach($requestData['product_type']['product']['cash']['store'] as $store) {
                // Initialize
                $totalsPayByStore = 0;

                $transaction = Transaction::create([
                    'store_id'              => $store['store_id'],
                    'invoice_id'            => $invoice->id,
                    'total_payment'         => 0,
                    'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                    'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                    'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                    'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                    'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                    'service_date'          => null
                ]);

                if ($transaction) {
                    foreach($store['products'] as $val) {
                        // Initialize
                        $product    = Course::where('id', $val['course_id'])->first();
                        $checkCart  = Cart::where(['user_id' => $auth_user->id, 'course_id' => $product->id])->first();

                        // Create Transaction Details
                        $transactionDetails = $this->insertTransactionDetails($transaction, $val['qty'], $product, $checkCart);

                        // save journal
                        // check begin balance
                        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                        $this->journal($begin_balance, $product, $transaction, $val, $store, $auth_user);

                        // Check Question Details Transaction
                        if ($val['question_details_transaction'] != null && count($val['question_details_transaction']) > 0) {
                            foreach($val['question_details_transaction'] as $qdt) {
                                QuestionDetailsTransaction::create([
                                    'transaction_details_id' => $transactionDetails->id,
                                    'value'                  => $qdt['value']
                                ]);
                            }
                        }

                        // Check Wholesale
                        if ($product->wholesalePrice) {
                            // Initialize
                            $wholesalePriceFormula = $this->_wholesalePrice($product, $val);
                            $totalsPayByStore      += $wholesalePriceFormula;
                            $totalsPay             += $wholesalePriceFormula;
                        } else {
                            // Check Discount
                            if ($product->discount > 0) {
                                // Initialize
                                $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                                $totalsPayByStore += ($priceAfterDisc * $val['qty']);
                                $totalsPay        += ($priceAfterDisc * $val['qty']);
                            } else {
                                $totalsPayByStore += ($product->price_num * $val['qty']);
                                $totalsPay        += ($product->price_num * $val['qty']);
                            }
                        }

                        // Delete Data in Cart
                        $carts = Cart::where(['user_id' => $auth_user->id, 'course_id' => $val['course_id']])->delete();
                    }

                    // Initialize
                    $costPay = ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;

                    $transaction->update([
                        'total_payment' => ($totalsPayByStore + $costPay)
                    ]);

                    // Notification
                    $this->_checkoutNotfication($requestData, $invoice, $transaction, $auth_user);
                }
            }

            // If Order By Balance
            if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                Wallet::create([
                    'user_id'           => $auth_user->id,
                    'balance'           => (-($totalsPay)),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalsPay),
                    'details'           => 'Pembelian Produk - No Invoice (#INV-'.$invoice->id.')'
                ]);

                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'payment_type'       => 3,
                    'bank_name'          => 'Saldo',
                    'no_rek'             => '-',
                    'status'             => 1,
                    'second_unique_code' => substr(($totalsPay), -3)
                ]);
            } elseif ($requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] != null) {
                // Initialize
                $wallet              = Wallet::where('user_id', $auth_user->id)->sum('balance');
                $totalPayWithBalance = 0;

                if ($wallet > 0) {
                    // Initialize
                    $totalPayWithBalance = ($totalsPay - $wallet);

                    Wallet::create([
                        'user_id'           => $auth_user->id,
                        'balance'           => (-($wallet)),
                        'is_verified'       => 1,
                        'balance_type'      => 2,
                        'apps_commission'   => 0,
                        'original_balance'  => (-$wallet),
                        'details'           => 'Pembelian Produk - No Invoice (#INV-'.$invoice->id.')'
                    ]);
                }

                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'payment_type'                  => ($requestData['payment']['payment_type'] == 1) ? 4 : 5,
                    'total_payment'                 => ($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay,
                    'total_payment_without_balance' => $totalsPay,
                    'second_unique_code'            => substr((($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay), -3)
                ]);
            } else {
                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'second_unique_code' => substr(($totalsPay), -3)
                ]);
            }

            return $invoice;
        }
    }

    // Termin Products
    private function _checkTerminProducts($requestData)
    {
        /*
            Notes :
            1. Check Product Details (there or not)
            2. Check Stock Product
            3. Check Comparison of stock and qty
            4. Check Wholesale Price (Not yet supported)
            5. Check Negotiable and Termin Calculation
            6. Check Discount (Not yet supported)
        */
        
        // Initialize
        $totals         = 0;
        $shippingCost   = 0;

        // Check Data
        foreach($requestData['product_type']['product']['termin']['store'] as $store) {
            // Initialize
            $shippingCost += ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;

            foreach($store['products'] as $productCheck) {
                // Initialize
                $product = Course::where('id', $productCheck['course_id'])->first();

                if (!$product) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk dengan ID ('.$productCheck['course_id'].') tidak ditemukan.'
                    ];

                    break;
                }

                // Check Stock
                if ($product->user_quota_join <= 0) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk '.$product->name.' sudah habis.'
                    ];

                    break;
                }

                // Check Stock and QTY
                if ($product->user_quota_join < $productCheck['qty']) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk ('.$product->name.') melebihi batas pembelian. Stok ('.$product->user_quota_join.') QTY ('.$productCheck['qty'].')'
                    ];

                    break;
                }

                // Negotiable
                if ($product->is_termin == 1 && $productCheck['negotiable']['is_negotiable'] == 1) {
                    // Check Termin Calculation
                    if (isset($productCheck['negotiable']['termin']) && count($productCheck['negotiable']['termin']) > 0) {
                        // Check  json termin
                        $getSimulation      = finalTermin($product->id, $productCheck['qty']);
                        $get_total_termin_v = 0;

                        // Get total value termin product
                        foreach ($getSimulation as $tm) {
                            $get_total_termin_v += $tm['value_num'];
                        }

                        $get_total_termin_input = 0;
                        foreach ($productCheck['negotiable']['termin'] as $ter) { // get total value termin from input
                            $get_total_termin_input += $ter['value_num'];
                        }

                        // VALIDATION
                        if ($get_total_termin_input < $get_total_termin_v) {
                            $data = [
                                'status'    => 'error',
                                'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                'code'      => 400
                            ];
                    
                            return $data;
                        }
                    
                        if ($get_total_termin_input > $get_total_termin_v) {
                            $data = [
                                'status'    => 'error',
                                'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                'code'      => 400
                            ];

                            return $data;
                        }

                        // Get Down Payment
                        foreach ($productCheck['negotiable']['termin'] as $t => $ter) {
                            if ($t == 0) {
                                $totals += $ter['value_num'];

                                break;
                            }
                        }
                    }
                } elseif ($product->is_termin == 1 && $productCheck['negotiable']['is_negotiable'] == 0) { // No Negotiable
                    // Initialize
                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                    if ($courseTermin->is_percentage == 1) {
                        $dp_tot = ($courseTermin->down_payment/100) * $courseTermin->installment_amount;
                    } else {
                        $dp_tot = $courseTermin->down_payment;
                    }
                    
                    // pembayaran pertama hanya dp
                    $totals += $dp_tot * $productCheck['qty'];
                }
            }
        }

        return [
            'status'        => 'success',
            'totals'        => $totals,
            'shipping_cost' => $shippingCost
        ];
    }

    private function _insertTerminProducts($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user)
    {
        /*
            Notes :
            1. Notification for termin product (Not yet supported)
        */
        
        // Initialize
        $invoice = $this->_invoice($configPayment, '0', $auth_user);

        if ($invoice) {
            // Initialize
            $invoiceAddress = $this->_invoiceAddress($invoice, $checkAddress);
            $totalsPay      = ($configPayment['total_shipping'] + $configPayment['unique_code']);

            foreach($requestData['product_type']['product']['termin']['store'] as $store) {
                // Initialize
                $totalsPayByStore = 0;

                $transaction = Transaction::create([
                    'store_id'              => $store['store_id'],
                    'invoice_id'            => $invoice->id,
                    'total_payment'         => 0,
                    'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                    'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                    'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                    'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                    'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                    'service_date'          => null
                ]);

                if ($transaction) {
                    foreach($store['products'] as $val) {
                        // Initialize
                        $product    = Course::where('id', $val['course_id'])->first();
                        $checkCart  = Cart::where(['user_id' => $auth_user->id, 'course_id' => $product->id])->first();

                        // Create Transaction Details
                        $transactionDetails = $this->insertTransactionDetails($transaction, $val['qty'], $product, $checkCart);

                        // save journal
                        // check begin balance
                        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                        $this->journal($begin_balance, $product, $transaction, $val, $store, $auth_user);

                        // Check Question Details Transaction
                        if ($val['question_details_transaction'] != null && count($val['question_details_transaction']) > 0) {
                            foreach($val['question_details_transaction'] as $qdt) {
                                QuestionDetailsTransaction::create([
                                    'transaction_details_id' => $transactionDetails->id,
                                    'value'                  => $qdt['value']
                                ]);
                            }
                        }

                        // Check Termin
                        if ($product->is_termin == 1) {
                            // With Negotiable
                            if (isset($val['negotiable']['is_negotiable']) && $val['negotiable']['is_negotiable'] == 1) {
                                // Initialize
                                $finalTermin = finalTermin($product->id, $val['qty']);

                                $get_tot = 0;
                                foreach ($finalTermin as $tot) {
                                    $get_tot += $tot['value_num'];
                                }
                        
                                foreach ($finalTermin as $index => $ft) {
                                    // Check Payment Method
                                    $isVerifiedTermin = 0;

                                    if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                                        $isVerifiedTermin = 1;
                                    }

                                    // Initialize
                                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                                    $courseTerminS = CourseTerminSchedule::create([
                                        'course_id'                     => $product->id,
                                        'user_id'                       => $auth_user->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $val['negotiable']['termin'][$index]['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($val['negotiable']['termin'][$index]['due_date'])),
                                        // 'due_date'                      => null,
                                        'termin_percentage'             => ($ft['is_percentage'] == 0) ?
                                                                            rupiah($val['negotiable']['termin'][$index]['value_num']) :
                                                                            ($val['negotiable']['termin'][$index]['value_num']/$get_tot) * 100  . '%',
                                        'completion_percentage'         => $ft['completion_percentage'],
                                        'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                                        'due_date_description'          => $ft['due_date_description'],
                                        'duedate_number'                => $ft['duedate_number'],
                                        'duedate_name'                  => $ft['duedate_name'],
                                        'is_verified'                   => ($index == 0) ? $isVerifiedTermin : 0, 
                                        'is_percentage'                 => $ft['is_percentage'],
                                    ]);
                                }
                            } else { // Not Negotiable
                                // Initialize
                                $finalTermin = finalTermin($product->id, $val['qty']);
                        
                                foreach ($finalTermin as $index => $ft) {
                                    // Check Payment Method
                                    $isVerifiedTermin = 0;

                                    if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                                        $isVerifiedTermin = 1;
                                    }

                                    // Initialize
                                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();
                        
                                    $courseTerminS = CourseTerminSchedule::create([
                                        'course_id'                     => $product->id,
                                        'user_id'                       => $auth_user->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $ft['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($ft['due_date'])),
                                        // 'due_date'                      => null, // sementara settingan yg terbaru jadwal nya null
                                        'termin_percentage'             => $ft['termin_percentage'],
                                        'completion_percentage'         => $ft['completion_percentage'],
                                        'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                                        'due_date_description'          => $ft['due_date_description'],
                                        'duedate_number'                => $ft['duedate_number'],
                                        'duedate_name'                  => $ft['duedate_name'],
                                        'is_verified'                   => ($index == 0) ? $isVerifiedTermin : 0, 
                                        'is_percentage'                 => $ft['is_percentage'],
                                    ]);
                                }
                            }

                            // Initialize
                            $terminScheduleDetails = CourseTerminSchedule::where([
                                                        'user_id'                       => $auth_user->id,
                                                        'course_id'                     => $product->id,
                                                        'course_transaction_detail_id'  => $transactionDetails->id
                                                    ])
                                                    ->first();
                            
                            $totalsPayByStore += $terminScheduleDetails->value;
                            $totalsPay        += $terminScheduleDetails->value;
                        } else {
                            $totalsPayByStore += $product->price_num * $val['qty'];
                            $totalsPay        += $product->price_num * $val['qty'];
                        }

                        // Delete Data in Cart
                        $carts = Cart::where(['user_id' => $auth_user->id, 'course_id' => $val['course_id']])->delete();
                    }

                    // Initialize
                    $costPay = ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;
                    
                    $transaction->update([
                        'total_payment' => ($totalsPayByStore + $costPay)
                    ]);

                    // Notification
                    $this->_checkoutNotfication($requestData, $invoice, $transaction, $auth_user);
                }
            }

            // If Order By Balance
            if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                Wallet::create([
                    'user_id'           => $auth_user->id,
                    'balance'           => (-($totalsPay)),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalsPay),
                    'details'           => 'Pembelian Produk - No Invoice (#INV-'.$invoice->id.')'
                ]);

                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'payment_type'       => 3,
                    'bank_name'          => 'Saldo',
                    'no_rek'             => '-',
                    'status'             => 1,
                    'second_unique_code' => substr(($totalsPay), -3),
                ]);
            } elseif ($requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] != null) {
                // Initialize
                $wallet              = Wallet::where('user_id', $auth_user->id)->sum('balance');
                $totalPayWithBalance = 0;

                if ($wallet > 0) {
                    // Initialize
                    $totalPayWithBalance = ($totalsPay - $wallet);

                    $dataWallet = Wallet::create([
                        'user_id'           => $auth_user->id,
                        'balance'           => (-($wallet)),
                        'is_verified'       => 1,
                        'balance_type'      => 2,
                        'apps_commission'   => 0,
                        'original_balance'  => (-$wallet),
                        'details'           => 'Pembelian Produk - No Invoice (#INV-'.$invoice->id.')'
                    ]);

                    // Pending Wallet Transaction
                    PendingWalletTransaction::create([
                        'user_id'    => $auth_user->id,
                        'wallet_id'  => $dataWallet->id,
                        'invoice_id' => $invoice->id,
                        'total'      => $wallet
                    ]);

                    // Update Invoice
                    Invoice::where('id', $invoice->id)->update([
                        'payment_type'                  => ($requestData['payment']['payment_type'] == 1) ? 4 : 5,
                        'total_payment'                 => ($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay,
                        'total_payment_without_balance' => $totalsPay,
                        'second_unique_code'            => substr(($totalPayWithBalance), -3),
                        'is_termin'                     => 1
                    ]);
                }
            } else {
                // Check Exist is_termin
                Invoice::where('id', $invoice->id)->update([
                    'second_unique_code' => substr(($totalsPay), -3),
                    'is_termin'          => 1
                ]);
            }
            
            return $invoice;
        }
    }

    // Cash Service
    private function _checkCashService($requestData)
    {
        // Initialize
        $totals       = 0;
        $shippingCost = null;

        // Check Data
        foreach($requestData['product_type']['service']['cash']['store'] as $store) {
            foreach($store['products'] as $productCheck) {
                // Initialize
                $product = Course::where('id', $productCheck['course_id'])->first();

                if (!$product) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk dengan ID ('.$productCheck['course_id'].') tidak ditemukan.'
                    ];

                    break;
                }

                // Check Stock
                // if ($product->user_quota_join <= 0) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jasa '.$product->name.' sudah mencapai limit kuota.'
                //     ];

                //     break;
                // }

                // Check Stock and QTY
                // if ($product->user_quota_join < $productCheck['qty']) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jasa ('.$product->name.') melebihi batas pembelian. Stok ('.$product->user_quota_join.') QTY ('.$val['qty'].')'
                //     ];

                //     break;
                // }

                // Config check Jasa
                $replaceSmbl = str_replace('-', '/', $productCheck['service']['date']);
                $convertDate = date('Y-m-d', strtotime($replaceSmbl));
                $get_day     = Carbon::createFromFormat('Y-m-d', $convertDate)->translatedFormat('l');
                $product_day = explode(',', strtolower($product->period_day));
                $start_time  = $product->start_time_min;
                $end_time    = $product->end_time_min;
                $select_time = $productCheck['service']['time'];
                $check_day   = in_array(strtolower($get_day), $product_day);
                $time_valid  = false;

                if (!$check_day) { // check ketersedian jasa hari nya
                    return [
                        'status'    => 'error',
                        'message'   => 'Tanggal Hari yang dipilih ('. $get_day .') tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk hari ' . $product->period_day . '.'
                    ];
                }

                // check if time jasa null (24 jam)
                if (!$start_time && !$end_time) {
                    $time_valid = true;
                }

                if ($start_time < $select_time && $end_time > $select_time) { // check selected time valid
                    $time_valid = true;
                }

                // Check time not valid
                // if (!$time_valid) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jam yang dipilih tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk Jam ' . $product->start_time_min . ' - ' . $product->end_time_min . '.'
                //     ];
                // }

                // Check Discount
                if ($product->discount > 0) {
                    // Initialize
                    $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                    $totals += ($priceAfterDisc * $productCheck['qty']);
                } else {
                    $totals += ($product->price_num * $productCheck['qty']);
                }
            }
        }

        return [
            'status'        => 'success',
            'totals'        => $totals,
            'shipping_cost' => $shippingCost
        ];
    }

    private function _insertCashService($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user)
    {
        // Initialize
        $invoice = $this->_invoice($configPayment, '1', $auth_user);

        if ($invoice) {
            // Initialize
            $invoiceAddress = $this->_invoiceAddress($invoice, $checkAddress);
            $totalsPay      = ($configPayment['unique_code']);

            foreach($requestData['product_type']['service']['cash']['store'] as $store) {
                // Initialize
                $totalsPayByStore = 0;

                $transaction = Transaction::create([
                    'store_id'      => $store['store_id'],
                    'invoice_id'    => $invoice->id,
                    'total_payment' => 0
                ]);

                if ($transaction) {
                    foreach($store['products'] as $val) {
                        // Initialize
                        $product        = Course::where('id', $val['course_id'])->first();
                        $checkCart      = Cart::where(['user_id' => $auth_user->id, 'course_id' => $product->id])->first();
                        $select_date    = isset($val['service']) ? $val['service']['date'] : null;
                        $select_time    = isset($val['service']) ? $val['service']['time'] : null;
                        $serviceDateVal = $select_date ? $select_date . ' ' . $select_time : null;

                        // Create Transaction Details
                        $transactionDetails = $this->insertTransactionDetails($transaction, $val['qty'], $product, $checkCart, $serviceDateVal);

                        // save journal
                        // check begin balance
                        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                        $this->journal($begin_balance, $product, $transaction, $val, $store, $auth_user);

                        // Check Question Details Transaction
                        if ($val['question_details_transaction'] != null && count($val['question_details_transaction']) > 0) {
                            foreach($val['question_details_transaction'] as $qdt) {
                                QuestionDetailsTransaction::create([
                                    'transaction_details_id' => $transactionDetails->id,
                                    'value'                  => $qdt['value']
                                ]);
                            }
                        }

                        // Custom Document Input
                        if (isset($val['custom_document_input']) && count($val['custom_document_input']) > 0) {
                            TransactionDetailsCustomDocumentInput::create([
                                'transaction_details_id'    => $transactionDetails->id,
                                'value'                     => json_encode($val['custom_document_input'])
                            ]);
                        }

                        // Check Discount
                        if ($product->discount > 0) {
                            // Initialize
                            $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                            $totalsPay        += ($priceAfterDisc * $val['qty']);
                            $totalsPayByStore += ($priceAfterDisc * $val['qty']);
                        } else {
                            $totalsPay        += ($product->price_num * $val['qty']);
                            $totalsPayByStore += ($product->price_num * $val['qty']);
                        }

                        // Delete Data in Cart
                        $carts = Cart::where(['user_id' => $auth_user->id, 'course_id' => $val['course_id']])->delete();
                    }

                    $transaction->update([
                        'total_payment' => ($totalsPayByStore)
                    ]);

                    // Notification
                    $this->_checkoutNotfication($requestData, $invoice, $transaction, $auth_user);
                }
            }

            // If Order By Balance
            if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                $dataWallet = Wallet::create([
                    'user_id'           => $auth_user->id,
                    'balance'           => (-($totalsPay)),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalsPay),
                    'details'           => 'Transaksi Jasa - No Invoice (#INV-'.$invoice->id.')'
                ]);

                // Pending Wallet Transaction
                PendingWalletTransaction::create([
                    'user_id'    => $auth_user->id,
                    'wallet_id'  => $dataWallet->id,
                    'invoice_id' => $invoice->id,
                    'total'      => $totalsPay
                ]);

                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'payment_type'       => 3,
                    'bank_name'          => 'Saldo',
                    'no_rek'             => '-',
                    'second_unique_code' => substr(($totalsPay), -3),
                    'is_service'         => 1
                ]);
            } elseif ($requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] != null) {
                // Initialize
                $wallet              = Wallet::where('user_id', $auth_user->id)->sum('balance');
                $totalPayWithBalance = 0;

                if ($wallet > 0) {
                    // Initialize
                    $totalPayWithBalance = ($totalsPay - $wallet);

                    $dataWallet = Wallet::create([
                        'user_id'           => $auth_user->id,
                        'balance'           => (-($wallet)),
                        'is_verified'       => 1,
                        'balance_type'      => 2,
                        'apps_commission'   => 0,
                        'original_balance'  => (-$wallet),
                        'details'           => 'Transaksi Jasa - No Invoice (#INV-'.$invoice->id.')'
                    ]);

                    // Pending Wallet Transaction
                    PendingWalletTransaction::create([
                        'user_id'    => $auth_user->id,
                        'wallet_id'  => $dataWallet->id,
                        'invoice_id' => $invoice->id,
                        'total'      => $wallet
                    ]);
                    
                    // Update Invoice
                    Invoice::where('id', $invoice->id)->update([
                        'payment_type'                  => ($requestData['payment']['payment_type'] == 1) ? 4 : 5,
                        'total_payment'                 => ($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay,
                        'total_payment_without_balance' => $totalsPay,
                        'second_unique_code'            => substr((($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay), -3),
                        'is_service'                    => 1
                    ]);
                }
            } else {
                // Check Exist is_termin
                Invoice::where('id', $invoice->id)->update([
                   'second_unique_code' => substr(($totalsPay), -3),
                   'is_service'         => 1
                ]);
            }
            
            return $invoice;
        }
    }

    // Termin Service
    private function _checkTerminService($requestData)
    {
        // Initialize
        $totals       = 0;
        $shippingCost = null;

        // Check Data
        foreach($requestData['product_type']['service']['termin']['store'] as $store) {
            foreach($store['products'] as $productCheck) {
                // Initialize
                $product = Course::where('id', $productCheck['course_id'])->first();

                if (!$product) {
                    return [
                        'status'    => 'error',
                        'message'   => 'Produk dengan ID ('.$productCheck['course_id'].') tidak ditemukan.'
                    ];
                }

                // Check Stock
                // if ($product->user_quota_join <= 0) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jasa '.$product->name.' sudah mencapai limit kuota.'
                //     ];
                // }

                // Check Stock and QTY
                // if ($product->user_quota_join < $productCheck['qty']) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jasa ('.$product->name.') melebihi batas pembelian. Stok ('.$product->user_quota_join.') QTY ('.$val['qty'].')'
                //     ];
                // }

                // Config check Jasa
                $replaceSmbl = str_replace('-', '/', $productCheck['service']['date']);
                $convertDate = date('Y-m-d', strtotime($replaceSmbl));
                $get_day     = Carbon::createFromFormat('Y-m-d', $convertDate)->translatedFormat('l');
                $product_day = explode(',', strtolower($product->period_day));
                $start_time  = $product->start_time_min;
                $end_time    = $product->end_time_min;
                $select_time = $productCheck['service']['time'];
                $check_day   = in_array(strtolower($get_day), $product_day);
                $time_valid  = false;

                if (!$check_day) { // check ketersedian jasa hari nya
                    return [
                        'status'    => 'error',
                        'message'   => 'Tanggal Hari yang dipilih ('. $get_day .') tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk hari ' . $product->period_day . '.'
                    ];
                }

                // check if time jasa null (24 jam)
                if (!$start_time && !$end_time) {
                    $time_valid = true;
                }

                if ($start_time < $select_time && $end_time > $select_time) { // check selected time valid
                    $time_valid = true;
                }

                // Check time not valid
                // if (!$time_valid) {
                //     return [
                //         'status'    => 'error',
                //         'message'   => 'Jam yang dipilih tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk Jam ' . $product->start_time_min . ' - ' . $product->end_time_min . '.'
                //     ];
                // }

                // CHECK TERMIN
                if (isset($productCheck['negotiable']['is_negotiable']) && $productCheck['negotiable']['is_negotiable'] == 1) {
                    // Check Termin Calculation
                    if (isset($productCheck['negotiable']['termin']) && count($productCheck['negotiable']['termin']) > 0) { // check  json termin
                        // Initialize
                        $getSimulation      = finalTermin($product->id, $productCheck['qty']);
                        $get_total_termin_v = 0;
                        
                        foreach ($getSimulation as $tm) { // get total value termin product
                            $get_total_termin_v += $tm['value_num'];
                        }

                        $get_total_termin_input = 0;
                        foreach ($productCheck['negotiable']['termin'] as $ter) { // get total value termin from input
                            $get_total_termin_input += $ter['value_num'];
                        }

                        // VALIDATION
                        if ($get_total_termin_input < $get_total_termin_v) {
                            $data = [
                                'status'    => 'error',
                                'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                'code'      => 400
                            ];
                    
                            return $data;
                        }
                    
                        if ($get_total_termin_input > $get_total_termin_v) {
                            $data = [
                                'status'    => 'error',
                                'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                'code'      => 400
                            ];
                    
                            return $data;
                        }

                        // Get Down Payment
                        foreach ($productCheck['negotiable']['termin'] as $t => $ter) {
                            if ($t == 0) {
                                $totals += $ter['value_num'];

                                break;
                            }
                        }
                    }
                } elseif ($product->is_termin == 1 && $productCheck['negotiable']['is_negotiable'] == 0) { // No Negotiable
                    // Initialize
                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                    if ($courseTermin->is_percentage == 1) {
                        $dp_tot = ($courseTermin->down_payment/100) * $courseTermin->installment_amount;
                    } else {
                        $dp_tot = $courseTermin->down_payment;
                    }

                    // pembayaran pertama hanya dp
                    $totals += $dp_tot * $productCheck['qty'];
                }

                // Check Discount - Not Supported
                // if ($product->discount > 0) {
                //     // Initialize
                //     $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                //     $totals += ($priceAfterDisc * $productCheck['qty']);
                // } else {
                //     $totals += ($product->price_num * $productCheck['qty']);
                // }
            }
        }

        return [
            'status'        => 'success',
            'totals'        => $totals,
            'shipping_cost' => $shippingCost
        ];
    }

    private function _insertTerminService($requestData, $configPayment, $checkAddress, $isWalletPayment, $auth_user)
    {
        // Initialize
        $invoice = $this->_invoice($configPayment, '1', $auth_user);

        if ($invoice) {
            // Initialize
            $invoiceAddress = $this->_invoiceAddress($invoice, $checkAddress);
            $totalsPay      = ($configPayment['unique_code']);

            foreach($requestData['product_type']['service']['termin']['store'] as $store) {
                // Initialize
                $totalsPayByStore = 0;

                $transaction = Transaction::create([
                    'store_id'      => $store['store_id'],
                    'invoice_id'    => $invoice->id,
                    'total_payment' => 0
                ]);

                if ($transaction) {
                    foreach($store['products'] as $val) {
                        // Initialize
                        $product        = Course::where('id', $val['course_id'])->first();
                        $checkCart      = Cart::where(['user_id' => $auth_user->id, 'course_id' => $product->id])->first();
                        $select_date    = isset($val['service']) ? $val['service']['date'] : null;
                        $select_time    = isset($val['service']) ? $val['service']['time'] : null;
                        $serviceDateVal = $select_date ? $select_date . ' ' . $select_time : null;
                        
                        // Create Transaction Details
                        $transactionDetails = $this->insertTransactionDetails($transaction, $val['qty'], $product, $checkCart, $serviceDateVal);

                        // save journal
                        // check begin balance
                        $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                        $this->journal($begin_balance, $product, $transaction, $val, $store, $auth_user);

                        // Check Question Details Transaction
                        if ($val['question_details_transaction'] != null && count($val['question_details_transaction']) > 0) {
                            foreach($val['question_details_transaction'] as $qdt) {
                                QuestionDetailsTransaction::create([
                                    'transaction_details_id' => $transactionDetails->id,
                                    'value'                  => $qdt['value']
                                ]);
                            }
                        }

                        // Custom Document Input
                        if (isset($val['custom_document_input']) && count($val['custom_document_input']) > 0) {
                            TransactionDetailsCustomDocumentInput::create([
                                'transaction_details_id'    => $transactionDetails->id,
                                'value'                     => json_encode($val['custom_document_input'])
                            ]);
                        }

                        // Check Termin
                        if ($product->is_termin == 1) {
                            // With Negotiable
                            if (isset($val['negotiable']['is_negotiable']) && $val['negotiable']['is_negotiable'] == 1) {
                                // Initialize
                                $finalTermin = finalTermin($product->id, $val['qty']);

                                $get_tot = 0;
                                foreach ($finalTermin as $tot) {
                                    $get_tot += $tot['value_num'];
                                }
                        
                                foreach ($finalTermin as $index => $ft) {
                                    // Initialize
                                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                                    $courseTerminS = CourseTerminSchedule::create([
                                        'course_id'                     => $product->id,
                                        'user_id'                       => $auth_user->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $val['negotiable']['termin'][$index]['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($val['negotiable']['termin'][$index]['due_date'])),
                                        // 'due_date'                      => null,
                                        'termin_percentage'             => ($ft['is_percentage'] == 0) ?
                                                                            rupiah($val['negotiable']['termin'][$index]['value_num']) :
                                                                            ($val['negotiable']['termin'][$index]['value_num']/$get_tot) * 100  . '%',
                                        'completion_percentage'         => $ft['completion_percentage'],
                                        'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                                        'due_date_description'          => $ft['due_date_description'],
                                        'duedate_number'                => $ft['duedate_number'],
                                        'duedate_name'                  => $ft['duedate_name'],
                                        'is_verified'                   => 0, 
                                        'is_percentage'                 => $ft['is_percentage'],
                                    ]);
                                }
                            } else { // Not Negotiable
                                // Initialize
                                $finalTermin = finalTermin($product->id, $val['qty']);
                        
                                foreach ($finalTermin as $index => $ft) {
                                    // Initialize
                                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();
                        
                                    $courseTerminS = CourseTerminSchedule::create([
                                        'course_id'                     => $product->id,
                                        'user_id'                       => $auth_user->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $ft['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($ft['due_date'])),
                                        // 'due_date'                      => null,
                                        'termin_percentage'             => $ft['termin_percentage'],
                                        'completion_percentage'         => $ft['completion_percentage'],
                                        'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                                        'due_date_description'          => $ft['due_date_description'],
                                        'duedate_number'                => $ft['duedate_number'],
                                        'duedate_name'                  => $ft['duedate_name'],
                                        'is_verified'                   => 0, 
                                        'is_percentage'                 => $ft['is_percentage'],
                                    ]);
                                }
                            }

                            // Initialize
                            $terminScheduleDetails = CourseTerminSchedule::where([
                                                        'user_id'                       => $auth_user->id,
                                                        'course_id'                     => $product->id,
                                                        'course_transaction_detail_id'  => $transactionDetails->id
                                                    ])
                                                    ->first();
                            
                            $totalsPayByStore += $terminScheduleDetails->value;
                            $totalsPay        += $terminScheduleDetails->value;
                        } else {
                            $totalsPayByStore += $product->price_num * $val['qty'];
                            $totalsPay        += $product->price_num * $val['qty'];
                        }

                        // Delete Data in Cart
                        $carts = Cart::where(['user_id' => $auth_user->id, 'course_id' => $val['course_id']])->delete();
                    }

                    $transaction->update([
                        'total_payment' => ($totalsPayByStore)
                    ]);

                    // Notification
                    $this->_checkoutNotfication($requestData, $invoice, $transaction, $auth_user);
                }
            }

            // If Order By Balance
            if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] == null) {
                $dataWallet = Wallet::create([
                    'user_id'           => $auth_user->id,
                    'balance'           => (-($totalsPay)),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalsPay),
                    'details'           => 'Transaksi Jasa - No Invoice (#INV-'.$invoice->id.')'
                ]);

                // Pending Wallet Transaction
                PendingWalletTransaction::create([
                    'user_id'    => $auth_user->id,
                    'wallet_id'  => $dataWallet->id,
                    'invoice_id' => $invoice->id,
                    'total'      => $totalsPay
                ]);

                // Update Invoice
                Invoice::where('id', $invoice->id)->update([
                    'payment_type'       => 3,
                    'bank_name'          => 'Saldo',
                    'no_rek'             => '-',
                    'second_unique_code' => substr(($totalsPay), -3),
                    'is_termin'          => 1,
                    'is_service'         => 1
                ]);
            } elseif ($requestData['use_balance'] == 1 && $requestData['payment']['payment_type'] != null) {
                // Initialize
                $wallet              = Wallet::where('user_id', $auth_user->id)->sum('balance');
                $totalPayWithBalance = 0;

                if ($wallet > 0) {
                    // Initialize
                    $totalPayWithBalance = ($totalsPay - $wallet);

                    $dataWallet = Wallet::create([
                        'user_id'           => $auth_user->id,
                        'balance'           => (-($wallet)),
                        'is_verified'       => 1,
                        'balance_type'      => 2,
                        'apps_commission'   => 0,
                        'original_balance'  => (-$wallet),
                        'details'           => 'Transaksi Jasa - No Invoice (#INV-'.$invoice->id.')'
                    ]);

                    // Pending Wallet Transaction
                    PendingWalletTransaction::create([
                        'user_id'    => $auth_user->id,
                        'wallet_id'  => $dataWallet->id,
                        'invoice_id' => $invoice->id,
                        'total'      => $wallet
                    ]);
                    
                    // Update Invoice
                    Invoice::where('id', $invoice->id)->update([
                        'payment_type'                  => ($requestData['payment']['payment_type'] == 1) ? 4 : 5,
                        'total_payment'                 => ($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay,
                        'total_payment_without_balance' => $totalsPay,
                        'second_unique_code'            => substr((($totalPayWithBalance > 0) ? $totalPayWithBalance : $totalsPay), -3),
                        'is_termin'                     => 1,
                        'is_service'                    => 1
                    ]);
                }
            } else {
                // Check Exist is_termin
                Invoice::where('id', $invoice->id)->update([
                    'second_unique_code' => substr(($totalsPay), -3),
                    'is_termin'          => 1,
                    'is_service'         => 1
                ]);
            }
            
            return $invoice;
        }
    }

    private function _wholesalePrice($product, $productCheck)
    {
        // Initialize
        $dataMaxQtyArray     = [];
        $latestMaxQty        = WholesalePrice::where('course_id', $product->id)->latest()->first();
        $totalWholesalePrice = 0;

        foreach($product->wholesalePrice as $key => $whos) {
            if ($key > 0) {
                array_push($dataMaxQtyArray, $whos->qty);
            }
        }

        foreach($product->wholesalePrice as $key => $wholesale) {
            // Check Totals Purchase
            $minQtyReset = $product->wholesalePrice[$key]->qty;

            if ($key > 0) {
                $maxQtyReset = $dataMaxQtyArray[$key - 1];
            } else {
                $maxQtyReset = $dataMaxQtyArray[$key];
            }

            $minQty = $wholesale->qty;

            if ($productCheck['qty'] >= $minQtyReset && $productCheck['qty'] <= $maxQtyReset) {
                $totalWholesalePrice = $wholesale->price;

                break;
            } else if ($productCheck['qty'] >= $latestMaxQty->qty) {
                $totalWholesalePrice = $latestMaxQty->price;

                break;
            }
        }

        // Check Discount in Wholesale Price
        if ($totalWholesalePrice > 0) {
            if ($product->discount > 0) {
                // Initialize
                $priceAfterDisc = discountFormula($product->discount, $totalWholesalePrice);
                
                $totalsPayByStore = ($priceAfterDisc * $productCheck['qty']);
            } else {
                // Initialize
                $totalsPayByStore = ($totalWholesalePrice * $productCheck['qty']);
            }
        } else {
            if ($product->discount > 0) {
                // Initialize
                $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                $totalsPayByStore = ($priceAfterDisc * $productCheck['qty']);
            } else {
                // Initialize
                $totalsPayByStore = ($product->price_num * $productCheck['qty']);
            }
        }

        return $totalsPayByStore;
    }

    private function _checkoutNotfication($requestData, $invoice, $transaction, $auth_user)
    {
        // Notification
        $dateNotif = date('d-m-Y H:i', strtotime('+22 hourse'));

        // Check use Wallet or Not
        if ($requestData['use_balance'] == 1 && !$requestData['payment']['payment_type']) {
            // Initialize
            $sellerMessage = $auth_user->name.' Telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') menggunakan saldo.';
            $buyerMessage  = 'Anda telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') menggunakan saldo.';
        } else {
            // Initialize
            $sellerMessage = $auth_user->name.' Telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.'). Status Pembayaran menunggu transfer.';
            $buyerMessage  = 'Anda telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') segera lakukan pembayaran sebelum '.$dateNotif;
        }

        // * For Seller
        $recipient      = $transaction->company->user;
        $receiverId     = '0';
        $title          = 'Transaksi Baru';
        $code           = '03';
        $data           = [
            'transaction_id'        => $transaction->id,
            'type_notif'            => 'transaksi',
            'transaction_details'   => [
                'invoice_id'        => $invoice->id,
                'transaction_id'    => $transaction->id,
                'store_id'          => $transaction->store_id,
                'user_id'           => $invoice->user_id,
                'tab'               => 0
            ]
        ];
        $icon           = '';

        Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $sellerMessage, $data, $icon));

        // * For Buyer
        $recipient      = $auth_user;
        $receiverId     = '0';
        $title          = 'Transaksi Baru';
        $code           = '03';
        $data           = [
            'transaction_id'        => $transaction->id,
            'type_notif'            => 'transaksi',
            'transaction_details'   => [
                'invoice_id'        => $invoice->id,
                'transaction_id'    => $transaction->id,
                'store_id'          => $transaction->store_id,
                'user_id'           => $invoice->user_id,
                'tab'               => 0
            ]
        ];
        $icon           = '';

        Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $buyerMessage, $data, $icon));
    }

    private function journal($begin_balance, $product, $transaction, $val, $store, $auth_user) {
        // init
        $ship_cost = $transaction->shipping_cost ? $transaction->shipping_cost : 0;
        // Journal method beginbalance = 1  type (barang)
        if ($begin_balance && $begin_balance->Method == 1 && $product->course_package_category == 0) {
            $account_debit_1 = Account::where('CurrType', 'Account Receivable')->first();
            $account_debit_2 = Account::where('CurrType', 'Freight Expenses')->first();
            $account_debit_3 = null;
            $account_credit_1 = Account::where('CurrType', 'Sales Inventory')->first();
            $account_credit_2 = Account::where('CurrType', 'Account Payable')->first();

            if ($product->discount && $product->discount > 0) {
                $account_debit_3 = Account::where('CurrType', 'Sales Discount')->first();
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
                $debit3_json = array();

                // CREDIT
                $res_acc_credit1 = array();

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
                $debit1['value'] = (($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val['qty']) + $ship_cost;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                // debit 2 (ONGKIR)
                $acc_debit2['id'] = $account_debit_2->ID;
                $acc_debit2['name'] = $account_debit_2->Name;
                $acc_debit2['code'] = $account_debit_2->Code;
                $acc_debit2['group'] = $account_debit_2->group;
                $acc_debit2['type'] = $account_debit_2->CurrType;
                $res_acc_debit2[] = $acc_debit2;

                $debit2['id'] = $account_debit_2->ID;
                $debit2['value'] = $ship_cost;
                $debit2['account'] = $res_acc_debit2;
                $debit2_json[] = $debit2;

                // DEBIT 3
                if ($account_debit_3) { // check diskon
                    $acc_debit3['id'] = $account_debit_3->ID;
                    $acc_debit3['name'] = $account_debit_3->Name;
                    $acc_debit3['code'] = $account_debit_3->Code;
                    $acc_debit3['group'] = $account_debit_3->group;
                    $acc_debit3['type'] = $account_debit_3->CurrType;

                    $res_acc_debit3[] = $acc_debit3;


                    $debit3['id'] = $account_debit_3->ID;
                    $debit3['value'] = (($product->discount/100) * $product->price_num) * $val['qty'];
                    $debit3['account'] = $res_acc_debit3;
                    $debit3_json[] = $debit3;
                    
                }

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
                $credit1['value'] = ($product->price_num * $val['qty']) + $ship_cost;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // credit 2
                $acc_credit2['id'] = $account_credit_2->ID;
                $acc_credit2['name'] = $account_credit_2->Name;
                $acc_credit2['code'] = $account_credit_2->Code;
                $acc_credit2['group'] = $account_credit_2->group;
                $acc_credit2['type'] = $account_credit_2->CurrType;

                $res_acc_credit2[] = $acc_credit2;

                $credit2['id'] = $account_credit_2->ID;
                $credit2['value'] = $ship_cost;
                $credit2['account'] = $res_acc_credit2;
                $credit2_json[] = $credit2;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;
                if (count($credit2_json) > 0) {
                    $credit_json1 = array_merge($credit1_json, $credit2_json);
                    $credit_json = $credit_json1;
                }

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => $store['store_id'],
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Tidak Langsung Kas Barang Online|' . $transaction->id . '|' . $product->name,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => $auth_user->id,
                    'AddedByIP'             => $_SERVER['REMOTE_ADDR']
                ]);
            }
        }

        // Journal method beginbalance = 0  type (barang) && check product HPP
        if ($begin_balance && $begin_balance->Method == 0 && $product->course_package_category == 0 && $product->hpp) {
            $account_debit_1 = Account::where('CurrType', 'Account Receivable')->first();
            $account_debit_2 = Account::where('CurrType', 'COGS Inventory')->first();
            $account_debit_3 = Account::where('CurrType', 'Freight Expenses')->first();
            $account_debit_4 = null;
            $account_credit_1 = Account::where('CurrType', 'Sales Inventory')->first();
            $account_credit_2 = Account::where('CurrType', 'Inventory RM')->first();
            $account_credit_3 = Account::where('CurrType', 'Account Payable')->first();

            if ($product->discount && $product->discount > 0) {
                $account_debit_4 = Account::where('CurrType', 'Sales Discount')->first();
            }

            if ($account_debit_1 && $account_credit_1) {
                // init 

                // DEBIT
                $res_acc_debit1 = array();
                $res_acc_debit2 = array();
                $res_acc_debit3 = array();
                $res_acc_debit4 = array();
                
                $debit1 = array();
                $debit2 = array();
                $debit3 = array();
                $debit4 = array();

                $debit_json = array();
                $debit1_json = array();
                $debit2_json = array();
                $debit3_json = array();
                $debit4_json = array();

                // CREDIT
                $res_acc_credit1 = array();
                $res_acc_credit2 = array();
                $res_acc_credit3 = array();
                $res_acc_credit4 = array();

                $credit1 = array();
                $credit2 = array();
                $credit3 = array();
                $credit4 = array();

                $credit_json = array();
                $credit1_json = array();
                $credit2_json = array();
                $credit3_json = array();

                $res_doc = array();

                // debit 1
                $acc_debit1['id'] = $account_debit_1->ID;
                $acc_debit1['name'] = $account_debit_1->Name;
                $acc_debit1['code'] = $account_debit_1->Code;
                $acc_debit1['group'] = $account_debit_1->group;
                $acc_debit1['type'] = $account_debit_1->CurrType;
                $res_acc_debit1[] = $acc_debit1;

                $debit1['id'] = $account_debit_1->ID;
                $debit1['value'] = (($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val['qty']) + $ship_cost;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                // DEBIT 2 (COGS)
                $acc_debit2['id'] = $account_debit_2->ID;
                $acc_debit2['name'] = $account_debit_2->Name;
                $acc_debit2['code'] = $account_debit_2->Code;
                $acc_debit2['group'] = $account_debit_2->group;
                $acc_debit2['type'] = $account_debit_2->CurrType;
                $res_acc_debit2[] = $acc_debit2;

                $debit2['id'] = $account_debit_2->ID;
                $debit2['value'] = $product->hpp * $val['qty'];
                $debit2['account'] = $res_acc_debit2;
                $debit2_json[] = $debit2;

                // DEBIT 3 (ONGKIR)
                $acc_debit3['id'] = $account_debit_3->ID;
                $acc_debit3['name'] = $account_debit_3->Name;
                $acc_debit3['code'] = $account_debit_3->Code;
                $acc_debit3['group'] = $account_debit_3->group;
                $acc_debit3['type'] = $account_debit_3->CurrType;
                $res_acc_debit3[] = $acc_debit3;

                $debit3['id'] = $account_debit_3->ID;
                $debit3['value'] = $ship_cost;
                $debit3['account'] = $res_acc_debit3;
                $debit3_json[] = $debit3;


                // DEBIT 4 (DISKON)
                if ($account_debit_4) { // check diskon
                    $acc_debit4['id'] = $account_debit_4->ID;
                    $acc_debit4['name'] = $account_debit_4->Name;
                    $acc_debit4['code'] = $account_debit_4->Code;
                    $acc_debit4['group'] = $account_debit_4->group;
                    $acc_debit4['type'] = $account_debit_4->CurrType;

                    $res_acc_debit4[] = $acc_debit4;


                    $debit4['id'] = $account_debit_4->ID;
                    $debit4['value'] = (($product->discount/100) * $product->price_num) * $val['qty'];
                    $debit4['account'] = $res_acc_debit4;
                    $debit4_json[] = $debit4;
                }

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

                if (count($debit4_json) > 0) {
                    $debit_json3 = array_merge($debit_json, $debit4_json);
                    $debit_json = $debit_json3;
                }


                // credit 1
                $acc_credit1['id'] = $account_credit_1->ID;
                $acc_credit1['name'] = $account_credit_1->Name;
                $acc_credit1['code'] = $account_credit_1->Code;
                $acc_credit1['group'] = $account_credit_1->group;
                $acc_credit1['type'] = $account_credit_1->CurrType;

                $res_acc_credit1[] = $acc_credit1;

                $credit1['id'] = $account_credit_1->ID;
                $credit1['value'] = ($product->price_num * $val['qty']) + $ship_cost;
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
                $credit2['value'] = $product->hpp * $val['qty'];
                $credit2['account'] = $res_acc_credit2;
                $credit2_json[] = $credit2;

                // credit 3 (Ongkir)
                $acc_credit2['id'] = $account_credit_3->ID;
                $acc_credit3['name'] = $account_credit_3->Name;
                $acc_credit3['code'] = $account_credit_3->Code;
                $acc_credit3['group'] = $account_credit_3->group;
                $acc_credit3['type'] = $account_credit_3->CurrType;

                $res_acc_credit3[] = $acc_credit3;

                $credit3['id'] = $account_credit_3->ID;
                $credit3['value'] = $ship_cost;
                $credit3['account'] = $res_acc_credit3;
                $credit3_json[] = $credit3;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;
                if (count($credit2_json) > 0) {
                    $credit_json1 = array_merge($credit1_json, $credit2_json);
                    $credit_json = $credit_json1;
                }
                if (count($credit3_json) > 0) {
                    $credit_json2 = array_merge($credit_json, $credit3_json);
                    $credit_json = $credit_json2;
                }
                

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => $store['store_id'],
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Tidak Langsung Kas Barang Online|' . $transaction->id . '|' . $product->name,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => $auth_user->id,
                    'AddedByIP'             => $_SERVER['REMOTE_ADDR']
                ]);
            }
        }

        // Journal method beginbalance = 0 atau 1 (sama aja)  type (jasa)
        if ($begin_balance && $product->course_package_category == 1) {
            $account_debit_1 = Account::where('CurrType', 'Account Receivable')->first();
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
                $debit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val['qty'];
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
                    $debit2['value'] = (($product->discount/100) * $product->price_num) * $val['qty'];
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
                $credit1['value'] = ($product->discount && $product->discount > 0 ? discountFormula($product->discount, $product->price_num) : $product->price_num) * $val['qty'];
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                // multi result bila banyak credit menggunakan merge
                $credit_json = $credit1_json;

                // Docs
                $doc['no'] = $transaction->id;
                $doc['file'] = null;
                $res_doc[] = $doc;
                

                $journal = Journal::create([
                    'IDCompany'             => $store['store_id'],
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Tidak Langsung Kas Jasa Online|' . $transaction->id . '|' . $product->name,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => $auth_user->id,
                    'AddedByIP'             => $_SERVER['REMOTE_ADDR']
                ]);
            }
        }
    }

    private function _totalTermin($transactions)
    {
        // Initialize
        $totalTermin = 0;

        foreach ($transactions as $transaction) {
            foreach($transaction->transactionDetails as $val) {
                if ($val->course->is_termin == 1 && $val->course->courseTermin) {
                    foreach ($val->terminSchedules as $key => $schedule) {
                        if ($key == 0) { // get DP
                            $totalTermin += $schedule->value;
                        } else {
                            break;
                        }
                    }
                } else {
                    if ($val->course->is_termin == 0) {
                        $totalTermin += 0;
                    }
                }
            }
        }

        return $totalTermin;
    }
}
