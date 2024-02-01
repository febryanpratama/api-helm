<?php

namespace App\Http\Controllers\Api\Buyer;

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
use App\CourseTerminSchedule;
use App\WholesalePrice;
// use App\CourseTransactionTerminPayment;
use App\TransactionDetailsCustomDocumentInput;
use App\Http\Resources\CheckoutStoreResource;
use Carbon\Carbon;
use Notification;
use App\Notifications\GlobalNotification;

class CheckoutController extends Controller
{
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
        $requestData = request()->all();

        // Validation
        if ($requestData['payment']['payment_type'] == null && $requestData['use_balance'] != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tipe Pembayaran harus diisi. (payment_type or use_balance)'
            ]);
        }

        // Initialize
        $uniqueCode          = rand(100, 1000);
        $nowDate             = date('Y-m-d H:i:s');
        $totals              = 0;
        $shippingCost        = 0;
        $totalWholesalePrice = 0;

        // Check Address
        $address = Address::where('id', $requestData['address_id'])->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat pengiriman tidak ditemukan.'
            ]);
        }

        // Check Master Location
        $masterLocation = MasterLocation::where('id', $address->district_id)->first();

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

        // Loop for checking data
        foreach($requestData['store'] as $store) {
            /*
                Notes :
                1. Count Total Shipping Cost
                2. Check Product
            */

            // Initialize
            $shippingCost += ($store['expedition']) ? $store['expedition']['shipping_cost'] : 0;

            foreach($store['products'] as $val) {
                // Initialize
                $product = Course::where('id', $val['course_id'])->first();

                if (!$product) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Produk dengan ID ('.$val['course_id'].') tidak ditemukan.'
                    ]);

                    break;
                }
                
                if ($product->course_package_category == 0) {
                    if ($product->wholesalePrice) {
                        // Initialize
                        $dataMaxQtyArray = [];
                        $latestMaxQty    = WholesalePrice::where('course_id', $product->id)->latest()->first();

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

                            if ($val['qty'] >= $minQtyReset && $val['qty'] <= $maxQtyReset) {
                                $totalWholesalePrice = $wholesale->price;

                                break;
                            } else if ($val['qty'] >= $latestMaxQty->qty) {
                                $totalWholesalePrice = $latestMaxQty->price;

                                break;
                            }
                        }
                    } else {
                        $totalWholesalePrice = $product->price_num;
                    }
                }

                // Check Stock
                if ($product->user_quota_join <= 0) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Produk '.$product->name.' sudah habis.'
                    ]);

                    break;
                }

                // Check Stock and QTY
                if ($product->user_quota_join < $val['qty']) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Produk ('.$product->name.') melebihi batas pembelian. Stok ('.$product->user_quota_join.') QTY ('.$val['qty'].')'
                    ]);

                    break;
                }

                // Check SP File
                if ($product->is_sp) {
                    // Check Upload MOU or SP File
                    // $existsMou = AgreementLetter::where([
                    //                 'course_id' => $product->id,
                    //                 'user_id'   => auth()->user()->id,
                    //                 'status'    => 0
                    //             ])
                    //             ->first();

                    // if (!$existsMou) {
                    //     return response()->json([
                    //         'status'    => 'error',
                    //         'message'   => 'Produk ('.$product->name.') harus menyertakan Surat SP/MOU.'
                    //     ]);
                    // }
                }

                // config check Jasa
                if (isset($requestData['is_service']) && $requestData['is_service'] == 1) {
                    // init
                    $get_day = Carbon::createFromFormat('Y-m-d', $store['service']['date'])->translatedFormat('l');
                    $product_day = explode(', ', strtolower($product->period_day));
                    $start_time = $product->start_time_min;
                    $end_time = $product->end_time_min;
                    $select_time = $store['service']['time'];
                    $check_day = in_array(strtolower($get_day), $product_day);
                    $time_valid = false;

                    if (!$check_day) { // check ketersedian jasa hari nya
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Tanggal Hari yang dipilih ('. $get_day .') tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk hari ' . $product->period_day . '.'
                        ]);
    
                        break;
                    }

                    // check if time jasa null (24 jam)
                    if (!$start_time && !$end_time) {
                        $time_valid = true;
                    }

                    if ($start_time < $select_time && $end_time > $select_time) { // check selected time valid
                        $time_valid = true;
                    }

                    if (!$time_valid) { // check time not valid
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Jam yang dipilih tidak tersedia untuk jasa '.$product->name.' hanya tersedia untuk Jam ' . $product->start_time_min . ' - ' . $product->end_time_min . '.'
                        ]);
    
                        break;
                    }
                }

                /* Notes :
                    1. Check Total Pay
                    2. Check Termin
                    3. Check Discount
                */
                
                if ($product->is_termin == 1 && $requestData['is_termin'] == 1) {
                    // CHECK TERMIN
                    if (isset($requestData['is_negotiable']) && $requestData['is_negotiable'] == 1) {
                        // dd($val['termin']);

                        if (isset($val['termin']) && count($val['termin']) > 0) { // check  json termin
                            $getSimulation = finalTermin($product->id, $val['qty']);
                            $get_total_termin_v = 0;
                            foreach ($getSimulation as $tm) { // get total value termin product
                                $get_total_termin_v += $tm['value_num'];
                            }

                            $get_total_termin_input = 0;
                            foreach ($val['termin'] as $ter) { // get total value termin from input
                                $get_total_termin_input += $ter['value_num'];
                            }

                            // VALIDATION
                            if ($get_total_termin_input < $get_total_termin_v) {
                                $data = [
                                    'status'    => 'error',
                                    'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                    'code'      => 400
                                ];
                        
                                return response()->json($data, 400);
                            }
                        
                            if ($get_total_termin_input > $get_total_termin_v) {
                                $data = [
                                    'status'    => 'error',
                                    'message'   => 'Jumlah Total termin & dp harus ' . rupiah($get_total_termin_v) . ' total yang anda input adalah ' . rupiah($get_total_termin_input),
                                    'code'      => 400
                                ];
                        
                                return response()->json($data, 400);
                            }
                        }
                    }

                    if (isset($requestData['is_negotiable']) && $requestData['is_negotiable'] == 1) { // nego
                        $dp_tot = 0;

                        foreach ($val['termin'] as $t => $ter) { // get total value termin from input
                            if ($t == 0) { // get DP
                                $dp_tot = $ter['value_num'];

                                $totals += $dp_tot;
                                break;
                            }
                        }
                    } else { // tanpa nego
                        // Initialize
                        $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();
    
                        if ($courseTermin->is_percentage == 1) {
                            $dp_tot = ($courseTermin->down_payment/100) * $courseTermin->installment_amount;
                        } else {
                            $dp_tot = $courseTermin->down_payment;
                        }
                        
                        $totals += $dp_tot * $val['qty']; // pembayaran pertama hanya dp
                    }
                } else {
                    // Check Discount
                    if ($product->discount > 0) {
                        // Initialize
                        $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                        $totals += ($priceAfterDisc * $val['qty']);
                    } else {
                        // Initialize
                        if ($totalWholesalePrice > 0) {
                            $priceProduct = $totalWholesalePrice;
                        } else {
                            $priceProduct = $product->price_num;
                        }

                        $totals += ($priceProduct * $val['qty']);
                    }
                }
            }
        }

        // Initialize - Payment
        $bank               = $requestData['payment']['bank_name'];
        $noRek              = $requestData['payment']['no_rek'];
        $status             = 0;
        $totalPay           = ($totals + $uniqueCode + $shippingCost) + $requestData['transaction_fees'];
        $totalPayOriginal   = ($totals + $shippingCost);
        $paymentType        = $requestData['payment']['payment_type'];
        $wallet             = Wallet::where('user_id', auth()->user()->id)->sum('balance');
        $isWalletPayment    = 'unfulfilled';
        $totalsWithoutPrice = ($uniqueCode + $shippingCost) + $requestData['transaction_fees'];

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
            $status = 1;
        }

        $invoice = Invoice::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $totalPay,
            'total_payment_original' => $totalPayOriginal,
            'payment_type'           => $paymentType,
            'total_shipping_cost'    => $shippingCost,
            'transaction_fees'       => $requestData['transaction_fees'],
            'bank_name'              => $bank,
            'no_rek'                 => $noRek,
            'unique_code'            => $uniqueCode,
            'status'                 => $status,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'is_termin'              => $requestData['is_termin']
        ]);

        if ($invoice) {
            InvoiceAddress::create([
                'invoice_id'        => $invoice->id,
                'address_id'        => $requestData['address_id'],
                'province'          => $masterLocation->provinsi,
                'city'              => $masterLocation->kota,
                'district'          => $masterLocation->kecamatan,
                'address_type'      => $masterLocation->type,
                'details_address'   => $address->details_address
            ]);

            foreach($requestData['store'] as $store) {
                // Initialize
                $totalsPayByStore = 0;
                $select_date = isset($store['service']) ? $store['service']['date'] : null; // for services
                $select_time = isset($store['service']) ? $store['service']['time'] : null; // for services

                $transaction = Transaction::create([
                    'store_id'              => $store['store_id'],
                    'invoice_id'            => $invoice->id,
                    'total_payment'         => 0,
                    'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                    'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                    'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                    'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                    'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                    'service_date'          => $select_date ? $select_date . ' ' . $select_time : null
                ]);

                if ($transaction) {
                    foreach($store['products'] as $val) {
                        // Initialize
                        $product    = Course::where('id', $val['course_id'])->first();
                        $checkCart  = Cart::where(['user_id' => auth()->user()->id, 'course_id' => $product->id])->first();

                        // Create Transaction Details
                        $transactionDetails = TransactionDetails::create([
                            'transaction_id'              => $transaction->id,
                            'course_id'                   => $product->id,
                            'course_name'                 => $product->name,
                            'course_detail'               => $product->description,
                            'price_course'                => $product->price_num,
                            'discount'                    => $product->discount,
                            'price_course_after_discount' => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                            'qty'                         => $val['qty'],
                            'weight'                      => $product->weight,
                            'back_payment_status'         => $product->back_payment_status,
                            'category_detail_inputs'      => ($checkCart) ? $checkCart->category_detail_inputs : null
                        ]);

                        // Check SP File
                        if ($product->is_sp) {
                            // Check Upload MOU or SP File
                            $existsMou = AgreementLetter::where([
                                            'course_id' => $product->id,
                                            'user_id'   => auth()->user()->id,
                                            'status'    => 0
                                        ])
                                        ->update([
                                            'transaction_details_id' => $transactionDetails->id, 
                                            'status'                 => 1,
                                        ]);
                        }

                        // Termin
                        if ($product->is_termin == 1 && $requestData['is_termin'] == 1) {

                            if (isset($requestData['is_negotiable']) && $requestData['is_negotiable'] == 1) { // with nego
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
                                        'user_id'                       => auth()->user()->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $val['termin'][$index]['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($val['termin'][$index]['due_date'])),
                                        'termin_percentage'             => ($ft['is_percentage'] == 0) ? rupiah($val['termin'][$index]['value_num']) : ($val['termin'][$index]['value_num']/$get_tot) * 100  . '%',
                                        'completion_percentage'         => $ft['completion_percentage'],
                                        'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                                        'due_date_description'          => $ft['due_date_description'],
                                        'duedate_number'                => $ft['duedate_number'],
                                        'duedate_name'                  => $ft['duedate_name'],
                                        'is_verified'                   => 0, 
                                        'is_percentage'                 => $ft['is_percentage'],
                                    ]);
                                }

                                // Notification
                                // * For Seller
                                $sellerMessage  = 'Pembeli memesan jasa '.$product->name.' dengan total '.rupiah($get_tot).' dan nego di uang muka dan termin';
                                $recipient      = $transaction->company->user;
                                $receiverId     = '0';
                                $title          = 'Transaksi Baru';
                                $code           = '03';
                                $data           = [
                                    'transaction_id' => $transaction->id
                                ];
                                $icon           = '';

                                Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $sellerMessage, $data, $icon));
                            } else {
                                // Initialize
                                $finalTermin = finalTermin($product->id, $val['qty']);
    
                                foreach ($finalTermin as $index => $ft) {
                                    // Initialize
                                    $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();
    
                                    $courseTerminS = CourseTerminSchedule::create([
                                        'course_id'                     => $product->id,
                                        'user_id'                       => auth()->user()->id,
                                        'course_transaction_detail_id'  => $transactionDetails->id,
                                        'course_termin_id'              => $courseTermin->id,
                                        'description'                   => $ft['description'],
                                        'value'                         => $ft['value_num'],
                                        'interest'                      => $ft['interest'],
                                        'due_date'                      => date('Y-m-d', strtotime($ft['due_date'])),
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
                        }

                        if ($product->is_termin == 1 && $requestData['is_termin'] == 1) {
                            // Initialize
                            $courseTermin = CourseTermin::where('id', $product->courseTermin->id)->first();

                            // Get Termin Schedule
                            $terminScheduleDetails = CourseTerminSchedule::where(['user_id' => auth()->user()->id, 'course_id' => $product->id, 'course_transaction_detail_id' => $transactionDetails->id])->first();
                            // $totalTermin1          = ($terminScheduleDetails) ? ($terminScheduleDetails[1]) ? $terminScheduleDetails[1]->value : 0 : 0;
                            
                            $totalsPayByStore += $terminScheduleDetails->value;
                        } else {
                            // Check Discount
                            if ($product->discount > 0) {
                                // Initialize
                                $priceAfterDisc = discountFormula($product->discount, $product->price_num);

                                $totalsPayByStore += ($priceAfterDisc * $val['qty']);
                            } else {
                                $totalsPayByStore += ($product->price_num * $val['qty']);
                            }
                        }

                        // Custom Document Input
                        if (isset($val['custom_document_input']) && count($val['custom_document_input']) > 0) {
                            TransactionDetailsCustomDocumentInput::create([
                                'transaction_details_id'    => $transactionDetails->id,
                                'value'                     => json_encode($val['custom_document_input'])
                            ]);
                        }

                        // Delete Data in Cart
                        $carts = Cart::where(['user_id' => auth()->user()->id, 'course_id' => $val['course_id']])->delete();
                    }

                    $transaction->update([
                        'total_payment' => $totalsPayByStore
                    ]);

                    // Notification
                    $dateNotif = date('d-m-Y H:i', strtotime('+22 hourse'));

                    // Check use Wallet or Not
                    if ($requestData['use_balance'] == 1 && !$requestData['payment']['payment_type']) {
                        // Initialize
                        $sellerMessage = auth()->user()->name.' Telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') menggunakan saldo.';
                        $buyerMessage  = 'Anda telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') menggunakan saldo.';
                    } else {
                        // Initialize
                        $sellerMessage = auth()->user()->name.' Telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.'). Status Pembayaran menunggu transfer.';
                        $buyerMessage  = 'Anda telah melakukan Transaksi dengan Nomor Invoice (#INV-'.$invoice->id.') segera lakukan pembayaran sebelum '.$dateNotif;
                    }

                    // * For Seller
                    $recipient      = $transaction->company->user;
                    $receiverId     = '0';
                    $title          = 'Transaksi Baru';
                    $code           = '03';
                    $data           = [
                        'transaction_id' => $transaction->id
                    ];
                    $icon           = '';

                    Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $sellerMessage, $data, $icon));

                    // * For Buyer
                    $recipient      = auth()->user();
                    $receiverId     = '0';
                    $title          = 'Transaksi Baru';
                    $code           = '03';
                    $data           = [
                                        'transaction_id' => $transaction->id
                                    ];
                    $icon           = '';

                    Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $buyerMessage, $data, $icon));
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
            $invoice        = Invoice::where('id', $invoice->id)->first();
            $totalPayTermin = $this->_totalTermin($invoice->transaction); // this DP

            if ($totalPayTermin != 0 && $requestData['is_termin'] == 1) {
                $invoice->update([
                    // 'total_payment'             => ($totalPayTermin + $shippingCost + $uniqueCode),
                    'total_payment_original'    => $totalPay,
                    'is_termin'                 => 1,
                    'total_payment_termin'      => $totalPayTermin,
                    'second_unique_code'        => substr(($totalPayTermin + $uniqueCode), -3)
                ]);
            } else {
                $invoice->update([
                    'second_unique_code' => substr(($totalPay), -3)
                ]);
            }
            
            return new CheckoutStoreResource($invoice);
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
                
                // if ($val->course->is_termin == 1 && $val->course->courseTermin) {
                //     // Initialize
                //     $finalTermin = finalTermin($val->course->id, $val->qty);

                //     foreach ($finalTermin as $index => $ft) {
                //         if ($index == 0) { // get DP
                //             $totalTermin += $ft['value_num'];
                //         } else {
                //             break;
                //         }
                //     }
                // } else {
                //     if ($val->course->is_termin == 0) {
                //         $totalTermin += 0;
                //     }
                // }
            }
        }

        return $totalTermin;
    }
}
