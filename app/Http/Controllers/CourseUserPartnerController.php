<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\CourseUserPartner;
use App\Partner;
use App\Checkout;
use App\CheckoutDetail;
use App\Course;
use App\UserCourse;
use App\TheoryLock;
use App\Majors;
use App\MajorsSubject;
use Chat;
use DB;

class CourseUserPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check Account
        $account = User::where('email', $request->email)->first();

        if (!$account) {
            // Create Users
            $account = User::create([
                'name'      => request('name'),
                'email'     => request('email'),
                'phone'     => request('phone'),
                'role_id'   => 6
            ]);
        }

        // Get Course
        $courseUserPartner = CourseUserPartner::where('partner_id', request('partner_id'))->first();

        if ($courseUserPartner) {
            // Check Course Users Partner
            $cup = CourseUserPartner::where(['user_id' => $account->id, 'partner_id' => request('partner_id')])->first();

            if (!$cup) {
                // Create User Partner
                $cupC = CourseUserPartner::create([
                    'course_id'     => $courseUserPartner->course_id,
                    'partner_id'    => request('partner_id'),
                    'user_id'       => $account->id
                ]);

                if ($cupC) {
                    // Create To Transaction
                    $course     = Course::where('id', $courseUserPartner->course_id)->first();
                    $uniqueCode = rand(100, 1000);
                    $nowDate    = date('Y-m-d H:i:s');
                    $total      = $course->price_num;

                    // Check Exists Unique Code
                    $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

                    // Check Unique Code
                    if ($checkoutExists) {
                        for ($i= 0; $i < 100; $i++) { 
                            // Initialize
                            $uniqueCode     = rand(100, 1000);
                            $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

                            if (!$checkoutExists) {
                                break;
                            }
                        }
                    }

                    // Create Transaction
                    $checkout = Checkout::create([
                        'user_id'                => $account->id,
                        'total_payment'          => ($course->price_num + $uniqueCode),
                        'total_payment_original' => $course->price_num,
                        'payment_type'           => '',
                        'bank_name'              => '',
                        'no_rek'                 => '',
                        'unique_code'            => $uniqueCode,
                        'status_transaction'     => ($course->course_type == 2) ? 1 : 0,
                        'status_payment'         => ($course->course_type == 2) ? 1 : 0,
                        'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                        'buy_now'                => 1,
                        'status_transaction'     => 2,
                        'status_payment'         => 2
                    ]);

                    if ($checkout) {
                        // Initialize
                        $expiredCourse = expiredDate($course->periode_type, $course->periode);
                        
                        // Create Detail Transaction
                        $checkoutDetail = CheckoutDetail::create([
                            'course_transaction_id' => $checkout->id,
                            'user_id'               => $account->id,
                            'course_id'             => $course->id,
                            'course_name'           => $course->name,
                            'price_course'          => $course->price,
                            'original_price_course' => $course->price_num,
                            'course_periode_type'   => $course->periode_type,
                            'course_periode'        => $course->periode,
                            'course_type'           => $course->course_type,
                            'course_start'          => date('Y-m-d H:i:s'),
                            'expired_course'        => ($course->course_type == 2) ? $expiredCourse : '',
                            'apps_commission'       => ($course->course_type == 2) ? 0 : 5
                        ]);

                        // Insert To More Table
                        if ($checkoutDetail) {
                            // Initialize
                            $major         = Majors::where('IDCourse', $checkoutDetail->course_id)->take(1)->get();
                            $majorSubject  = MajorsSubject::where('major_id', $major[0]['ID'])->take(1)->get();

                            // User Course
                            UserCourse::create([
                                'user_id'        => $account->id,
                                'course_id'      => $checkoutDetail->course_id,
                                'course_start'   => date('Y-m-d H:i:s'),
                                'course_expired' => $expiredCourse
                            ]);

                            // Insert Theory Lock
                            TheoryLock::create([
                                'user_id'    => $account->id,
                                'course_id'  => $checkoutDetail->course_id,
                                'major_id'   => $major[0]['ID'],
                                'subject_id' => $majorSubject[0]['subject_id']
                            ]);

                            // Insert To Chat
                            $getChatsGroup = DB::table('course_chat')->where('course_id', $checkoutDetail->course_id)->first();

                            if ($getChatsGroup) {
                                // Initialize
                                $checkExistsChat = DB::table('chat_participation')
                                                    ->where('conversation_id', $getChatsGroup->conversation_id)
                                                    ->where('messageable_id', $account->id)
                                                    ->first();

                                if (!$checkExistsChat) {
                                    $participant     = User::find($account->id);
                                    $conversation    = Chat::conversations()->getById($getChatsGroup->conversation_id);
                                    $addParticipants = Chat::conversation($conversation)->addParticipants([$participant]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil ditambahkan'
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // Initialize
        $oldEmail = $user->email;

        if ($oldEmail != request('email')) {
            // Check Email
            $email = User::where('email', request('email'))->first();

            if ($email) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Email sudah terdaftar'
                ]);
            }
        }

        $user->update([
            'name'  => request('name'),
            'email' => request('email'),
            'phone' => request('phone')
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner, User $user)
    {
        /* 
            Notes :
                * Delete From Course User Partner
                * Delete From User Course
                * Delete From Theory
                * Delete From Course Transaction
                * Delete From Detail Course Transaction
        */
       
        // Initialize
        $checkoutD  = CheckoutDetail::where(['user_id' => $user->id, 'course_id' => request('course-id')])->first();
        $users      = CourseUserPartner::where(['partner_id' => $partner->id, 'user_id' => $user->id])->delete();
        $userCourse = UserCourse::where(['user_id' => $user->id, 'course_id' => request('course-id')])->delete();
        $theory     = TheoryLock::where(['user_id' => $user->id, 'course_id' => request('course-id')])->delete();

        if ($checkoutD) {
            // Initialize
            $checkout = Checkout::where(['id' => $checkoutD->course_transaction_id, 'user_id' => $user->id, 'status_transaction' => 2])->first();

            if ($checkout) {
                $checkout->delete();
                $checkoutD->delete();
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }
}
