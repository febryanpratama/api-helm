<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CourseUserPartnerRequest;
use App\Http\Requests\CourseUserPartnerUpdateRequest;
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

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseUserPartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $data = DB::table('course_user_partner')
                ->join('users', 'course_user_partner.user_id', '=', 'users.id')
                ->join('roles', 'users.role_id', '=', 'roles.ID')
                ->select('users.*', 'course_user_partner.partner_id', 'roles.Name as role_name')
                ->where('course_user_partner.partner_id', request('partner_id'))
                ->latest()
                ->get();

        // Custom Paginate
        $users  = $this->paginate($data, 20, null, ['path' => $request->fullUrl()]);
        $data   = [];

        foreach ($users as $val) {
            // Initialize
            $row['id']          = $val->id;
            $row['partner_id']  = $val->partner_id;
            $row['name']        = $val->name;
            $row['email']       = $val->email;
            $row['phone']       = $val->phone;
            $row['avatar']      = $val->avatar;
            $row['role_id']     = $val->role_id;
            $row['role_name']   = $val->role_name;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Course User Partner.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $users->currentPage(),
                'from'              => 1,
                'last_page'         => $users->lastPage(),
                'next_page_url'     => $users->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $users->perPage(),
                'prev_page_url'     => $users->previousPageUrl(),
                'total'             => $users->total()
            ]
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
    public function store(CourseUserPartnerRequest $request)
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
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Course User Partner.',
            'data'      => $account
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CourseUserPartnerUpdateRequest $request, $id)
    {
        // Initialize
        $user = User::where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'User tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

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
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data Course User Partner.',
            'data'      => $user
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($partnerId, $userId)
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
        $partner = Partner::where('id', $partnerId)->first();
        $user    = User::where('id', $userId)->first();

        if (!$partner) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Partner tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if (!$user) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'User tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if ($user->role_id == 2) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tidak bisa dihapus'
            ]);
        }
       
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
            'status'    => 'success',
            'message'   => 'Data berhasil dihapus'
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
