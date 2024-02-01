<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\Checkout;
use App\CheckoutDetail;
use App\Majors;
use App\MajorsSubject;
use App\TheoryLock;
use App\Wallet;
use App\User;
use App\HistoryTransfer;
use App\HistoryTransferUniqueCode;
use App\CourseTransactionPartner;
use App\CourseUserPartner;
use App\CourseTransactionJointAccountBank;
use App\UserCourse;
use App\CourseTransactionTerminPayment;
use App\Notifications\GlobalNotification;
use DB;
use Chat;
use Notification;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $nowDate = date('Y-m-d H:i:s');

        if (auth()->user()->role_id == 1) {
            // Initialize
            $courses = Course::where(['user_id' => auth()->user()->id])->pluck('id');

            if (request('filter')) {
                if (request('filter') == 'Expired') {
                    // Initialze
                    $transactions = DB::table('course_transaction_detail')
                                                    ->join('course_transaction', 'course_transaction.id', '=', 'course_transaction_detail.course_transaction_id')
                                                    ->join('users', 'users.id', '=', 'course_transaction.user_id')
                                                    ->join('course', 'course.id', '=', 'course_transaction_detail.course_id')
                                                    ->select(
                                                        'course_transaction_detail.*',
                                                        'course_transaction.user_id',
                                                        'course_transaction.expired_transaction',
                                                        'course_transaction.is_offline',
                                                        'course_transaction.status_transaction',
                                                        'course_transaction.status_payment',
                                                        'course_transaction.total_payment',
                                                        'course_transaction.unique_code',
                                                        'course_transaction.second_unique_code',
                                                        'course_transaction.bank_name',
                                                        'course_transaction.no_rek',
                                                        'course_transaction.buy_now',
                                                        'course_transaction.payment_type',
                                                        'users.name as username',
                                                        'users.curriculum_vitae',
                                                        'course.name as course_name',
                                                        'course.slug',
                                                        'course_transaction_detail.status_delivery',
                                                        'course_transaction_detail.expedition',
                                                        'course_transaction_detail.service',
                                                        'course_transaction_detail.shipping_cost',
                                                        'course_transaction_detail.etd',
                                                        'course_transaction_detail.receipt',
                                                        'course_transaction_detail.is_termin'
                                                    )
                                                    ->whereIn('course_transaction_detail.course_id', $courses)
                                                    ->where(['status_transaction' => 0, 'status_payment' => 0, 'is_offline' => 0])
                                                    ->whereDate('expired_transaction', '<=', $nowDate)
                                                    ->latest()
                                                    ->get();
                } else if (request('filter') == 'Menunggu Transfer') {
                    // Initialze
                    $transactions = DB::table('course_transaction_detail')
                                                    ->join('course_transaction', 'course_transaction.id', '=', 'course_transaction_detail.course_transaction_id')
                                                    ->join('users', 'users.id', '=', 'course_transaction.user_id')
                                                    ->join('course', 'course.id', '=', 'course_transaction_detail.course_id')
                                                    ->select(
                                                        'course_transaction_detail.*',
                                                        'course_transaction.user_id',
                                                        'course_transaction.expired_transaction',
                                                        'course_transaction.status_transaction',
                                                        'course_transaction.is_offline',
                                                        'course_transaction.status_payment',
                                                        'course_transaction.total_payment',
                                                        'course_transaction.unique_code',
                                                        'course_transaction.second_unique_code',
                                                        'course_transaction.bank_name',
                                                        'course_transaction.no_rek',
                                                        'course_transaction.buy_now',
                                                        'course_transaction.payment_type',
                                                        'users.name as username',
                                                        'users.curriculum_vitae',
                                                        'course.name as course_name',
                                                        'course.slug',
                                                        'course_transaction_detail.status_delivery',
                                                        'course_transaction_detail.expedition',
                                                        'course_transaction_detail.service',
                                                        'course_transaction_detail.shipping_cost',
                                                        'course_transaction_detail.etd',
                                                        'course_transaction_detail.receipt',
                                                        'course_transaction_detail.is_termin'
                                                    )
                                                    ->whereIn('course_transaction_detail.course_id', $courses)
                                                    ->where(['status_transaction' => 0, 'status_payment' => 0, 'is_offline' => 0])
                                                    ->whereDate('expired_transaction', '>=', $nowDate)
                                                    ->latest()
                                                    ->get();
                } else {
                    $transactions = DB::table('course_transaction_detail')
                                                    ->join('course_transaction', 'course_transaction.id', '=', 'course_transaction_detail.course_transaction_id')
                                                    ->join('users', 'users.id', '=', 'course_transaction.user_id')
                                                    ->join('course', 'course.id', '=', 'course_transaction_detail.course_id')
                                                    ->select(
                                                        'course_transaction_detail.*',
                                                        'course_transaction.user_id',
                                                        'course_transaction.expired_transaction',
                                                        'course_transaction.status_transaction',
                                                        'course_transaction.is_offline',
                                                        'course_transaction.status_payment',
                                                        'course_transaction.total_payment',
                                                        'course_transaction.unique_code',
                                                        'course_transaction.second_unique_code',
                                                        'course_transaction.bank_name',
                                                        'course_transaction.no_rek',
                                                        'course_transaction.buy_now',
                                                        'course_transaction.payment_type',
                                                        'users.name as username',
                                                        'users.curriculum_vitae',
                                                        'course.name as course_name',
                                                        'course.slug',
                                                        'course_transaction_detail.status_delivery',
                                                        'course_transaction_detail.expedition',
                                                        'course_transaction_detail.service',
                                                        'course_transaction_detail.shipping_cost',
                                                        'course_transaction_detail.etd',
                                                        'course_transaction_detail.receipt',
                                                        'course_transaction_detail.is_termin'
                                                    )
                                                    ->whereIn('course_transaction_detail.course_id', $courses)
                                                    ->where(['status_transaction' => 1, 'status_payment' => 1, 'is_offline' => 0])
                                                    ->latest()
                                                    ->get();
                }
            } else {
                $transactions = DB::table('course_transaction_detail')
                                ->join('course_transaction', 'course_transaction.id', '=', 'course_transaction_detail.course_transaction_id')
                                ->join('users', 'users.id', '=', 'course_transaction.user_id')
                                ->join('course', 'course.id', '=', 'course_transaction_detail.course_id')
                                ->select(
                                    'course_transaction_detail.*',
                                    'course_transaction.user_id',
                                    'course_transaction.expired_transaction',
                                    'course_transaction.status_transaction',
                                    'course_transaction.is_offline',
                                    'course_transaction.status_payment',
                                    'course_transaction.total_payment',
                                    'course_transaction.unique_code',
                                    'course_transaction.second_unique_code',
                                    'course_transaction.bank_name',
                                    'course_transaction.no_rek',
                                    'course_transaction.buy_now',
                                    'course_transaction.payment_type',
                                    'users.name as username',
                                    'users.curriculum_vitae',
                                    'course.name as course_name',
                                    'course.slug',
                                    'course_transaction_detail.status_delivery',
                                    'course_transaction_detail.expedition',
                                    'course_transaction_detail.service',
                                    'course_transaction_detail.shipping_cost',
                                    'course_transaction_detail.etd',
                                    'course_transaction_detail.receipt',
                                    'course_transaction_detail.is_termin'
                                )
                                ->whereIn('course_transaction_detail.course_id', $courses)
                                ->where(['is_offline' => 0])
                                ->latest()
                                ->get();
            }
        } else {
            // Search
            if (request('from_date') || request('till_date')) {
                if (request('from_date') && request('till_date')) {
                    // Initialze
                    $transactions = Checkout::where(['user_id' => auth()->user()->id, 'is_offline' => 0])->whereDate('created_at', '>=', request('from_date'))->whereDate('created_at', '<=', request('till_date'))->latest()->get();
                } else if (request('from_date')) {
                    // Initialize
                    $transactions = Checkout::where(['user_id' => auth()->user()->id, 'is_offline' => 0])->whereDate('created_at', '>=', request('from_date'))->latest()->get();
                } else {
                    // Initialize
                    $transactions = Checkout::where(['user_id' => auth()->user()->id, 'is_offline' => 0])->whereDate('created_at', '<=', request('till_date'))->latest()->get();
                }
            } else {
                if (request('filter')) {
                    if (request('filter') == 'Expired') {
                        // Initialze
                        $transactions = Checkout::where(['user_id' => auth()->user()->id, 'status_transaction' => 0, 'status_payment' => 0, 'is_offline' => 0])->whereDate('expired_transaction', '<=', $nowDate)->latest()->get();
                    } else if (request('filter') == 'Menunggu Transfer') {
                        // Initialze
                        $transactions = Checkout::where(['user_id' => auth()->user()->id, 'status_transaction' => 0, 'status_payment' => 0, 'is_offline' => 0])->whereDate('expired_transaction', '>=', $nowDate)->latest()->get();
                    } else {
                        // Initialze
                        $transactions = Checkout::where(['user_id' => auth()->user()->id, 'status_transaction' => 1, 'status_payment' => 1, 'is_offline' => 0])->latest()->get();
                    }
                } else {
                    // Initialze
                    $transactions = Checkout::where(['user_id' => auth()->user()->id, 'is_offline' => 0])->latest()->get();
                }
            }
        }

        // Custom Paginate
        $transactions = $this->paginate($transactions, 20, null, ['path' => $request->fullUrl()]);

        if (auth()->user()->role_id == 1) {
            $data = $this->_manageDataInstructor($transactions);
        } else {
            // Initialize
            $data = $this->_manageDataStudent($transactions);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data transaksi.',
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

    private function _manageDataInstructor($transactions)
    {
        // Initialize
        $data = [];

        // Loop Data
        foreach ($transactions as $val) {
            // Initialize
            $nowDate = date('Y-m-d H:i:s');
            
            // Check Termin Or Cash
            if ($val->is_termin == 1) {
                // Initialize
                $checkout          = Checkout::where('id', $val->course_transaction_id)->first();
                $commissionFormula = $checkout->total_payment - (($val->apps_commission/100) * $checkout->total_payment);
            } else {
                // Initialize
                $commissionFormula = $val->original_price_course - (($val->apps_commission/100) * $val->original_price_course);
            }
            
            $row['id']                           = $val->id;
            $row['user']                         = $val->username;
            $row['course']                       = $val->course_name;
            $row['slug']                         = $val->slug;
            $row['nominal_transaction']          = rupiah($commissionFormula);

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

            // Get Course Details
            $course = Course::where('id', $val->course_id)->first();

            if ($course) {
                $row['course_package_category'] = courseCategory($course->course_package_category);
            }

            $row['expedition']           = $val->expedition;
            $row['service']              = $val->service;
            $row['service_description']  = $val->service_description;
            $row['shipping_cost']        = $val->shipping_cost;
            $row['shipping_cost_rupiah'] = rupiah($val->shipping_cost);
            $row['etd']                  = $val->etd.' Hari';
            $row['receipt']              = $val->receipt;
            $row['is_termin']            = $val->is_termin;

            $data[] = $row;
        }

        return $data;
    }

    private function _manageDataStudent($transactions)
    {
        // Initialize
        $data = [];

        // Loop Data
        foreach ($transactions as $val) {
            // Initialize
            $detailTrasaction = [];
            $nowDate          = date('Y-m-d H:i:s');
            $row['id']        = $val->id;

            foreach ($val->checkoutDetail as $cd) {
                // Initialize
                $rowCD['course_id']                     = $cd->course_id;
                $rowCD['course_name']                   = $cd->course_name;
                $rowCD['course_slug']                   = $cd->slug;
                $rowCD['original_price_course']         = ($cd->original_price_course > 0) ? $cd->original_price_course : 'free';
                $rowCD['expedition']                    = $cd->expedition;
                $rowCD['service']                       = $cd->service;
                $rowCD['service_description']           = $cd->service_description;
                $rowCD['shipping_cost']                 = $cd->shipping_cost;
                $rowCD['shipping_cost_rupiah']          = rupiah($cd->shipping_cost);
                $rowCD['etd']                           = $cd->etd.' Hari';
                $rowCD['receipt']                       = $cd->receipt;
                $rowCD['is_termin']                     = $cd->is_termin;

                $detailTrasaction[] = $rowCD;
            }

            $row['detail_transaction']  = $detailTrasaction;

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
            $row['is_topup']            = $val->is_topup;

            if ($val->payment_type == 1) {
                $row['payment_type']    = 'Bank Transfer';
            } elseif ($val->payment_type == 2) {
                $row['payment_type']    = 'E-Money';
            } else {
                $row['payment_type']    = $val->payment_type;
            }

            $data[] = $row;
        }

        return $data;
    }

    public function approve($id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!'
            ]);
        }

        // Initialize
        $transaction = CheckoutDetail::where(['id' => $id, 'status_approval_institution' => 0])->first();
        $nowDate     = date('Y-m-d H:i:s');

        if ($transaction) {
            // Check Transfer Balance
            $checkoutExpired = Checkout::where('id', $transaction->course_transaction_id)->first();

            if ($checkoutExpired && $checkoutExpired->status_payment == 0 && $checkoutExpired->expired_transaction >= $nowDate) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Murid belum belum melakukan Pembayaran.'
                ]);
            }

            // Initialize
            $course             = Course::where('id', $transaction->course_id)->first();
            $expiredCourse      = expiredDate($transaction->course_periode_type, $transaction->course_periode);
            $major              = Majors::where('IDCourse', $transaction->course_id)->take(1)->get();
            $majorSubject       = MajorsSubject::where('major_id', $major[0]['ID'])->take(1)->get();
            $commissionFormula  = $transaction->original_price_course - (($transaction->apps_commission/100) * $transaction->original_price_course);

            // Check Termin Or Cash
            if ($transaction->is_termin == 1) {
                // Initialize
                $checkout          = Checkout::where('id', $transaction->course_transaction_id)->first();
                $commissionFormula = $checkout->total_payment - (($transaction->apps_commission/100) * $checkout->total_payment);
            }

            // Check Exsist Data
            $existsData = UserCourse::where(['user_id' => $transaction->user_id, 'course_id' => $transaction->course_id])->first();
            
            if (!$existsData) {
                // User Course
                UserCourse::create([
                    'user_id'        => $transaction->user_id,
                    'course_id'      => $transaction->course_id,
                    'course_start'   => date('Y-m-d H:i:s'),
                    'course_expired' => $expiredCourse
                ]);

                // Insert Theory Lock
                TheoryLock::create([
                    'user_id'    => $transaction->user_id,
                    'course_id'  => $transaction->course_id,
                    'major_id'   => $major[0]['ID'],
                    'subject_id' => $majorSubject[0]['subject_id']
                ]);
            }

            // Insert Wallet
            Wallet::create([
                'user_id'           => $course->user_id,
                'balance'           => $commissionFormula,
                'is_verified'       => 1,
                'balance_type'      => 0,
                'apps_commission'   => $transaction->apps_commission,
                'original_balance'  => $transaction->original_price_course,
                'details'           => 'Course Package Purchase - ('.$transaction->course_name.')'
            ]);

            // Insert Commission
            $getUpline = User::where('id', $course->user_id)->first();

            if ($getUpline && $getUpline->referral_id) {
                // Formula Cashback
                $cashbackUpline    = (2/100) * $transaction->original_price_course;
                $cashbackDownline  = (1/100) * $transaction->original_price_course;

                // Insert Wallet
                Wallet::create([
                    'user_id'           => $getUpline->referral_id,
                    'balance'           => $cashbackUpline,
                    'is_verified'       => 1,
                    'balance_type'      => 0,
                    'apps_commission'   => '',
                    'original_balance'  => $cashbackUpline,
                    'unique_code'       => '',
                    'details'           => 'Sales Bonus Course Package | '.$course->id
                ]);

                Wallet::create([
                    'user_id'           => $course->user_id,
                    'balance'           => $cashbackDownline,
                    'is_verified'       => 1,
                    'balance_type'      => 0,
                    'apps_commission'   => '',
                    'original_balance'  => $cashbackDownline,
                    'unique_code'       => '',
                    'details'           => 'Sales Bonus Course Package | '.$course->id
                ]);
            }

            // Insert History Transfer
            HistoryTransfer::create([
                'course_transaction_id'         => $transaction->course_transaction_id,
                'course_transaction_detail_id'  => $transaction->id,
                'price_course'                  => $transaction->price_course,
                'apps_commission'               => 5,
                'total_for_system'              => (($transaction->apps_commission/100) * $transaction->original_price_course)
            ]);

            if (!$existsData) {
                // Insert To Chat
                $getChatsGroup = DB::table('course_chat')->where('course_id', $transaction->course_id)->first();

                if ($getChatsGroup) {
                    // Initialize
                    $checkExistsChat = DB::table('chat_participation')
                                        ->where('conversation_id', $getChatsGroup->conversation_id)
                                        ->where('messageable_id', $transaction->user_id)
                                        ->first();

                    if (!$checkExistsChat) {
                        $participant     = User::find($transaction->user_id);
                        $conversation    = Chat::conversations()->getById($getChatsGroup->conversation_id);
                        $addParticipants = Chat::conversation($conversation)->addParticipants([$participant]);
                    }
                }
            }

            $transaction->update([
                'course_start'                  => date('Y-m-d H:i:s'),
                'expired_course'                => $expiredCourse,
                'status_approval_institution'   => 1
            ]);

            // Updated Joint Bank
            CourseTransactionJointAccountBank::where('course_transaction_detail_id', $id)->update([
                'status' => 1,
                'reason' => request('reason')
            ]);

            // Initialize For Notification - Student
            $sender         = $transaction->user_id;
            $receiverId     = $course->user;
            $title          = 'Hasil Peninjauan';
            $secondMessage  = 'Pembelian Paket '.courseCategory($course->course_package_category).' ('.$course->name.') Telah selesai di tinjau oleh Lembaga, Segera periksa pada menu Paket untuk memulai pembelajaran';
            $code           = '07';
            $data           = [
                'transaction_id' => $checkout->id
            ];
            $icon           = '';

            Notification::send($sender, new GlobalNotification($receiverId, $sender, $title, $code, $secondMessage, $data, $icon));

            return response()->json([
                'status'    => 'success',
                'message'   => 'Transaksi berhasil di Approve',
                'data'      => $transaction
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Data Transaksi tidak ditemukan atau data sudah di Approve/Rejected oleh Lembaga'
        ]);
    }

    public function rejected($id)
    {
        if (auth()->user()->role_id != 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!'
            ]);
        }

        if (!request()->reason) {
            return response()->json([
                'message'   => 'The given data was invalid.',
                'errors'    => [
                        'reason' => [
                            'Alasan dibutuhkan.'
                        ]
                    ]
            ]);
        }

        // Initialize
        $transaction = CheckoutDetail::where(['id' => $id, 'status_approval_institution' => 0])->first();
        $nowDate     = date('Y-m-d H:i:s');

        if ($transaction) {
            // Check Transfer Balance
            $checkoutExpired = Checkout::where('id', $transaction->course_transaction_id)->first();

            if ($checkoutExpired && $checkoutExpired->status_payment == 0 && $checkoutExpired->expired_transaction >= $nowDate) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Transaksi belum Dibayar.'
                ]);
            }
            
            /*
                Return the balance that has been transferred to the user
            */
            
            // Insert Wallet, Convert from transfer
            Wallet::create([
                'user_id'           => $transaction->user_id,
                'balance'           => $transaction->original_price_course,
                'is_verified'       => 1,
                'balance_type'      => 'income',
                'apps_commission'   => '',
                'original_balance'  => $transaction->original_price_course,
                'unique_code'       => '',
                'details'           => 'balance_refund_due_to_rejected_course_package_transaction'
            ]);

            // Updated Joint Bank
            CourseTransactionJointAccountBank::where('course_transaction_detail_id', $id)->update([
                'status' => 2,
                'reason' => request('reason')
            ]);

            $transaction->update([
                'status_approval_institution' => 2
            ]);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Transaksi di Tolak',
                'data'      => $transaction
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Data Transaksi tidak ditemukan atau data sudah di Approve/Rejected oleh Lembaga'
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
