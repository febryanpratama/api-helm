<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Notifications\GlobalNotification;
use Illuminate\Http\Request;
use App\Course;
use App\User;
use App\Checkout;
use App\CheckoutDetail;
use App\Majors;
use App\MajorsSubject;
use App\UserCourse;
use App\TheoryLock;
use App\Cart;
use App\CourseQuota;
use App\CourseTermin;
use App\CourseTerminSchedule;
use App\CourseTransactionTerminPayment;
use App\HistoryTransfer;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\StudentCheckoutStoreResource;
use Notification;
use Chat;
use DB;
use App\Wallet;
use App\Address;
use App\MasterLocation;
use Validator;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        // Validation
        $validated = Validator::make(request()->all(), [
            'address_id' => 'required',
            'expedition' => 'required'
        ]);

        if ($validated->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validated->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request('course_id') && request('buy_now')) {
            // Validation
            if (request('payment_type')) {
                // Validation
                $validated = Validator::make(request()->all(), [
                    'payment_type'  => 'required',
                    'bank'          => 'required'
                ]);

                if ($validated->fails()) {
                    $data = [
                        'status'    => 'error',
                        'message'   => $validated->errors()->first(),
                        'code'      => 400
                    ];

                    return response()->json($data, 400);
                }
            }

            if (!request('use_balance') && !request('payment_type')) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Select one method payment (payment_type or use_balance)'
                ]);
            }

            // Single Order
            $response = $this->_singleOrder($request->all());

            return $response;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Checkout Berhasil'
        ]);
    }

    private function _singleOrder($data)
    {
        /*
            Notes :
            * Check Product
            * Check Address
            * Check Unique Code
         */
        
        // Validation
        $validated = Validator::make(request()->all(), [
            'course_id'     => 'required',
            'qty'           => 'required'
        ]);

        if ($validated->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validated->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }
        
        // Initialize
        $totals          = 0;
        $nowDate         = date('Y-m-d H:i:s');
        $isWalletPayment = 'not fulfilled';
        $bank            = explode('|', request('bank'));
        $wallet          = Wallet::where('user_id', auth()->user()->id)->sum('balance');
        $dateNotif       = date('d-m-Y H:i', strtotime('+22 hourse'));

        // Check Product
        $course = Course::where('id', request('course_id'))->first();

        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Produk tidak ditemukan.'
            ]);
        }

        if ($course->user_quota_join <= 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Produk sudah habis.'
            ]);
        }

        // Check Balance
        if (request('use_balance') && request('payment_type')) {
            if ($wallet < 2000) {
                return response([
                    'status'    => 'error',
                    'message'   => 'Saldo minimal Rp.2000'
                ]);
            }
        }

        if (request('use_balance') && !request('payment_type')) {
            if ($course->is_termin == 1 && request('is_termin') == 1) {
                // Initialize
                $finalTermin = finalTermin($course->id);
                $totalPayT   = 0;

                foreach ($finalTermin as $index => $ft) {
                    if ($index <= 1) {
                        $totalPayT += $ft['value_num'];
                    } else {
                        break;
                    }
                }

                if ($totalPayT > ($wallet + 1000)) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Saldo tidak mencukupi untuk melakukan pembelian produk. Harga produk dengan Termin (Angsuran) ('.rupiah($totalPayT).') Saldo anda ('.rupiah($wallet).')'
                    ]);
                }

                // Initialize
                $isWalletPayment = 'fulfilled';
            } else {
                // Discount
                $fixedPrice = $course->price_num;

                if ($course->discount > 0) {
                    // Initialize
                    $fixedPrice = discountFormula($course->discount, $course->price_num);
                }

                if ($fixedPrice > ($wallet + 1000)) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Saldo tidak mencukupi untuk melakukan pembelian produk. Harga total produk ('.rupiah(($course->price_num * request('qty'))).') Saldo anda ('.rupiah($wallet).')'
                    ]);
                }

                // Initialize
                $isWalletPayment = 'fulfilled';
            }
        }

        // Check Address
        $address = Address::where('id', request('address_id'))->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat pengiriman tidak ditemukan.'
            ]);
        }

        // Initialize
        $masterLocation = MasterLocation::where('id', $address->district_id)->first();
        $uniqueCode     = rand(100, 1000);

        // Check Exists Unique Code
        $uniqueCodeExists = Checkout::where([
                            'unique_code'        => $uniqueCode,
                            'status_transaction' => 0
                        ])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode     = rand(100, 1000);
                $uniqueCodeExists = Checkout::where([
                                    'unique_code'        => $uniqueCode,
                                    'status_transaction' => 0
                                ])->whereDate('expired_transaction', '>=', $nowDate)
                ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        // Initialize
        $totalPayment       = (($course->price_num * request('qty')) + request('shipping_cost')) + $uniqueCode;
        $totalPaymentOri    = (($course->price_num * request('qty')) + request('shipping_cost'));
        $paymentType        = (request('use_balance') && $isWalletPayment == 'fulfilled') ? 3 : request('payment_type');
        $bankName           = (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[0];
        $noRek              = (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[1];
        $statusTransaction  = (request('use_balance') && $isWalletPayment == 'fulfilled') ? 1 : 0;
        $statusPayment      = (request('use_balance') && $isWalletPayment == 'fulfilled') ? 1 : 0;

        if (request('use_balance') && request('payment_type')) {
            // Initialize
            $totalPayment    = ((($course->price_num * request('qty')) + request('shipping_cost')) - $wallet) + $uniqueCode;
            $totalPaymentOri = (($course->price_num * request('qty')) + request('shipping_cost')) - $wallet;
            
            if (request('payment_type') == 1) {
                // Initialize
                $paymentType = 4;
            } else {
                // Initialize
                $paymentType = 5;
            }

            // Initialize
            $statusTransaction  = 0;
            $statusPayment      = 0;
            $bankName           = $bank[0];
            $noRek              = $bank[1];
        }

        // Check Discount
        if ($course->discount > 0) {
            // Initialize
            $totalPayment    = (discountFormula($course->discount, ($course->price_num * request('qty')))) + $uniqueCode;
            $totalPaymentOri = discountFormula($course->discount, ($course->price_num * request('qty')));
        }

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $totalPayment,
            'total_payment_original' => $totalPaymentOri,
            'payment_type'           => $paymentType,
            'bank_name'              => $bankName,
            'no_rek'                 => $noRek,
            'unique_code'            => $uniqueCode,
            'second_unique_code'     => substr(($totalPayment + $uniqueCode), -3),
            'status_transaction'     => $statusTransaction,
            'status_payment'         => $statusPayment,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'buy_now'                => 1,
            'address_id'             => request('address_id'),
            'province'               => $masterLocation->provinsi,
            'city'                   => $masterLocation->kota,
            'district'               => $masterLocation->kecamatan,
            'address_type'           => $masterLocation->type
        ]);

        if ($checkout) {
            // Initialize
            $expedition = json_decode(request('expedition'), true);

            // Create Detail Transaction
            $checkoutDetail = CheckoutDetail::create([
                'course_transaction_id'         => $checkout->id,
                'user_id'                       => auth()->user()->id,
                'course_id'                     => $course->id,
                'course_name'                   => $course->name,
                'price_course'                  => $course->price,
                'original_price_course'         => $course->price_num,
                'apps_commission'               => 5,
                'qty'                           => request('qty'),
                'weight'                        => $course->weight,
                'expedition'                    => $expedition[0]['expedition'],
                'service'                       => $expedition[0]['service'],
                'service_description'           => $expedition[0]['service_description'],
                'shipping_cost'                 => $expedition[0]['shipping_cost'],
                'etd'                           => $expedition[0]['etd']
            ]);

            // Updated Qty -> Move To Transaction After Approve Seller
            // if (!is_null($course->user_quota_join)) {
            //     $cDetail = Course::find($course->id);

            //     // Update Quota Now
            //     $quotaNow = CourseQuota::where('course_id', $course->id)->latest()->first();

            //     if ($quotaNow) {
            //         $quotaNow->update([
            //             'quota_now' => ($cDetail->user_quota_join - request('qty'))
            //         ]);
            //     }

            //     $cDetail->update([
            //         'user_quota_join' => ($cDetail->user_quota_join - request('qty'))
            //     ]);
            // }

            // Check is_termin
            if ($course->is_termin == 1 && request('is_termin') == 1) {
                // Initialize
                $finalTermin = finalTermin($course->id);

                foreach ($finalTermin as $index => $ft) {
                    // Initialize
                    $courseTermin = CourseTermin::where('id', $course->courseTermin->id)->first();

                    $courseTerminS = CourseTerminSchedule::create([
                        'course_id'                     => $course->id,
                        'user_id'                       => auth()->user()->id,
                        'course_transaction_detail_id'  => $checkoutDetail->id,
                        'course_termin_id'              => $courseTermin->id,
                        'description'                   => $ft['description'],
                        'value'                         => $ft['value_num'],
                        'interest'                      => $ft['interest'],
                        'due_date'                      => $ft['due_date'],
                        'is_verified'                   => ($index == 0 || $index == 1) ? 1 : 0
                    ]);

                    if ($courseTerminS && $index == 1) {
                        // Initialize
                        $totals = ($courseTerminS->value + $courseTermin->down_payment);

                        CourseTransactionTerminPayment::create([
                            'course_termin_schedule_id' => $courseTerminS->id,
                            'total_payment'             => ($totals),
                            'total_payment_original'    => ($totals),
                            'payment_type'              => (request('use_balance') && $isWalletPayment == 'fulfilled') ? 3 : request('payment_type'),
                            'bank_name'                 => (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[0],
                            'no_rek'                    => (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[1],
                            'unique_code'               => substr(($totals), -3),
                            'status'                    => (request('use_balance') && $isWalletPayment == 'fulfilled') ? 1 : 0,
                            'expired_transaction'       => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                            'with_down_payment'         => 1
                        ]);
                    }
                }
            }

            // Check Termin Totals
            $totalPaymentTermin = $this->_totalTermin($checkout->checkoutDetail);

            if ($totalPaymentTermin != 0 && $course->is_termin == 1 && request('is_termin') == 1) {
                // Initialize
                $totalPayment = ($totalPaymentTermin + $uniqueCode);

                $checkout->update([
                    'is_termin'             => 1,
                    'total_payment_termin'  => $totalPaymentTermin,
                    'total_payment'         => $totalPayment,
                    'second_unique_code'    => substr($totalPayment, -3)
                ]);
            }

            // Reduce Balance & Notification
            if (request('use_balance') && $isWalletPayment == 'fulfilled') {
                // --- Wallet Buyer
                Wallet::create([
                    'user_id'           => auth()->user()->id,
                    'balance'           => (-$totalPayment),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalPayment),
                    'details'           => 'Pembelian Produk - ('.$course->name.')'
                ]);

                // Initialize For Notification - Buyer
                $sender         = auth()->user();
                $receiverId     = $course->user_id;
                $title          = 'Transaksi Baru';
                $message        = 'Anda telah melakukan Transaksi menggunakan Saldo.';
                $code           = '01';
                $data           = [
                    'transaction_id' => $checkout->id
                ];
                $icon           = '';

                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
            }

            if (request('use_balance') && request('payment_type')) {
                $checkout->update([
                    'total_pay_with_balance' => $wallet
                ]);

                // --- Wallet Buyer
                Wallet::create([
                    'user_id'           => auth()->user()->id,
                    'balance'           => (-$wallet),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$wallet),
                    'details'           => 'Pembelian Produk - ('.$course->name.')'
                ]);

                // Initialize For Notification - Student
                $sender         = auth()->user();
                $receiverId     = '0';
                $title          = 'Transaksi Baru';
                $message        = 'Anda telah melakukan Transaksi menggunakan dua Metode Pembayaran ('.paymentType(request('payment_type')).' & Saldo). Saldo akan dikembalikan jika transaksi tidak diselesaikan sampai tanggal '.$dateNotif;
                $code           = '08';
                $data           = [
                    'transaction_id' => $checkout->id
                ];
                $icon           = '';

                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Checkout Berhasil.',
            'data'      => $checkout
        ]);
    }

    public function storeMultiple(Request $request)
    {
        // Initialize
        $requestData = request()->all();

        // Validation
        if ($requestData['payment']['payment_type'] == null && $requestData['use_balance'] != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tipe Pembayaran harus diisi. (payment_type or use_balance)'
            ]);
        }

        // Initialize
        $uniqueCode     = rand(100, 1000);
        $nowDate        = date('Y-m-d H:i:s');
        $totals         = 0;
        $shippingCost   = 0;
        
        // Check Address
        $address = Address::where('id', $requestData['address_id'])->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat pengiriman tidak ditemukan.'
            ]);
        }

        // Initialize
        $masterLocation = MasterLocation::where('id', $address->district_id)->first();

        // Check Exists Unique Code
        $uniqueCodeExists = Checkout::where([
                            'unique_code'        => $uniqueCode,
                            'status_transaction' => 0
                        ])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode       = rand(100, 1000);
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

        // Loop for checking data
        foreach($requestData['store'] as $store) {
            // Initialize
            $shippingCost += ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;

            foreach($store['products'] as $val) {
                // Initialize
                $product = Course::where('id', $val['course_id'])->first();

                if (!$product) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Produk tidak ditemukan.'
                    ]);

                    break;
                }

                // Check Stock
                if ($product->user_quota_join <= 0) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Produk '.$product->name.' sudah habis.'
                    ]);

                    break;
                }

                /* Notes :
                    * Check Total Pay
                    * Check Termin
                    * Check Discount
                */
               
                if ($product->is_termin == 1 && $requestData['is_termin'] == 1) {
                    // Initialize
                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();
                    
                    $totals += ($courseTermin->value + $courseTermin->down_payment);
                } else {
                    // Check Discount
                    if ($product->discount > 0) {
                        // Initialize
                        $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                        $totals += ($priceAfterDisc * $val['qty']);
                    } else {
                        $totals += ($product->price_num * $val['qty']);
                    }
                }
            }
        }

        // Initialize - Payment
        $bank               = $requestData['payment']['bank_name'];
        $noRek              = $requestData['payment']['no_rek'];
        $statusTransaction  = 0;
        $statusPayment      = 0;
        $totalPay           = ($totals + $uniqueCode + $shippingCost);
        $totalPayOriginal   = ($totals + $shippingCost);
        $paymentType        = $requestData['payment']['payment_type'];
        $wallet             = Wallet::where('user_id', auth()->user()->id)->sum('balance');
        $isWalletPayment    = 'unfulfilled';

        // Check Wallet if use_balance true
        if ($requestData['payment']['payment_type'] == null && $requestData['use_balance'] == 1) {
            if ($wallet < $totalPay) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Saldo tidak mencukupi untuk melakukan pembelian produk. Total Harga produk ('.rupiah($totalPay).') Saldo anda ('.rupiah($wallet).')'
                ]);
            }

            $isWalletPayment = 'fulfilled';
        }

        // If Order By use two payment method
        if ($requestData['payment']['payment_type'] != null && $requestData['use_balance'] == 1) {
            if ($wallet < 2000) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Saldo minimal Rp.2000'
                ]);
            }

            // Initialize
            $totalPay           = ($totals + $uniqueCode + $shippingCost) - $wallet;
            $totalPayOriginal   = ($totals + $shippingCost) - $wallet;
        }

        if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1) {
            // Initialize
            $statusTransaction = 1;
            $statusPayment     = 1;
        }

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $totalPay,
            'total_payment_original' => $totalPayOriginal,
            'payment_type'           => $paymentType,
            'bank_name'              => $bank,
            'no_rek'                 => $noRek,
            'unique_code'            => $uniqueCode,
            'status_transaction'     => $statusTransaction,
            'status_payment'         => $statusPayment,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'address_id'             => $requestData['address_id'],
            'province'               => $masterLocation->provinsi,
            'city'                   => $masterLocation->kota,
            'district'               => $masterLocation->kecamatan,
            'address_type'           => $masterLocation->type,
            'total_shipping_cost'    => $shippingCost
        ]);

        if ($checkout) {
            foreach($requestData['store'] as $store) {
                foreach($store['products'] as $val) {
                    // Initialize
                    $product = Course::where('id', $val['course_id'])->first();

                    if (!$product) {
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Produk tidak ditemukan.'
                        ]);

                        break;
                    }

                    // Create Detail Transaction
                    $checkoutDetail = CheckoutDetail::create([
                        'course_transaction_id'         => $checkout->id,
                        'user_id'                       => auth()->user()->id,
                        'store_id'                      => $store['store_id'],
                        'course_id'                     => $product->id,
                        'course_name'                   => $product->name,
                        'price_course'                  => $product->price,
                        'original_price_course'         => $product->price_num,
                        'discount'                      => $product->discount,
                        'price_course_after_discount'   => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                        'apps_commission'               => 5,
                        'qty'                           => $val['qty'],
                        'weight'                        => $product->weight,
                        'expedition'                    => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                        'service'                       => ($store['expedition']) ? $store['expedition']['service'] : null,
                        'service_description'           => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                        'shipping_cost'                 => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                        'etd'                           => ($store['expedition']) ? $store['expedition']['etd'] : null,
                        'is_termin'                     => ($product->is_termin == 1) ? (($requestData['is_termin']) ? $requestData['is_termin'] : 0) : 0
                    ]);

                    // Termin
                    if ($product->is_termin == 1 && $requestData['is_termin'] == 1) {
                        // Initialize
                        $finalTermin = finalTermin($product->id);

                        foreach ($finalTermin as $index => $ft) {
                            // Initialize
                            $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                            $courseTerminS = CourseTerminSchedule::create([
                                'course_id'                     => $product->id,
                                'user_id'                       => auth()->user()->id,
                                'course_transaction_detail_id'  => $checkoutDetail->id,
                                'course_termin_id'              => $courseTermin->id,
                                'description'                   => $ft['description'],
                                'value'                         => $ft['value_num'],
                                'interest'                      => $ft['interest'],
                                'due_date'                      => $ft['due_date'],
                                'is_verified'                   => ($index == 0 || $index == 1) ? 1 : 0
                            ]);

                            if ($courseTerminS && $index == 1) {
                                CourseTransactionTerminPayment::create([
                                    'course_termin_schedule_id' => $courseTerminS->id,
                                    'total_payment'             => ($courseTerminS->value + $courseTermin->down_payment),
                                    'total_payment_original'    => ($courseTerminS->value + $courseTermin->down_payment),
                                    'payment_type'              => (request('use_balance') && $isWalletPayment == 'fulfilled') ? 3 : request('payment_type'),
                                    'bank_name'                 => (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[0],
                                    'no_rek'                    => (request('use_balance') && $isWalletPayment == 'fulfilled') ? null : $bank[1],
                                    'unique_code'               => substr(($courseTerminS->value + $courseTermin->down_payment), -3),
                                    'status'                    => 0,
                                    'expired_transaction'       => date('Y-m-d H:i:s', strtotime('+22 hourse'))
                                ]);
                            }
                        }
                    }

                    // Delete Data in Cart
                    $carts = Cart::where(['user_id' => auth()->user()->id, 'course_id' => $val['course_id']])->delete();
                }
            }

            // If Order By Balance
            if ($isWalletPayment == 'fulfilled' && $requestData['use_balance'] == 1) {
                Wallet::create([
                    'user_id'           => auth()->user()->id,
                    'balance'           => (-($totalPay)),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$totalPayOriginal),
                    'details'           => 'Pembelian Multiple Produk'
                ]);
            }

            // Check Exist is_termin
            $cUpdate            = Checkout::where('id', $checkout->id)->first();
            $totalPaymentTermin = $this->_totalTermin($cUpdate->checkoutDetail);

            if ($totalPaymentTermin != 0 && $requestData['is_termin'] == 1) {
                $cUpdate->update([
                    'is_termin'             => 1,
                    'total_payment_termin'  => $totalPaymentTermin,
                    'second_unique_code'    => substr(($totalPaymentTermin + $uniqueCode), -3)
                ]);
            } else {
                $cUpdate->update([
                    'second_unique_code' => substr(($totalPay), -3)
                ]);
            }

            // If Order By use two payment method
            if ($requestData['payment']['payment_type'] != null && $requestData['use_balance'] == 1) {
                // Insert Wallet - For Student
                Wallet::create([
                    'user_id'           => auth()->user()->id,
                    'balance'           => (-$wallet),
                    'is_verified'       => 1,
                    'balance_type'      => 2,
                    'apps_commission'   => 0,
                    'original_balance'  => (-$wallet),
                    'details'           => 'Pembelian Multiple Produk. Pay with ('.paymentType($requestData['payment']['payment_type']).' & Saldo)'
                ]);

                $cUpdate->update([
                    'total_pay_with_balance' => $wallet
                ]);
            }
        }

        // Initialize
        $data = Checkout::where('id', $checkout->id)->first();

        return new StudentCheckoutStoreResource($data);
    }

    private function _totalTermin($cd)
    {
        // Initialize
        $totalTermin = 0;

        foreach ($cd as $val) {
            if ($val->course->is_termin == 1 && $val->course->courseTermin) {
                // Initialize
                $finalTermin = finalTermin($val->course->id);

                foreach ($finalTermin as $index => $ft) {
                    if ($index <= 1) {
                        $totalTermin += $ft['value_num'];
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

        return $totalTermin;
    }
}
