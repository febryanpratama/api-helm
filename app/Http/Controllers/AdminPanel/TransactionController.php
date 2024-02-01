<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Checkout;
use App\Course;
use App\TheoryLock;
use App\Majors;
use App\MajorsSubject;
use App\Wallet;
use App\User;
use App\UserCourse;
use App\CheckoutDetail;
use App\HistoryTransfer;
use Chat;
use DB;

class TransactionController extends Controller
{
    public function index()
    {
        // Initialize
        $transactions = Checkout::latest()->paginate(20);
        $nowDate      = date('Y-m-d H:i:s');

        return view('admin-panel.transaction.index', compact('transactions','nowDate'));
    }

    public function update(Request $request, Checkout $checkout)
    {
        return response()->json([
            'status'    => false,
            'message'   => 'Fitur Dalam Pengembangan'
        ]);

        if ($checkout) {
            // Initialize
            $uniqueCode = $checkout->unique_code;

            // Update Course Transaction
            $checkout->update([
                'status_transaction' => 1,
                'status_payment'     => 1
            ]);

            // Initialize
            $courseTransactionDetail = CheckoutDetail::where('course_transaction_id', $checkout->id)->get();
            $userCheckout            = User::where('id', $checkout->user_id)->first();

            foreach ($courseTransactionDetail as $val) {
                // Initialize
                $course             = Course::where('id', $val->course_id)->first();
                $expiredCourse      = expiredDate($val->course_periode_type, $val->course_periode);
                $major              = Majors::where('IDCourse', $val->course_id)->take(1)->get();
                $majorSubject       = MajorsSubject::where('major_id', $major[0]['ID'])->take(1)->get();
                $commissionFormula  = $val->original_price_course - (($val->apps_commission/100) * $val->original_price_course);

                // Update Course Transaction Detail
                CheckoutDetail::where('id', $val->id)->update([
                    'course_start'      => date('Y-m-d H:i:s'),
                    'expired_course'    => $expiredCourse
                ]);

                // Check Exsist Data
                $existsData = UserCourse::where(['user_id' => $val->user_id, 'course_id' => $val->course_id])->first();

                if (!$existsData) {
                    // User Course
                    UserCourse::create([
                        'user_id'        => $val->user_id,
                        'course_id'      => $val->course_id,
                        'course_start'   => date('Y-m-d H:i:s'),
                        'course_expired' => $expiredCourse
                    ]);

                    // Insert Theory Lock
                    TheoryLock::create([
                        'user_id'    => $val->user_id,
                        'course_id'  => $val->course_id,
                        'major_id'   => $major[0]['ID'],
                        'subject_id' => $majorSubject[0]['subject_id']
                    ]);
                }

                // Insert Wallet
                Wallet::create([
                    'user_id'           => $course->user_id,
                    'balance'           => $commissionFormula,
                    'is_verified'       => 1,
                    'balance_type'      => 'income',
                    'apps_commission'   => $val->apps_commission,
                    'original_balance'  => $val->original_price_course,
                    'details'           => 'Course Package Purchase'
                ]);

                // Initialize
                $dataForLog = [
                    'info' => 'insert_from_admin_panel'
                ];

                // Insert Mutation From Moota
                DB::table('log_moota')->insert([
                    'json_data'             => json_encode($dataForLog),
                    'course_transaction_id' => $checkout->id
                ]);

                // Insert Commission
                $getUpline = User::where('id', $course->user_id)->first();

                if ($getUpline && $getUpline->referral_id) {
                    // Formula Cashback
                    $cashbackUpline    = (2/100) * $val->original_price_course;
                    $cashbackDownline  = (1/100) * $val->original_price_course;

                    // Insert Wallet
                    Wallet::create([
                        'user_id'           => $getUpline->referral_id,
                        'balance'           => $cashbackUpline,
                        'is_verified'       => 1,
                        'balance_type'      => 'income',
                        'apps_commission'   => '',
                        'original_balance'  => $cashbackUpline,
                        'unique_code'       => '',
                        'details'           => 'Sales Bonus Course Package | '.$course->id
                    ]);

                    Wallet::create([
                        'user_id'           => $course->user_id,
                        'balance'           => $cashbackDownline,
                        'is_verified'       => 1,
                        'balance_type'      => 'income',
                        'apps_commission'   => '',
                        'original_balance'  => $cashbackDownline,
                        'unique_code'       => '',
                        'details'           => 'Sales Bonus Course Package | '.$course->id
                    ]);
                }

                // Insert History Transfer
                HistoryTransfer::create([
                    'course_transaction_id'         => $val->course_transaction_id,
                    'course_transaction_detail_id'  => $val->id,
                    'price_course'                  => $val->price_course,
                    'apps_commission'               => 5,
                    'total_for_system'              => (($val->apps_commission/100) * $val->original_price_course)
                ]);
                
                if (!$existsData) {
                    // Insert To Chat
                    $getChatsGroup = DB::table('course_chat')->where('course_id', $val->course_id)->first();

                    if ($getChatsGroup) {
                        // Initialize
                        $checkExistsChat = DB::table('chat_participation')
                                            ->where('conversation_id', $getChatsGroup->conversation_id)
                                            ->where('messageable_id', $checkout->user_id)
                                            ->first();

                        if (!$checkExistsChat) {
                            $participant     = User::find($checkout->user_id);
                            $conversation    = Chat::conversations()->getById($getChatsGroup->conversation_id);
                            $addParticipants = Chat::conversation($conversation)->addParticipants([$participant]);
                        }
                    }
                }
            }

            // Insert Unique Code To User Wallet
            Wallet::create([
                'user_id'           => $checkout->user_id,
                'balance'           => $uniqueCode,
                'is_verified'       => 1,
                'balance_type'      => 'income',
                'apps_commission'   => '',
                'original_balance'  => $uniqueCode,
                'unique_code'       => '',
                'details'           => 'course_package_payment_cashback'
            ]);

            // Email Notification
            \Mail::to($checkout->user->email)->send(new \App\Mail\DonePaymentStudent($userCheckout, $checkout));
            \Mail::to('incorelabmail@gmail.com')->send(new \App\Mail\DonePaymentManagement($userCheckout, $checkout));
            \Mail::to('aldagltk1@gmail.com')->send(new \App\Mail\DonePaymentManagement($userCheckout, $checkout));
            \Mail::to('mr.rahmat.hidayat@gmail.com')->send(new \App\Mail\DonePaymentManagement($userCheckout, $checkout));
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Transaksi berhasil di Approve'
        ]);
    }
}
