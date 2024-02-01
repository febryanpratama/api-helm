<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Partner;
use App\CourseUserPartner;
use App\Course;
use App\Majors;
use App\TheoryLock;
use App\MajorsSubject;
use App\CoursePartner;
use App\Checkout;
use App\CheckoutDetail;
use App\UserCourse;
use DB;
use Chat;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data = DB::table('partner')
                ->leftJoin('course_user_partner', 'partner.id', '=', 'course_user_partner.partner_id')
                ->leftJoin('users', 'partner.user_id', '=', 'users.id')
                ->leftJoin('course_partner_invoice', 'partner.id', '=', 'course_partner_invoice.partner_id')
                ->select('partner.*', 'course_user_partner.course_id', 'users.name as username', 'users.email as useremail', 'users.phone as userphone', DB::raw('count(course_partner_invoice.partner_id) as email_course'))
                ->where('course_user_partner.course_id', request('courseId'))
                ->groupBy('partner.user_id')
                ->latest()
                ->get();

        return response([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
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
        // Check Session And Theory
        $session = Majors::where('IDCourse', request('course_id'))->count();

        if (!$session) {
            return response()->json([
                'status'    => false,
                'message'   => 'Sesi dan Materi tidak boleh kosong.'
            ]);
        }

        // Check Publish Course
        $course = Course::where('id', request('course_id'))->first();

        if ($course->is_publish == 0) {
            return response()->json([
                'status'    => false,
                'message'   => 'Paket Kursus harus di Daftarkan.'
            ]);
        }

        // Check User
        $user = User::where(['email' => request('email')])->first();

        if (!$user) {
            // Insert To User
            $user = User::create([
                'name'      => request('pic'),
                'email'     => request('email'),
                'phone'     => request('phone'),
                'role_id'   => '2',
                'is_active' => 'y'
            ]);
        }

        // Check Partner
        $partner = Partner::where(['name' => request('name'), 'phone' => request('phone')])->first();

        if (!$partner) {
            $partner = Partner::create([
                'user_id'   => $user->id,
                'name'      => request('name'),
                'pic'       => request('pic'),
                'phone'     => request('phone')
            ]);
        }

        // Insert Course Partner
        CoursePartner::create([
            'course_id'     => request('course_id'),
            'partner_id'    => $partner->id
        ]);

        // Insert Course User Partner
        $cup = CourseUserPartner::create([
            'course_id'     => request('course_id'),
            'partner_id'    => $partner->id,
            'user_id'       => $user->id
        ]);

        // Insert Transaction Logic
        if ($cup) {
            // Create To Transaction
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
                'user_id'                => $user->id,
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
                    'user_id'               => $user->id,
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
                        'user_id'        => $user->id,
                        'course_id'      => $checkoutDetail->course_id,
                        'course_start'   => date('Y-m-d H:i:s'),
                        'course_expired' => $expiredCourse
                    ]);

                    // Insert Theory Lock
                    TheoryLock::create([
                        'user_id'    => $user->id,
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
                                            ->where('messageable_id', $user->id)
                                            ->first();

                        if (!$checkExistsChat) {
                            $participant     = User::find($user->id);
                            $conversation    = Chat::conversations()->getById($getChatsGroup->conversation_id);
                            $addParticipants = Chat::conversation($conversation)->addParticipants([$participant]);
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
    public function show(Partner $partner, Course $course)
    {
        // Initialize
        $users = DB::table('course_user_partner')
                ->join('users', 'course_user_partner.user_id', '=', 'users.id')
                ->join('roles', 'users.role_id', '=', 'roles.ID')
                ->select('users.*', 'course_user_partner.partner_id', 'roles.Name as role_name')
                ->where('course_user_partner.partner_id', $partner->id)
                ->latest()
                ->get();

        return view('partner.index', compact('partner', 'users', 'course'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Partner $partner)
    {
        // Initialize
        $partner = Partner::with('user')->where('id', $partner->id)->first();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $partner
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner $partner)
    {
        $partner->update([
            'name'  => request('name'),
            'pic'   => request('pic'),
            'phone' => request('phone')
        ]);

        // Initialize
        $user = User::where('id', $partner->user_id)->update([
            'name'  => request('pic'),
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
    public function destroy(Partner $partner, Course $course)
    {
        /*
            Notes :
            * Remove Member From Group Chat
            * Delete User Course
            * Delete Course Partner
            * Delete Course User Partner
            * Delete Chat Message
         */
        
        // Delete From Group Chat
        $chats = DB::table('course_chat')->where('course_id', $course->id)->first();

        if ($chats) {
            // Initialize
            $conversation = Chat::conversations()->getById($chats->conversation_id);

            if ($conversation) {
                // Get Users
                $users    = CourseUserPartner::where(['partner_id' => $partner->id, 'course_id' => $course->id])->pluck('user_id');
                $profiles = [];

                foreach ($users as $val) {
                    // Get Users
                    $user = User::find($val);

                    array_push($profiles, $user);
                }

                // Remove
                Chat::conversation($conversation)->removeParticipants($profiles);
            }
        }

        // Delete User Course
        $userCourse = UserCourse::whereIn('user_id', $users)->where('course_id', $course->id)->delete();

        // Delete Course Partner
        $coursePartner = CoursePartner::where(['partner_id' => $partner->id, 'course_id' => $course->id])->delete();

        // Delete Course User Partner
        $courseUserPartner = CourseUserPartner::where(['partner_id' => $partner->id, 'course_id' => $course->id])->delete();

        // Delete Chat Message
        DB::table('chat_messages')->where(['conversation_id' => $chats->conversation_id, 'participation_id' => null])->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }

    public function invoice(Request $request, Partner $partner, Course $course)
    {
        // Insert To Table Partner Invoice
        $partnerInv = DB::table('course_partner_invoice')->insert([
            'partner_id' => $partner->id
        ]);

        if ($partnerInv) {
            // Get Invoice
            $users      = CourseUserPartner::where(['partner_id' => $partner->id, 'course_id' => $course->id])->pluck('user_id');
            $checkoutDt = CheckoutDetail::whereIn('user_id', $users)->where('course_id', $course->id)->pluck('course_transaction_id');
            $checkout   = Checkout::whereIn('id', $checkoutDt)->sum('total_payment');

            // Send Email
            \Mail::to($partner->user->email)->send(new \App\Mail\CoursePartnerInvoice($partner->user, $course, $checkout));
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Invoice berhasil dikirim'
        ]);
    }
}
