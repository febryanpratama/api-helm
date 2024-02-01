<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\Majors;
use App\Subject;
use App\MajorsSubject;
use App\CourseCategory;
use App\Rating;
use App\UserCourse;
use App\PendingCommission;
use App\Wallet;
use App\HintWidget;
use App\Category;
use App\Company;
use App\User;
use Str;
use Chat;
use DB;

class CourseController extends Controller
{
    public function indexPage()
    {
        // Initialize
        $createCoursePackage = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'create-course-package'])->first();
        $category            = Category::orderBy('name', 'ASC')->get();

        return view('course.index', compact('createCoursePackage', 'category'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request('filter')) {
            // Check course_type Or is_publish
            if (request('filter') == 'paid-course' || request('filter') == 'free-course') {
                // Initialize
                $filter = 1;

                if (request('filter') == 'free-course') {
                    // Initialize
                    $filter = 2;
                }

                $courses = Course::with('majors','userCourse')->where(['user_id' => auth()->user()->id, 'course_type' => $filter])->latest()->paginate(10);
            } else {
                // Initialize
                $filter = 0;

                if (request('filter') == 'course-publish') {
                    // Initialize
                    $filter = 1;
                }

                if (request('filter') == 'all-course') {
                    // Initialize
                    $courses = Course::with('majors','userCourse')->where('user_id', auth()->user()->id)->latest()->paginate(10);
                } else {
                    // Initialize
                    $courses = Course::with('majors','userCourse')->where(['user_id' => auth()->user()->id, 'is_publish' => $filter])->latest()->paginate(10);
                }
            }
        } else {
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
                                        ->where('user_id', auth()->user()->id)
                                        ->get()
                                        ->sortByDesc('count_students_join')
                                        ->values();
                    } else {
                        // Initialize
                        $courseCategory = CourseCategory::where('category_id', request('category'))->pluck('course_id');
                        $courses        = Course::with('majors','userCourse')
                                            ->whereIn('id', $courseCategory)
                                            ->where('is_publish', 1)
                                            ->where('user_id', auth()->user()->id)
                                            ->get()
                                            ->sortByDesc('count_students_join')
                                            ->values();
                    }
                } else if (request('search')) {
                    // Initialize
                    $courses = Course::with('majors','userCourse')
                                ->where('is_publish', 1)
                                ->where('name', 'LIKE', '%'.request('search').'%')
                                ->where('user_id', auth()->user()->id)
                                ->get()
                                ->sortByDesc('count_students_join')
                                ->values();
                } else {
                    // Initialize
                    $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('user_id', auth()->user()->id)->get()->sortByDesc('count_students_join')->values();
                }
            } else {
                // Initialize
                $courses = Course::with('majors','userCourse')->where('user_id', auth()->user()->id)->get()->sortByDesc('count_students_join')->values();
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $courses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check Suspend Institution
        $company = Company::where('ID', auth()->user()->company_id)->first();

        if ($company->IsTakeDown == 1) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tidak bisa menambahkan Paket Kursus! <br> Lembaga Kursus terkena suspend, silahkan hubungi Admin untuk informasi lebih lanjut.'
            ]);
        }

        // Initialize
        $thumbnail = request()->file('upload_file');
        $path      = 'https://hecmedia.org/static/assets/nothumbnail.png';

        if (request('course_type') == 1) {
            $price      = $request->price;
            $priceNum   = str_replace('.', '', $price);
            $commission = 5;
        } else {
            $price      = 0;
            $priceNum   = 0;
            $commission = 0;
        }

        if ($thumbnail) {
            // Check Max Size
            if ($thumbnail->getSize() > 1000000) { // 10 MB
                return response()->json([
                    'status'    => false,
                    'message'   => 'Max Size Thumbnail 10 MB'
                ]);

                die;
            }

            // Initialize
            $path = env('SITE_URL'). '/storage/'.request('upload_file')->store('uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        }

        // Check Is Private
        if (request('is_private') == 1) {
            // Develop Mode
            $developIsOn = false;

            if ($developIsOn) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Tipe Pribadi dalam pengembangan.'
                ]);
            }

            // Initialize
            $data = [
                'name'                          => $request->name,
                'user_id'                       => auth()->user()->id,
                'description'                   => ($request->description) ? $request->description : '-',
                'periode_type'                  => $request->periode_type,
                'periode'                       => $request->periode,
                'course_type'                   => request('course_type'),
                'price'                         => $price,
                'price_num'                     => $priceNum,
                'commission'                    => $commission,
                'is_publish'                    => '0',
                'thumbnail'                     => $path,
                'slug'                          => Str::slug($request->name.'-'.auth()->user()->company->Name.'-'.auth()->user()->id.date('Yds'), '-'),
                'is_private'                    => 1,
                'commission_type'               => $request->commission_type,
                'min_user_joined'               => $request->min_user_joined,
                'max_user_joined'               => $request->max_user_joined,
                'commission_min_user_joined'    => $request->commission_min,
                'commission_max_user_joined'    => $request->commission_max,
                'course_package_category'       => $request->course_package_category
            ];
        } else {
            // Initialize
            $data = [
                'name'                      => $request->name,
                'user_id'                   => auth()->user()->id,
                'description'               => ($request->description) ? $request->description : '-',
                'periode_type'              => $request->periode_type,
                'periode'                   => $request->periode,
                'course_type'               => request('course_type'),
                'price'                     => $price,
                'price_num'                 => $priceNum,
                'commission'                => $commission,
                'is_publish'                => '0',
                'thumbnail'                 => $path,
                'slug'                      => Str::slug($request->name.'-'.auth()->user()->company->Name.'-'.auth()->user()->id.date('Yds'), '-'),
                'course_package_category'   => $request->course_package_category
            ];
        }

        $course = Course::create($data);

        if (request('category')) {
            foreach(request('category') as $val) {
                CourseCategory::create([
                    'course_id'     => $course->id,
                    'category_id'   => $val
                ]);
            }
        }

        if ($course) {
            // Initialize
            $pendingCommission = PendingCommission::where(['downline_id' => auth()->user()->id, 'status' => 0])->first();

            if ($pendingCommission) {
                // Insert Reward From Referral Invite
                Wallet::create([
                    'user_id'           => $pendingCommission->upline_id,
                    'balance'           => 2000,
                    'original_balance'  => 2000,
                    'details'           => 'Referral Invite | '.$pendingCommission->downline_id,
                    'is_verified'       => 1
                ]);

                // Insert Reward From Fill In Refferal Code
                Wallet::create([
                    'user_id'           => $pendingCommission->downline_id,
                    'balance'           => 1000,
                    'original_balance'  => 1000,
                    'details'           => 'Referral Register | '.$pendingCommission->upline_id,
                    'is_verified'       => 1
                ]);

                // Update Status
                $pendingCommission->update(['status' => 1]);
            }

            // Create Group Chat
            $participants = [auth()->user()];

            // Chat
            $conversation = Chat::createConversation($participants)->makePrivate(false);

            if ($conversation) {
                $data = ['title' => $request->name, 'description' => ''];
                $conversation->update(['data' => $data]);

                // Insert To Course Chat
                DB::table('course_chat')->insert([
                    'course_id'         => $course->id,
                    'conversation_id'   => $conversation->id
                ]);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil disimpan',
            'course_id' => $course->id
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        // Initialize
        $sessionAndTheoryTab = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'session-and-theory-tab'])->count();
        $addSessionBtn       = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'create-session-btn'])->count();
        $moreBtn             = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'show-course-package-more-btn'])->count();
        $publishBtn          = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'show-course-package-more-btn'])->count();
        $detailsTheory       = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'show-course-package-details-theory'])->count();
        $courseChat          = DB::table('course_chat')->where('course_id', $course->id)->first();

        // Check Course
        if ($course->user_id != auth()->user()->id) {
            return redirect()->back();
        }

        // Initialize
        $class        = Majors::where('IDCourse', $course->id)->pluck('id');
        $theory       = MajorsSubject::whereIn('major_id', $class)->count();
        $totalRate    = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
        $totalStudent = UserCourse::where('course_id', $course->id)->count();

        return view('course.show', compact('course', 'theory', 'totalRate', 'totalStudent', 'sessionAndTheoryTab', 'addSessionBtn', 'moreBtn', 'publishBtn', 'detailsTheory', 'courseChat'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        // Check Suspend Institution
        $company = Company::where('ID', auth()->user()->company_id)->first();

        if ($company->IsTakeDown == 1) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tidak bisa mengedit Paket Kursus! <br> Lembaga Kursus terkena suspend, silahkan hubungi Admin untuk informasi lebih lanjut.'
            ]);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $course
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        // Check Suspend Institution
        $company = Company::where('ID', auth()->user()->company_id)->first();

        if ($company->IsTakeDown == 1) {
            return response()->json([
                'status'    => false,
                'message'   => 'Tidak bisa mengedit Paket Kursus! <br> Lembaga Kursus terkena suspend, silahkan hubungi Admin untuk informasi lebih lanjut.'
            ]);
        }

        // Initialize
        $thumbnail = request()->file('upload_file');
        $path      = $course->thumbnail;

        if (request('course_type') == 1) {
            $price      = $request->price;
            $priceNum   = str_replace('.', '', $price);
            $commission = 5;
        } else {
            $price      = 0;
            $priceNum   = 0;
            $commission = 0;
        }

        if ($thumbnail) {
            // Check Max Size
            if ($thumbnail->getSize() > 1000000) { // 10 MB
                return response()->json([
                    'status'    => false,
                    'message'   => 'Max Size Thumbnail 10 MB'
                ]);

                die;
            }

            // Unlink File
            $explodePath = explode('/', $course->thumbnail);

            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[7]);

            // Initialize
            $path = env('SITE_URL'). '/storage/'.request('upload_file')->store('uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        }

        if (request('is_private') == 1) {
            // Develop Mode
            $developIsOn = false;

            if ($developIsOn) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Tipe Pribadi dalam pengembangan.'
                ]);
            }

            // Initialize
            $course->update([
                'name'                          => $request->name,
                'description'                   => ($request->description) ? $request->description : '-',
                'periode_type'                  => $request->periode_type,
                'periode'                       => $request->periode,
                'course_type'                   => request('course_type'),
                'price'                         => $price,
                'price_num'                     => $priceNum,
                'commission'                    => $commission,
                'thumbnail'                     => $path,
                'is_private'                    => request('is_private'),
                'commission_type'               => $request->commission_type,
                'min_user_joined'               => $request->min_user_joined,
                'max_user_joined'               => $request->max_user_joined,
                'commission_min_user_joined'    => $request->commission_min,
                'commission_max_user_joined'    => $request->commission_max,
                'course_package_category'       => $request->course_package_category
            ]);
        } else {
            $course->update([
                'name'                      => $request->name,
                'description'               => ($request->description) ? $request->description : '-',
                'periode_type'              => $request->periode_type,
                'periode'                   => $request->periode,
                'course_type'               => request('course_type'),
                'price'                     => $price,
                'price_num'                 => $priceNum,
                'commission'                => $commission,
                'thumbnail'                 => $path,
                'is_private'                => request('is_private'),
                'course_package_category'   => $request->course_package_category
            ]);
        }

        if (request('category')) {
            // Delete All Category
            CourseCategory::where('course_id', $course->id)->delete();

            foreach(request('category') as $val) {
                CourseCategory::create([
                    'course_id'     => $course->id,
                    'category_id'   => $val
                ]);
            }
        } else {
            // Delete All Category
            CourseCategory::where('course_id', $course->id)->delete();
        }

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
    public function destroy(Course $course)
    {
        // Validate Account
        if ($course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => false,
                'message'   => 'Paket Kursus gagal dihapus'
            ]);

            die;
        }

        // Check User Course
        $userCourse = UserCourse::where('course_id', $course->id)->count();

        if ($userCourse > 0) {
            return response()->json([
                'status'    => false,
                'message'   => 'Paket Kursus tidak bisa di hapus'
            ]);

            die;
        }

        // Unlink File
        $explodePath = explode('/', $course->thumbnail);
        
        if (count($explodePath) >= 7) {
            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[7]);
        }

        $course->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }

    public function courseCount(Course $course)
    {
        // Initialize
        $filter = 0;
        
        // Check is_admin_confirm Or is_publish
        if (request('filter') == 'course-waiting-approve' || request('filter') == 'course-approved' || request('filter') == 'course-rejected') {
            if (request('filter') == 'course-approved') {
                // Initialize
                $filter = 1;
            } else if (request('filter') == 'course-rejected') {
                // Initialize
                $filter = 2;
            }

            $courses = Course::where(['user_id' => auth()->user()->id, 'is_admin_confirm' => $filter])->count();
        } else {
            if (request('filter') == 'course-publish') {
                // Initialize
                $filter = 1;
            }

            if (request('filter') == 'all-course') {
                // Initialize
                $courses = Course::where('user_id', auth()->user()->id)->count();
            } else {
                // Initialize
                $courses = Course::where(['user_id' => auth()->user()->id, 'is_publish' => $filter])->count();
            }
        }
        
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $courses
        ]);
    }

    public function publish(Course $course)
    {
        $course->update(['is_publish' => request('status')]);

        return response()->json([
            'status'    => true,
            'message'   => (request('status') == 1) ? 'Paket Kursus Didaftarkan' : 'Data berhasil diperbarui'
        ]);
    }

    public function preview($slug, Subject $subject)
    {
        // Initialize
        $course      = Course::where('slug', $slug)->firstOrFail();
        $countTheory = 0;

        foreach($course->majors as $val) {
            $countTheory += count($val->subject);
        }

        return view('course.preview', compact('course', 'subject', 'countTheory'));
    }

    public function countData()
    {
        // Initialize
        $session      = Majors::where('IDCourse', request('courseId'));
        $sessionId    = $session->pluck('id');
        $totalSession = $session->count();
        $theory       = MajorsSubject::whereIn('major_id', $sessionId)->count();

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => [
                'session' => $totalSession,
                'theory'  => $theory
            ]
        ]);
    }

    public function createNewGroupChat(Course $course)
    {
        // Initialize
        $courseChat = DB::table('course_chat')->where('course_id', $course->id)->first();

        if ($courseChat) {
            return response()->json([
                'status'    => false,
                'message'   => 'Obroloan Grup Sudah dibuat!'
            ]);
        }

        // Create Group Chat
        $participants = [auth()->user()];

        // Chat
        $conversation = Chat::createConversation($participants)->makePrivate(false);

        if ($conversation) {
            $data = ['title' => $course->name, 'description' => ''];
            $conversation->update(['data' => $data]);

            // Insert To Course Chat
            $administrator = DB::table('course_chat')->insert([
                'course_id'         => $course->id,
                'conversation_id'   => $conversation->id
            ]);

            if ($administrator) {
                // Get All User Joined By Course Id
                $userCourse = UserCourse::where('course_id', $course->id)->pluck('user_id');

                foreach($userCourse as $val) {
                    // Initialize
                    $participant     = User::find($val);
                    $conversation    = Chat::conversations()->getById($conversation->id);
                    $addParticipants = Chat::conversation($conversation)->addParticipants([$participant]);
                }
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Obroloan Grup berhasil dibuat'
        ]);
    }
}
