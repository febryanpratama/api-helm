<?php

namespace App\Http\Controllers;

use App\Account;
use App\BeginBalance;
use Illuminate\Http\Request;
use App\Notifications\GlobalNotification;
use DB;
use App\Invoice;
use App\Wallet;
use App\TransactionJointBank;
use App\TransactionAdminCommission;
use App\Company;
use App\User;
use App\CourseTerminSchedule;
use App\Journal;
use Notification;

class MootaController extends Controller
{
    // Initialize
    public $mootaSecretCode;

    public function __construct()
    {
        // Initialize Var
        $this->mootaSecretCode = '0';
    }

    /*
        Notes :
        -
    */
    
    public function index()
    {
        // Get All Data From Sent.
        $publicData = file_get_contents("php://input");

        if ($publicData) {
            // Change To Array
            $arrayData = json_decode($publicData, true);

            // Insert Mutation From Moota
            DB::table('log_moota')->insert([
                'json_data'             => json_encode($arrayData),
                'course_transaction_id' => '0'
            ]);

            // Loop Data
            $index = 0;
            foreach ($arrayData as $val) {
                // Create Unique Code
                $uniqueCode = substr($val['amount'], -3);

                // Check Invoice
                $invoice = Invoice::where([
                            'total_payment' => $val['amount'],
                            'unique_code'   => $uniqueCode,
                            'status'        => 0
                        ])
                        ->first();

                if (!$invoice) {
                    // Check Invoice
                    $invoice = Invoice::where([
                        'total_payment'      => $val['amount'],
                        'second_unique_code' => $uniqueCode,
                        'status'             => 0
                    ])
                    ->first();
                }

                if ($invoice) {
                    // Checking Invoice Category
                    if ($invoice->category_transaction == 0) { // Shopping
                        /*
                            --- Notes :
                            * Check Termin
                            * Category Transaction == 2 : Termin/Installment  
                         */
                        
                        if ($invoice->is_termin) {
                            foreach($invoice->transaction as $transaction) {
                                // Update Status Termin Schedule
                                foreach($transaction->transactionDetails as $val) {
                                    // Initialize
                                    $terminSchedule = CourseTerminSchedule::where([
                                                        'course_transaction_detail_id'  => $val->id,
                                                        'description'                   => 'Uang Muka'
                                                    ])->update([
                                                        'is_verified' => 1
                                                    ]);
                                }

                                // Check Platinum Account
                                $platinumAccount = Company::where([
                                                    'city_id'       => $transaction->company->city_id,
                                                    'status'        => 1,
                                                    'is_verified'   => 1
                                                ])
                                                ->first();
                                $totalDeduction  = 0.05;

                                if ($platinumAccount) {
                                    $totalDeduction = 0.04;
                                }

                                // Commission Formula
                                $formula            = ($val->transaction->total_payment) - (0.05 * $val->transaction->total_payment);
                                $systemCommission   = ($totalDeduction * $val->transaction->total_payment);
                                $platinumCommission = 0;

                                if ($platinumAccount) {
                                    $platinumCommission = (0.01 * $val->transaction->total_payment);
                                }

                                if ($platinumAccount) {
                                    // Get Commission from Transaction (1%)
                                    Wallet::create([
                                        'user_id'           => $platinumAccount->user->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 3,
                                        'apps_commission'   => 0,
                                        'balance'           => $platinumCommission,
                                        'original_balance'  => $platinumCommission,
                                        'details'           => 'Commision Transaksi - (#INV-'.$invoice->id.')'
                                    ]);
                                }

                                // Insert Wallet For Seller
                                Wallet::create([
                                    'user_id'           => $transaction->company->user->id,
                                    'is_verified'       => 1,
                                    'balance_type'      => 0,
                                    'apps_commission'   => 5,
                                    'balance'           => $formula,
                                    'original_balance'  => $invoice->total_payment,
                                    'details'           => 'Income Transaksi (DP dan Termin Pertama) - (#INV-'.$invoice->id.')'
                                ]);

                                // Insert Joint Bank
                                TransactionJointBank::create([
                                    'invoice_id'                    => $invoice->id,
                                    'transaction_id'                => $transaction->id,
                                    'total_payment_by_transaction'  => $invoice->total_payment_without_balance,
                                    'apps_commission'               => 5,
                                    'total_after_deduction'         => ($invoice->total_payment_without_balance) - (0.05 * $invoice->total_payment_without_balance),
                                    'status'                        => 1
                                ]);

                                // Insert Admin Commission
                                TransactionAdminCommission::create([
                                    'invoice_id'                    => $invoice->id,
                                    'transaction_id'                => $transaction->id,
                                    'total_payment_by_transaction'  => $formula,
                                    'apps_commission'               => substr($totalDeduction, 3),
                                    'total_after_deduction'         => $systemCommission
                                ]);

                                // Notification
                                $sender         = $transaction->company->user;
                                $receiverId     = $transaction->company->user->id;
                                $title          = 'Pembayaran Transaksi';
                                $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah($formula).' Dari '.rupiah($invoice->total_payment).' Dipotong 5% untuk transaksi. Nomor Invoice (#INV-'.$invoice->id.')';
                                $code           = '04';
                                $data           = [
                                    'transaction_id' => $transaction->id
                                ];
                                $icon           = '';
                                
                                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));

                                // save journal
                                $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                                $this->journal($begin_balance, $transaction, $formula);
                            }

                            // Insert Unique Code
                            Wallet::create([
                                'user_id'           => $invoice->user_id,
                                'is_verified'       => 1,
                                'balance_type'      => 0,
                                'apps_commission'   => 0,
                                'balance'           => $invoice->unique_code,
                                'original_balance'  => $invoice->unique_code,
                                'details'           => 'Pengembalian Dana (Unique Code) dari Invoice - (#INV-'.$invoice->id.')'
                            ]);
                        } else {
                            foreach($invoice->transaction as $transaction) {
                                // Initialize
                                $total = 0;

                                foreach($transaction->transactionDetails as $val) {
                                    // Back Payment
                                    if ($val->back_payment_status == 1) {
                                        // Initialize
                                        $totalPrice = $val->price_course;

                                        if ($val->discount) {
                                            $totalPrice = discountFormula($val->discount, $val->price_course);
                                        }
                                        
                                        $total += ($totalPrice * $val->qty);
                                    }
                                }

                                if ($total > 0) {
                                    // Check Platinum Account
                                    $platinumAccount = Company::where([
                                                        'city_id'       => $transaction->company->city_id,
                                                        'status'        => 1,
                                                        'is_verified'   => 1
                                                    ])
                                                    ->first();
                                    $totalDeduction  = 0.05;

                                    if ($platinumAccount) {
                                        $totalDeduction = 0.04;
                                    }

                                    // Commission Formula
                                    $formula            = ($total) - ($totalDeduction * $total);
                                    $systemCommission   = ($totalDeduction * $total);
                                    $platinumCommission = 0;

                                    if ($platinumAccount) {
                                        $platinumCommission = (0.01 * $total);
                                    }

                                    // Insert Wallet For Seller
                                    Wallet::create([
                                        'user_id'           => $transaction->company->user->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 0,
                                        'apps_commission'   => 5,
                                        'balance'           => $formula,
                                        'original_balance'  => $total,
                                        'details'           => 'Income Transaksi - (#INV-'.$invoice->id.')'
                                    ]);

                                    // Insert Joint Bank
                                    TransactionJointBank::create([
                                        'invoice_id'                    => $invoice->id,
                                        'transaction_id'                => $transaction->id,
                                        'total_payment_by_transaction'  => $total,
                                        'apps_commission'               => 5,
                                        'total_after_deduction'         => ($total) - (0.05 * $total),
                                        'status'                        => 1
                                    ]);

                                    // Insert Admin Commission
                                    TransactionAdminCommission::create([
                                        'invoice_id'                    => $invoice->id,
                                        'transaction_id'                => $transaction->id,
                                        'total_payment_by_transaction'  => $formula,
                                        'apps_commission'               => $totalDeduction,
                                        'total_after_deduction'         => $systemCommission,
                                    ]);

                                    if ($platinumAccount) {
                                        // Get Commission from Transaction (1%)
                                        Wallet::create([
                                            'user_id'           => $platinumAccount->user->id,
                                            'is_verified'       => 1,
                                            'balance_type'      => 3,
                                            'apps_commission'   => 0,
                                            'balance'           => $platinumCommission,
                                            'original_balance'  => $platinumCommission,
                                            'details'           => 'Commision Transaksi - (#INV-'.$invoice->id.')'
                                        ]);
                                    }

                                    // Notification
                                    $sender         = $transaction->company->user;
                                    $receiverId     = $transaction->company->user->id;
                                    $title          = 'Pembayaran Transaksi';
                                    $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah($total).' Dari '.rupiah($formula).' Dipotong 5% untuk transaksi. Nomor Invoice (#INV-'.$invoice->id.')';
                                    $code           = '04';
                                    $data           = [
                                        'transaction_id' => $transaction->id
                                    ];
                                    $icon           = '';
                                    
                                    Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));

                                    // save journal (back payment)
                                    $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                                    $this->journal($begin_balance, $transaction, $total);
                                }

                                if ($total == 0) {
                                    // save journal
                                    $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                                    $total = $transaction->total_payment;
                                    $this->journal($begin_balance, $transaction, $total);
                                }
                            }
                        }

                        // Initialize
                        $responseData = [
                            'status'    => true,
                            'message'   => 'Success',
                            'data'      => [
                                'details' => 'Shopping'
                            ]
                        ];
                    } else if ($invoice->category_transaction == 1) { // Top Up
                        // Insert Unique Code To User Wallet
                        Wallet::create([
                            'user_id'           => $invoice->user_id,
                            'balance'           => $invoice->total_payment,
                            'is_verified'       => 1,
                            'balance_type'      => 0,
                            'apps_commission'   => 0,
                            'original_balance'  => $invoice->total_payment,
                            'unique_code'       => null,
                            'details'           => 'Top Up'
                        ]);

                        // Initialize For Notification - Institution
                        $sender         = $invoice->user;
                        $receiverId     = $invoice->user_id;
                        $title          = 'Top Up';
                        $code           = '05';
                        $message        = 'Top Up Saldo sebesar '.rupiah($invoice->total_payment).' berhasil.';
                        $data           = [
                            'transaction_id' => $invoice->id
                        ];
                        $icon           = '';

                        Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));

                        // Initialize
                        $responseData = [
                            'status'    => true,
                            'message'   => 'Success',
                            'data'      => [
                                'details' => 'Top Up'
                            ]
                        ];
                    } else if ($invoice->category_transaction == 2) { // Termin
                        foreach($invoice->transaction as $transaction) {
                            // Update Status Termin Schedule
                            $invoiceTerminSchedule = $invoice->invoiceTerminSchedule;

                            if ($invoiceTerminSchedule) {
                                $terminSchedule = CourseTerminSchedule::where('id', $invoiceTerminSchedule->termin_schedule_id)->update([
                                                    'is_verified' => 1
                                                ]);

                                // Check Platinum Account
                                $platinumAccount = Company::where([
                                                    'city_id'       => $transaction->company->city_id,
                                                    'status'        => 1,
                                                    'is_verified'   => 1
                                                ])
                                                ->first();
                                $totalDeduction  = 0.05;

                                if ($platinumAccount) {
                                    $totalDeduction = 0.04;
                                }

                                // Commission Formula
                                $formula            = ($invoice->total_payment) - ($totalDeduction * $invoice->total_payment);
                                $systemCommission   = ($totalDeduction * $invoice->total_payment);
                                $platinumCommission = 0;

                                if ($platinumAccount) {
                                    $platinumCommission = (0.01 * $invoice->total_payment);
                                }

                                // Insert Wallet For Seller
                                Wallet::create([
                                    'user_id'           => $transaction->company->user->id,
                                    'is_verified'       => 1,
                                    'balance_type'      => 0,
                                    'apps_commission'   => $totalDeduction,
                                    'balance'           => $formula,
                                    'original_balance'  => $invoice->total_payment,
                                    'details'           => 'Income Transaksi (Termin) - (#INV-'.$invoice->id.')'
                                ]);

                                // Insert Joint Bank
                                TransactionJointBank::create([
                                    'invoice_id'                    => $invoice->id,
                                    'transaction_id'                => $transaction->id,
                                    'total_payment_by_transaction'  => $invoice->total_payment,
                                    'apps_commission'               => 5,
                                    'total_after_deduction'         => ($invoice->total_payment) - (0.05 * $invoice->total_payment),
                                    'status'                        => 1
                                ]);

                                // Insert Admin Commission
                                TransactionAdminCommission::create([
                                    'invoice_id'                    => $invoice->id,
                                    'transaction_id'                => $transaction->id,
                                    'total_payment_by_transaction'  => $formula,
                                    'apps_commission'               => $totalDeduction,
                                    'total_after_deduction'         => $systemCommission
                                ]);

                                if ($platinumAccount) {
                                    // Get Commission from Transaction (1%)
                                    Wallet::create([
                                        'user_id'           => $platinumAccount->user->id,
                                        'is_verified'       => 1,
                                        'balance_type'      => 3,
                                        'apps_commission'   => 0,
                                        'balance'           => $platinumCommission,
                                        'original_balance'  => $platinumCommission,
                                        'details'           => 'Commision Transaksi - (#INV-'.$invoice->id.')'
                                    ]);
                                }

                                // Notification
                                $sender         = $transaction->company->user;
                                $receiverId     = $transaction->company->user->id;
                                $title          = 'Pembayaran Transaksi';
                                $message        = 'Pembayaran diteruskan ke dompet anda sebesar '.rupiah($formula).' Dari '.rupiah($invoice->total_payment).' Dipotong 5% untuk transaksi. Nomor Invoice (#INV-'.$invoice->id.')';
                                $code           = '04';
                                $data           = [
                                    'transaction_id' => $transaction->id
                                ];
                                $icon           = '';
                                
                                Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $message, $data, $icon));

                                // save journal
                                $begin_balance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();
                                $this->journal($begin_balance, $transaction, $formula);
                            }
                        }

                        // Initialize
                        $responseData = [
                            'status'    => true,
                            'message'   => 'Success',
                            'data'      => [
                                'details' => 'Termin Payment'
                            ]
                        ];
                    }

                    // Update Invoice
                    $invoice->update([
                        'status'          => 1,
                        'status_tf_moota' => 1,
                        'date_tf_moota'   => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // Initialize
                    $responseData = [
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => [
                            'details' => 'No Data Found'
                        ]
                    ];
                }
            }
        }
        
        return response()->json($responseData);
    }

    private function checkSignature($rawJson)
    {
        return hash_hmac('sha256', json_encode($rawJson), $this->mootaSecretCode);
    }

    private function journal($begin_balance, $transaction, $total) {
        // Journal method beginbalance = 0 atau 1
        if ($begin_balance) {
            $account_debit_1 = Account::where('CurrType', 'Cash In Hand')->first();
            $account_credit_1 = Account::where('CurrType', 'Account Receivable')->first();
            $account_debit_2 = null;
            $account_credit_2 = null;

            if ($transaction->shipping_cost && $transaction->shipping_cost != 0) {
                $account_debit_2 = Account::where('CurrType', 'Account Payable')->first();
                $account_credit_2 = Account::where('CurrType', 'Cash In Hand')->first();
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
                $debit1['value'] = $total;
                $debit1['account'] = $res_acc_debit1;
                $debit1_json[] = $debit1;

                if ($account_debit_2) {
                    // debit 2
                    $acc_debit2['id'] = $account_debit_2->ID;
                    $acc_debit2['name'] = $account_debit_2->Name;
                    $acc_debit2['code'] = $account_debit_2->Code;
                    $acc_debit2['group'] = $account_debit_2->group;
                    $acc_debit2['type'] = $account_debit_2->CurrType;
                    $res_acc_debit2[] = $acc_debit2;

                    $debit2['id'] = $account_debit_2->ID;
                    $debit2['value'] = $transaction->shipping_cost;
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
                $credit1['value'] = $total;
                $credit1['account'] = $res_acc_credit1;
                $credit1_json[] = $credit1;

                if ($account_credit_2) {
                    // credit 2
                    $acc_credit2['id'] = $account_credit_2->ID;
                    $acc_credit2['name'] = $account_credit_2->Name;
                    $acc_credit2['code'] = $account_credit_2->Code;
                    $acc_credit2['group'] = $account_credit_2->group;
                    $acc_credit2['type'] = $account_credit_2->CurrType;

                    $res_acc_credit2[] = $acc_credit2;

                    $credit2['id'] = $account_credit_2->ID;
                    $credit2['value'] = $transaction->shipping_cost;
                    $credit2['account'] = $res_acc_credit2;
                    $credit2_json[] = $credit2;
                }

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
                    'IDCompany'             => $transaction->store_id,
                    'IDCurrency'            => 0,
                    'Rate'                  => 1,
                    'JournalType'           => 'general',
                    'JournalDate'           => date('Y-m-d'),
                    'JournalName'           => 'Penjualan Saat Pembayaran Buyer Masuk|' . $transaction->id,
                    'JournalDocNo'          => $res_doc,
                    'json_debit'            => $debit_json,
                    'json_credit'           => $credit_json,
                    'AddedTime'             => time(),
                    'AddedBy'               => $transaction->invoice->user_id,
                    'AddedByIP'             => $_SERVER['REMOTE_ADDR']
                ]);
            }
        }
    }
}
