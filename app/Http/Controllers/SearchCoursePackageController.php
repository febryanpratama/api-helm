<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Majors;
use App\MajorsSubject;
use App\UserCourse;
use App\Checkout;
use App\TheoryLock;
use App\Rating;
use App\User;
use App\CheckoutDetail;
use App\Category;
use App\CourseCategory;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchCoursePackageController extends Controller
{
    public function index()
    {
        // Initialize
        $countCourse = Course::where('is_publish', 1)->count();
        $category    = Category::orderBy('name', 'ASC')->get();

        return view('search.course-package.index', compact('countCourse', 'category'));
    }

    public function courses(Request $request)
    {
        if (request('category') || request('search')) {
            // Search By Category
            if (request('category') != 'all') {
                if (request('category') != 'all' && request('search')) {
                    // Initialize
                    $courseCategory = CourseCategory::where('category_id', request('category'))->pluck('course_id');
                    $courses        = Course::with('majors','userCourse')
                                    ->whereIn('id', $courseCategory)
                                    ->where('is_publish', 1)
                                    ->where('name', 'LIKE', '%'.request('search').'%')
                                    ->where('is_take_down', 0)
                                    ->get()
                                    ->sortByDesc('count_students_join')
                                    ->values();
                } else {
                    // Initialize
                    $courseCategory = CourseCategory::where('category_id', request('category'))->pluck('course_id');
                    $courses        = Course::with('majors','userCourse')
                                        ->whereIn('id', $courseCategory)
                                        ->where('is_publish', 1)
                                        ->where('is_take_down', 0)
                                        ->get()
                                        ->sortByDesc('count_students_join')
                                        ->values();
                }
            } else if (request('search')) {
                // Initialize
                $courses = Course::with('majors','userCourse')
                            ->where('is_publish', 1)
                            ->where('name', 'LIKE', '%'.request('search').'%')
                            ->where('is_take_down', 0)
                            ->get()
                            ->sortByDesc('count_students_join')
                            ->values();
            } else {
                // Initialize
                $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('is_take_down', 0)->get()->sortByDesc('count_students_join')->values();
            }
        } else {
            // Initialize
            $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('is_take_down', 0)->get()->sortByDesc('count_students_join')->values();
        }
        
        // if (request('search')) {
        //     // Initialize
        //     $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('name', 'LIKE', '%'.request('search').'%')->latest()->get()->sortByDesc('count_students_join')->values();
        // } else {
        //     // Initialize
        //     $courses = Course::with('majors','userCourse')->where('is_publish', 1)->latest()->get()->sortByDesc('count_students_join')->values();
        // }

        // Initialize
        $dataFinal = $this->paginate($courses, 20, null, ['path' => $request->fullUrl()]);
        $courses   = $this->_manageData($dataFinal);

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $courses,
            'meta'      => [
                'current_page'      => $dataFinal->currentPage(),
                'from'              => 1,
                'last_page'         => $dataFinal->lastPage(),
                'next_page_url'     => $dataFinal->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $dataFinal->perPage(),
                'prev_page_url'     => $dataFinal->previousPageUrl(),
                'total'             => $dataFinal->total()
            ]
        ]);
    }

    private function _manageData($courses)
    {
        // Initialize
        $data = [];

        foreach ($courses as $val) {
            // Initialize
            $row['id']          = $val->id;
            $row['name']        = $val->name;
            $row['description'] = $val->description;
            $row['slug']        = $val->slug;
            $row['thumbnail']   = $val->thumbnail;
            $row['price']       = $val->price;
            $row['course_type'] = $val->course_type;
            $row['majors']      = $val->majors;
            $row['user_course'] = $val->userCourse;
            $row['is_private']  = $val->is_private;

            $data[] = $row;
        }

        return $data;
    }

    public function redirect(Request $request)
    {
        // Initialize
        $redirect = '';

        if ($request->url()) {
            // Initialize
            $expRedirect = explode('/', $request->url());
            $redirect    = $expRedirect[6];
        }

        if (!auth()->check()) {
            return redirect()->route('auth.signin', 'redirect='.$redirect);
        }

        if (auth()->check() && auth()->user()->role_id == 1) {
            return redirect()->back();
        }

        return redirect()->route('member.course.show', $expRedirect[6]);
    }

    public function show($slug)
    {
        // Initialize
        $course       = Course::where('slug', $slug)->firstOrFail();
        $class        = Majors::where('IDCourse', $course->id)->pluck('id');
        $theory       = MajorsSubject::whereIn('major_id', $class)->count();
        $nowDate      = date('Y-m-d H:i:s');
        $totalRate    = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
        $totalStudent = UserCourse::where('course_id', $course->id)->count();

        return view('member.course.show-not-signin', compact('course', 'theory', 'totalRate', 'totalStudent', 'nowDate'));
    }

    public function majors()
    {
        // Initialize
        $majors = Majors::where(['IDCourse' => request('course_id')])->orderBy('ID', 'ASC')->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $majors
        ]);
    }

    public function rating()
    {
        // Initialize
        $data = Rating::with('user')->where('course_id', request('course_id'))->latest()->get();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function subject()
    {
        // Initialize
        $subjects = MajorsSubject::where('major_id', request('majorId'))->get();
        $major    = Majors::where('ID', request('majorId'))->first();
        $course   = Course::where('id', $major->IDCourse)->first();

        $data = [];
        foreach ($subjects as $key => $val) {
            // Initialize
            $subject = $val->subject;

            $row['ID']           = $subject->ID;
            $row['Name']         = $subject->Name;
            $row['FileType']     = $subject->FileType;
            $row['MajorId']      = $val->major_id;
            $row['courseExists'] = 'n';
            $row['slug']         = $course->slug;
            $row['theoryLock']   = false;

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function buy(Request $request)
    {
        // Initialize
        $course     = Course::where('slug', $request->course)->firstOrFail();
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');
        
        // Check Exists Unique Code
        $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

        if ($checkoutExists) {
            for ($i= 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode = rand(100, 1000);
                $checkoutExists = Checkout::where(['unique_code' => $uniqueCode, 'status_transaction' => 0])->whereDate('expired_transaction', '>=', $nowDate)->first();

                if (!$checkoutExists) {
                    break;
                }
            }
        }

        if ($course->course_type == 2) {
            // Initialize
            $uniqueCode = 0;
        }

        return view('not-logged-in.checkout.index', compact('course','uniqueCode'));
    }

    public function checkout()
    {
        // Create User
        $user    = User::where('email', request('email'))->first();
        $otpCode = rand(1111, 9999);

        // Signin Logic
        if ($user) {
            // Initialize
            $user->password      = bcrypt($otpCode);
            $user->role_id       = 6;
            $user->is_instructor = 0;
            $user->save();
        } else {
            // Initialize
            $referralCodeGenerate = $this->generateRandomString(6);

            // Check Referral Code Exists
            $userReferral = User::where('referral_code', $referralCodeGenerate)->first();

            if ($userReferral) {
                for ($i= 0; $i < 100; $i++) { 
                    // Initialize
                    $referralCodeGenerate = $this->generateRandomString(6);
                    $userReferral         = User::where('referral_code', $referralCodeGenerate)->first();

                    if (!$userReferral) {
                        break;
                    }
                }
            }

            // SignUp Logic
            $user = User::create([
                'email'         => request('email'),
                'name'          => ucfirst(request('name')),
                'role_id'       => 6,
                'password'      => bcrypt($otpCode),
                'is_instructor' => 0,
                'is_active'     => 'y',
                'referral_code' => $referralCodeGenerate,
                'imei'          => request('imei')
            ]);
        }

        // Initialize
        $user->otp = $otpCode;

        // Send Email OTP Code
        \Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));

        // Initialize
        $course        = Course::where('id', request('course-id'))->first();
        $bank          = explode('|', request('bank'));
        $uniqueCode    = ($course->course_type == 2) ? 0 : request('uniqueCode');

        // Create Transaction
        $checkout = Checkout::create([
            'user_id'                => $user->id,
            'total_payment'          => ($course->price_num + $uniqueCode),
            'total_payment_original' => $course->price_num,
            'payment_type'           => request('paymentType'),
            'bank_name'              => $bank[0],
            'no_rek'                 => $bank[1],
            'unique_code'            => $uniqueCode,
            'status_transaction'     => ($course->course_type == 2) ? 1 : 0,
            'status_payment'         => ($course->course_type == 2) ? 1 : 0,
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
            'buy_now'                => 1
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
                'course_start'          => ($course->course_type == 2) ? date('Y-m-d H:i:s') : null,
                'expired_course'        => ($course->course_type == 2) ? $expiredCourse : '',
                'apps_commission'       => ($course->course_type == 2) ? 0 : 5
            ]);

            // Insert To More Table
            if ($checkoutDetail && $course->course_type == 2) {
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
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Transaksi Berhasil',
            'data'      => $checkout
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}