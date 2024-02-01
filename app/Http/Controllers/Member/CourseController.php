<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Course;
use App\Majors;
use App\MajorsSubject;
use App\UserCourse;
use App\Checkout;
use App\CheckoutDetail;
use App\Subject;
use App\TheoryLock;
use App\Rating;
use App\Category;
use App\CourseCategory;
use App\HintWidget;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseController extends Controller
{
    public function index()
    {
        // Initialize
        $category = Category::orderBy('name', 'ASC')->get();
        $moreBtn  = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'more-btn-in-show-course'])->count();
        $cartBtn  = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'add-to-cart-in-btn'])->count();

        if (request('myCourse') || request('my-course')) {
            // Initialize
            $myCourse    = UserCourse::where('user_id', auth()->user()->id)->pluck('course_id');
            $countCourse = Course::where('is_publish', 1)->whereIn('id', $myCourse)->count();
        } else {
            // Initialize
            $countCourse = Course::where('is_publish', 1)->count();
        }

        return view('member.course.index', compact('countCourse', 'category', 'moreBtn', 'cartBtn'));
    }

    public function showAll(Request $request)
    {
        // Initialize
        $myCourse = UserCourse::where('user_id', auth()->user()->id)->pluck('course_id');

        if (request('category') || request('search')) {
            // Search By Category
            if (request('category') != 'all') {
                if (request('category') != 'all' && request('search')) {
                    // Initialize
                    $courseCategory = CourseCategory::where('category_id', request('category'))->pluck('course_id');

                    if (request('myCourse') || request('my-course')) {
                        // Initialize
                        $courses = Course::with('majors','userCourse')
                                    ->whereIn('id', $courseCategory)
                                    ->whereIn('id', $myCourse)
                                    ->where('is_publish', 1)
                                    ->where('name', 'LIKE', '%'.request('search').'%')
                                    ->where('is_take_down', '0')
                                    ->get()
                                    ->sortByDesc('count_students_join')
                                    ->values();
                    } else {
                        // Initialize
                        $courses = Course::with('majors','userCourse')
                                    ->whereIn('id', $courseCategory)
                                    ->where('is_publish', 1)
                                    ->where('name', 'LIKE', '%'.request('search').'%')
                                    ->where('is_take_down', '0')
                                    ->get()
                                    ->sortByDesc('count_students_join')
                                    ->values();
                    }
                } else {
                    // Initialize
                    $courseCategory = CourseCategory::where('category_id', request('category'))->pluck('course_id');

                    if (request('myCourse') || request('my-course')) {
                        // Initialize
                        $courses = Course::with('majors','userCourse')
                                    ->whereIn('id', $courseCategory)
                                    ->whereIn('id', $myCourse)
                                    ->where('is_publish', 1)
                                    ->get()
                                    ->sortByDesc('count_students_join')
                                    ->values();
                    } else {
                        $courses = Course::with('majors','userCourse')
                                    ->whereIn('id', $courseCategory)
                                    ->where('is_publish', 1)
                                    ->where('is_take_down', '0')
                                    ->get()
                                    ->sortByDesc('count_students_join')
                                    ->values();
                    }
                }
            } else if (request('search')) {
                if (request('myCourse') || request('my-course')) {
                    // Initialize
                    $courses = Course::with('majors','userCourse')
                                ->where('is_publish', 1)
                                ->whereIn('id', $myCourse)
                                ->where('name', 'LIKE', '%'.request('search').'%')
                                ->get()
                                ->sortByDesc('count_students_join')
                                ->values();
                } else {
                    // Initialize
                    $courses = Course::with('majors','userCourse')
                                ->where('is_publish', 1)
                                ->where('name', 'LIKE', '%'.request('search').'%')
                                ->where('is_take_down', '0')
                                ->get()
                                ->sortByDesc('count_students_join')
                                ->values();
                }
            } else {
                if (request('myCourse') || request('my-course')) {
                    // Initialize
                    $courses = Course::with('majors','userCourse')->where('is_publish', 1)->whereIn('id', $myCourse)->get()->sortByDesc('count_students_join')->values();
                } else {
                    // Initialize
                    $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('is_take_down', '0')->get()->sortByDesc('count_students_join')->values();
                }
            }
        } else {
            if (request('myCourse') || request('my-course')) {
                // Initialize
                $courses  = Course::with('majors','userCourse')->where('is_publish', 1)->whereIn('id', $myCourse)->get()->sortByDesc('count_students_join')->values();
            } else {
                // Initialize
                $courses = Course::with('majors','userCourse')->where('is_publish', 1)->where('is_take_down', '0')->get()->sortByDesc('count_students_join')->values();
            }
        }

        // Initialize
        $dataFinal = $this->paginate($courses, 20, null, ['path' => $request->fullUrl()]);
        $courses   = $this->_manageData($dataFinal);

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => [
                'courses'   => $courses
            ],
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

            // Check User Have Course
            $userCourse = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => $val->id])->first();

            if ($userCourse) {
                $row['user_have_course'] = true;
            } else {
                $row['user_have_course'] = false;
            }

            // Initialize
            $transactionDetails = CheckoutDetail::where(['user_id' => auth()->user()->id, 'course_id' => $val->id])->first();

            if ($transactionDetails && !$transactionDetails->checkout->status_payment) {
                $status             = 'complate-payment';
                $row['checkout_id'] = $transactionDetails->checkout->id;
            } else if ($transactionDetails && $transactionDetails->checkout->status_payment) {
                $status = 'learn-course';

                // Initialize
                $theoryLock        = TheoryLock::where(['user_id' => auth()->user()->id, 'course_id' => $val->id])->first();
                $row['subject_id'] = ($theoryLock) ? $theoryLock->subject_id + 1 : 0;
            } else {
                $status = 'buy';
            }

            $row['status_payment'] = $status;

            $data[] = $row;
        }

        return $data;
    }

    public function show($slug)
    {
        // Initialize
        $course       = Course::where('slug', $slug)->firstOrFail();
        $class        = Majors::where('IDCourse', $course->id)->pluck('id');
        $theory       = MajorsSubject::whereIn('major_id', $class)->count();
        $nowDate      = date('Y-m-d H:i:s');
        $purchased    = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->whereDate('course_expired', '>=', $nowDate)->first();
        $checkoutDt   = CheckoutDetail::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();
        $theoryLock   = TheoryLock::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->count();
        $rating       = Rating::where(['course_id' => $course->id, 'user_id' => auth()->user()->id])->first();
        $totalRate    = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
        $totalStudent = UserCourse::where('course_id', $course->id)->count();

        if ($theoryLock == 1) {
            $percentage = 0;
        } else {
            $percentage = ($theoryLock/$theory) * 100;
        }

        return view('member.course.show', compact('course', 'theory', 'purchased', 'percentage', 'rating', 'totalRate', 'totalStudent', 'nowDate', 'checkoutDt'));
    }

    public function learn($slug, Subject $subject)
    {
        // Initialize
        $course             = Course::where('slug', $slug)->firstOrFail();
        $majorsSubject      = MajorsSubject::where('subject_id', $subject->ID)->firstOrFail();
        $theoryLockExists   = TheoryLock::where(['user_id' => auth()->user()->id, 'major_id' => $majorsSubject->major_id])->pluck('subject_id');
        $subjects           = MajorsSubject::where('major_id', $majorsSubject->major_id)
                            ->whereNotIn('subject_id', $theoryLockExists)
                            ->pluck('subject_id');
        $countTheory        = 0;

        foreach($course->majors as $val) {
            $countTheory += count($val->subject);
        }

        if (count($subjects) > 0) {
            // Check Subject Exist
            $theoryLock  = TheoryLock::where(['user_id' => auth()->user()->id, 'subject_id' => $subjects[0]])->first();

            if (!$theoryLock) {
                TheoryLock::create([
                    'user_id'    => auth()->user()->id,
                    'course_id'  => $course->id,
                    'major_id'   => $majorsSubject->major_id,
                    'subject_id' => $subjects[0]
                ]);
            }
        } else {
            // Initialize
            $latestSubjectId = MajorsSubject::where('major_id', $majorsSubject->major_id)->orderBy('id', 'DESC')->first();

            if ($subject->ID == $latestSubjectId->subject_id) {
                // Initialize
                $majorsExists = TheoryLock::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->groupBy('major_id')->pluck('major_id');
                $majors       = Majors::where('IDCourse', $course->id)->whereNotIn('ID', $majorsExists)->pluck('ID');

                if (count($majors) > 0) {
                    $subjects     = MajorsSubject::where('major_id', $majors[0])
                                        ->whereNotIn('subject_id', $theoryLockExists)
                                        ->pluck('subject_id');

                    if (count($subjects) > 0) {
                        // Check Subject Exist
                        $theoryLock  = TheoryLock::where(['user_id' => auth()->user()->id, 'subject_id' => $subjects[0]])->first();

                        if (!$theoryLock) {
                            TheoryLock::create([
                                'user_id'    => auth()->user()->id,
                                'course_id'  => $course->id,
                                'major_id'   => $majorsSubject->major_id,
                                'subject_id' => $subjects[0]
                            ]);
                        }
                    }  
                }
            }
        }

        return view('member.course.learn', compact('course', 'subject', 'countTheory'));
    }

    function array_default_key($array) {
        $arrayTemp = array();
        $i = 0;
        foreach ($array as $key => $val) {
            $arrayTemp[$i] = $val;
            $i++;
        }
        return $arrayTemp;
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
