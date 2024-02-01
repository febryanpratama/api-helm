<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\StudentCourseResource;
use App\Course;
// use App\UserCourse;
use App\MajorsSubject;
use App\TheoryLock;
use App\Majors;
use App\Rating;
use App\Subject;
use App\Task;
use App\TaskAttachment;
use App\CourseTermin;
use App\LandingPromo;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $filter = request('filter');
        $search = request('search');
        $cpci   = request('course_package_category_id');

        if (request('my-course')) {
            // Initialize
            $userCourse = UserCourse::where('user_id', auth()->user()->id)->pluck('course_id');

            if ($search) {
                $courses = Course::whereIn('id', $userCourse)->where(['is_publish' => 1, 'is_take_down' => '0'])->where('name', 'LIKE', '%'.$search.'%')->latest()->get();
            } elseif ($filter) {
                if ($filter == 'New') {
                    // Initialize
                    $courses = Course::whereIn('id', $userCourse)->where(['is_publish' => 1, 'is_take_down' => '0'])->latest()->get();
                } elseif ($filter == 'Free') {
                    // Initialize
                    $courses = Course::whereIn('id', $userCourse)->where(['is_publish' => 1, 'course_type' => 2, 'is_take_down' => '0'])->latest()->get();
                } else {
                    // Initialize
                    $courses = Course::whereIn('id', $userCourse)->where(['is_publish' => 1, 'is_take_down' => '0'])->get()->sortByDesc('count_students_join')->values();
                }
            }

            $courses = Course::whereIn('id', $userCourse)->where('is_publish', 1)->latest()->get();
        } else if ($cpci) {
            // Initialize
            $courses = Course::where(['is_publish' => 1, 'is_take_down' => '0', 'course_package_category' => request('course_package_category_id')])->latest()->get();
        } else {
            if ($search) {
                // Initialize
                $courses = Course::where('is_publish', 1)->where('name', 'LIKE', '%'.$search.'%')->where('is_take_down', '0')->latest()->get();
            } elseif ($filter) {
                // Check Filter
                if ($filter) {
                    if ($filter == 'New') {
                        // Initialize
                        $courses = Course::where('is_publish', 1)->where('is_take_down', '0')->latest()->get();
                    } elseif ($filter == 'Free') {
                        // Initialize
                        $courses = Course::where(['is_publish' => 1, 'course_type' => 2])->where('is_take_down', '0')->latest()->get();
                    } else {
                        // Initialize
                        $courses = Course::where(['is_publish' => 1])->where('is_take_down', '0')->get()->sortByDesc('count_students_join')->values();
                    }
                } else {
                    // Initialize
                    $courses = Course::where('is_publish', 1)->where('is_take_down', '0')->latest()->get();
                }
            } else {
                // Initialize
                $courses = Course::where('is_publish', 1)->where('is_take_down', '0')->latest()->get();
            }
        }

        // Initialize
        $data = [];

        // Custom Paginate
        $courses = $this->paginate($courses, 20, null, ['path' => $request->fullUrl()]);

        foreach ($courses as $course) {
            // Initialize
            $formula = ($course->discount/100) * $course->price_num;
            
            $row['id']               = $course->id;
            $row['name']             = $course->name;
            $row['description']      = $course->description;
            $row['thumbnail']        = $course->thumbnail;
            $row['periode_type']     = $course->periode_type;
            $row['periode']          = $course->periode;
            $row['course_type']      = $course->course_type;
            $row['price']            = $course->price;
            $row['price_num']        = $course->price_num;
            $row['discount']         = $course->discount;
            $row['price_after_disc'] = ($course->discount > 0) ? ($course->price_num - $formula) : 0;
            $row['commission']       = $course->commission;
            $row['slug']             = $course->slug;
            $row['is_publish']       = ($course->is_publish) ? true : false;
            $row['is_admin_confirm'] = $course->is_admin_confirm;
            $row['is_private']       = $course->is_private;

            // Initialize
            $class          = Majors::where('IDCourse', $course->id)->pluck('id');
            $theory         = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate      = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;

            $row['total_rating']                = ($totalRate) ? $totalRate : 0;
            $row['total_session']               = count($course->majors);
            $row['course_package_category']     = courseCategory($course->course_package_category);
            $row['course_package_category_id']  = $course->course_package_category;
            $row['category_id']                 = ($course->courseCategory) ? $course->courseCategory->category_id : null;

            // Task
            $task           = Task::whereIn('major_id', $class)->pluck('id');
            $countTask      = Task::whereIn('major_id', $class)->count();
            $taskAttachment = TaskAttachment::whereIn('task_id', $task)->where('user_id', auth()->user()->id)->count();

            $row['task'] = [
                'total_task'      => $countTask,
                'completed_task'  => $taskAttachment,
                'unfinished_task' => ($countTask - $taskAttachment)
            ];

            // Termin
            $termin = CourseTermin::where('course_id', $course->id)->first();

            $row['is_termin']                    = $course->is_termin;
            $row['instalment_title']             = ($termin) ? $termin->instalment_title : null;
            $row['interval']                     = ($termin) ? $termin->interval : null;
            $row['down_payment']                 = ($termin) ? $termin->down_payment : null;
            $row['interest']                     = ($termin) ? $termin->interest : null;
            $row['is_immovable_object']          = $course->is_immovable_object;
            $row['back_payment_status']          = $course->back_payment_status;
            $row['end_time_min']                 = $course->end_time_min;
            $row['start_time_min']               = $course->start_time_min;
            $row['period_day']                   = $course->period_day;
            $row['termin_percentage']            = ($course->courseTermin) ? array_map('intval', $course->courseTermin->value) : null;
            $row['completion_percentage']        = ($course->courseTermin) ? array_map('intval', $course->courseTermin->completion_percentage) : null;
            $row['completion_percentage_detail'] = ($course->courseTermin) ? $course->courseTermin->completion_percentage_detail : null;
            $row['dp_duedate_number']            = ($course->courseTermin) ? (int)$course->courseTermin->dp_duedate_number : null;
            $row['dp_duedate_name']              = ($course->courseTermin) ? $course->courseTermin->dp_duedate_name : null;
            $row['termin_duedate_number']        = ($course->courseTermin) ? array_map('intval', $course->courseTermin->termin_duedate_number) : null;
            $row['termin_duedate_name']          = ($course->courseTermin) ? $course->courseTermin->termin_duedate_name : null;
            $row['is_percentage']                = ($course->courseTermin) ? $course->courseTermin->is_percentage : null;
            $row['is_hidden']                    = ($course->courseTermin) ? $course->courseTermin->is_hidden : null;

            $row['promotion']                    = LandingPromo::where('product_id', $course->id)->where('end_period', '>=', date('Y-m-d H:i:s'))->where('start_period', '<=', date('Y-m-d H:i:s'))->get();
            $row['create_at']        = $course->created_at;
            $row['update_at']        = $course->updated_at;

            $data[] = $row;
        }

        if (request('my-course')) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil mendapatkan data paket kursus.',
                'data'      => $data
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $courses->currentPage(),
                'from'              => 1,
                'last_page'         => $courses->lastPage(),
                'next_page_url'     => $courses->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $courses->perPage(),
                'prev_page_url'     => $courses->previousPageUrl(),
                'total'             => $courses->total()
            ]
        ]);
    }

    public function show($slug)
    {
        // Initialize
        $course = Course::where('slug', $slug)->first();

        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        return new StudentCourseResource($course);
    }

    public function unlockTheory($subjectId)
    {
        // Initialize
        $majorsSubject = MajorsSubject::where('subject_id', $subjectId)->first();

        if (!$majorsSubject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Materi tidak ditemukan'
            ]);
        }

        $majors             = Majors::where('id', $majorsSubject->major_id)->first();
        $course             = Course::where('id', $majors->IDCourse)->firstOrFail();
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

            if ($subjectId == $latestSubjectId->subject_id) {
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

        return response()->json([
            'status'    => 'success',
            'message'   => 'Unlocked'
        ]);
    }

    public function showFile($id)
    {
        // Check Auth Token
        $staticToken = '695f5bbebbe97d677bcb6c111dabc1123c75fee1'; // ruangajar.com

        if (request('token') != $staticToken) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Token Salah!'
            ]);
        }

        // Initialize
        $theory = Subject::where('id', $id)->first();

        if ($theory) {
            if ($theory->FileType == 1) {
                // Initialize
                $expTheory = explode('/', $theory->Path);
                $fullPath  = $expTheory[3].'/'.$expTheory[4].'/'.$expTheory[5].'/'.$expTheory[6].'/'.$expTheory[7].'/'.$expTheory[8].'/'.$expTheory[9];

                return response()->file($fullPath);
            }
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Data tidak ditemukan'
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
