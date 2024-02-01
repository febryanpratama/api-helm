<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\StudentCourseResource;
use App\Course;
use App\Category;
use App\CourseCategory;
use App\Rating;
use App\Majors;
use App\MajorsSubject;
// use App\UserCourse;
use App\User;
use App\CourseTermin;
use App\CategoryTransactionAutocomplete;
use App\CustomDocumentInput;
use App\LandingPromo;
use DB;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $search             = request('search');
        $filterByCategory   = request('course_package_category_id');
        $data               = [];

        // Get data
        $products = DB::table('course')
                    ->join('users', 'users.id', '=', 'course.user_id')
                    ->join('company', 'users.company_id', '=', 'company.ID')
                    ->join('unit', 'course.unit_id', '=', 'unit.id')
                    ->select(
                        'course.*',
                        'users.id as user_id',
                        'users.name as user_name',
                        'company.Name as company_name',
                        'company.ID as company_id',
                        'company.Status as company_verify',
                        'unit.name as unit_name'
                    )
                    ->where('course.is_publish', 1)
                    ->where('course.is_take_down', 0)
                    ->where('course.name', 'LIKE', '%'.request('search').'%')
                    ->where(function ($query) {
                        // Validation for product type (Product or Service)
                        if (request('course_package_category_id') != null) {
                            $query->where('course.course_package_category', request('course_package_category_id'));
                        }

                        if (request('web')) {
                            $query->where('company.Status', 2);
                        } else {
                            $query->where('company.Status', 1);
                        }
                    })
                    ->latest()
                    ->paginate(20);

        foreach ($products as $val) {
            // Initialize
            $formula = ($val->discount/100) * $val->price_num;
            
            $row['id']               = $val->id;
            $row['name']             = $val->name;
            $row['description']      = $val->description;
            $row['thumbnail']        = $val->thumbnail;
            $row['periode_type']     = $val->periode_type;
            $row['periode']          = $val->periode;
            $row['course_type']      = $val->course_type;
            $row['price']            = $val->price;
            $row['price_num']        = $val->price_num;
            $row['discount']         = $val->discount;
            $row['price_after_disc'] = ($val->discount > 0) ? ($val->price_num - $formula) : 0;
            $row['weight']           = $val->weight;
            $row['unit_id']          = $val->unit_id;
            $row['unit_name']        = $val->unit_name;
            $row['commission']       = $val->commission;
            $row['slug']             = $val->slug;
            $row['is_publish']       = ($val->is_publish) ? true : false;
            $row['is_take_down']     = ($val->is_take_down) ? true : false;
            $row['is_admin_confirm'] = $val->is_admin_confirm;
            $row['is_sp']            = $val->is_sp;
            $row['sp_file']          = $val->sp_file;

            // Initialize
            $class      = Majors::where('IDCourse', $val->id)->pluck('id');
            $theory     = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate  = Rating::where('course_id', $val->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                 = ($totalRate) ? $totalRate : 0;
            $row['total_customer']               = $totalBuyer;
            $row['course_package_category']      = courseCategory($val->course_package_category);
            $row['course_package_category_id']   = $val->course_package_category;

            // Get Category
            $courseCategory = CourseCategory::where('course_id', $val->id)->first();

            $row['category_id']                  = ($courseCategory) ? $courseCategory->category_id : null;
            $row['is_immovable_object']          = $val->is_immovable_object;
            $row['back_payment_status']          = $val->back_payment_status;
            $row['end_time_min']                 = $val->end_time_min;
            $row['start_time_min']               = $val->start_time_min;
            $row['period_day']                   = $val->period_day;

            // Get Course Termin
            $courseTermin = CourseTermin::where('course_id', $val->id)->first();

            $row['termin_percentage']            = ($courseTermin) ? array_map('intval', $courseTermin->value) : null;
            $row['completion_percentage']        = ($courseTermin) ? array_map('intval', $courseTermin->completion_percentage) : null;
            $row['completion_percentage_detail'] = ($courseTermin) ? $courseTermin->completion_percentage_detail : null;
            $row['dp_duedate_number']            = ($courseTermin) ? (int)$courseTermin->dp_duedate_number : null;
            $row['dp_duedate_name']              = ($courseTermin) ? $courseTermin->dp_duedate_name : null;
            $row['termin_duedate_number']        = ($courseTermin) ? $courseTermin->termin_duedate_number : null;
            $row['termin_duedate_name']          = ($courseTermin) ? $courseTermin->termin_duedate_name : null;
            $row['is_percentage']                = ($courseTermin) ? $courseTermin->is_percentage : null;
            $row['is_hidden']                    = ($courseTermin) ? $courseTermin->is_hidden : null;

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            // Get Custom Document Input
            $customDocumentInputs = CustomDocumentInput::where('course_id', $val->id)->get();

            if ($customDocumentInputs) {
                foreach ($customDocumentInputs as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added
            if ($courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = false;
            } 

            $row['is_question_required']            = ($categoryTransactionAutocomplete) ? true : false;
            $row['custom_document_input']           = $customDocumentInput;
            $row['custom_document_input_required']  = $customDocumentInputRequired;
            $row['company_name']                    = $val->company_name;
            $row['user_details']                    = [
                                                        'id'        => $val->user_id,
                                                        'store_id'  => $val->company_id
                                                    ];
            // $row['store']                           = $val->user->company;
            $row['promotion']                       = LandingPromo::where('product_id', $val->id)->where('end_period', '>=', date('Y-m-d H:i:s'))->where('start_period', '<=', date('Y-m-d H:i:s'))->get();
            $row['create_at']                       = $val->created_at;
            $row['update_at']                       = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $products->currentPage(),
                'from'              => 1,
                'last_page'         => $products->lastPage(),
                'next_page_url'     => $products->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $products->perPage(),
                'prev_page_url'     => $products->previousPageUrl(),
                'total'             => $products->total()
            ]
        ]);
    }

    public function courseCategory(Request $request, $id) {
        // Initialize
        $search             = request('search');
        $filterByCategory   = request('course_package_category_id');
        $data               = [];

        // Get data
        $products = DB::table('course')
                    ->join('users', 'users.id', '=', 'course.user_id')
                    ->join('company', 'users.company_id', '=', 'company.ID')
                    ->join('unit', 'course.unit_id', '=', 'unit.id')
                    ->join('course_category', 'course.id', '=', 'course_category.course_id')
                    ->select(
                        'course.*',
                        'users.name as user_name',
                        'company.Name as company_name',
                        'company.Status as company_verify',
                        'unit.name as unit_name',
                        'course_category.category_id as category_id'
                    )
                    ->where('course.is_publish', 1)
                    ->where('course.is_take_down', 0)
                    ->where('course.name', 'LIKE', '%'.request('search').'%')
                    ->where(function ($query) {
                        // Validation for product type (Product or Service)
                        if (request('course_package_category_id') != null) {
                            $query->where('course.course_package_category', request('course_package_category_id'));
                        }
                    })
                    ->where('company.Status', 1)
                    ->where('course_category.category_id', $id)
                    ->latest()
                    ->paginate(20);

        foreach ($products as $val) {
            // Initialize
            $formula = ($val->discount/100) * $val->price_num;
            
            $row['id']               = $val->id;
            $row['name']             = $val->name;
            $row['description']      = $val->description;
            $row['thumbnail']        = $val->thumbnail;
            $row['periode_type']     = $val->periode_type;
            $row['periode']          = $val->periode;
            $row['course_type']      = $val->course_type;
            $row['price']            = $val->price;
            $row['price_num']        = $val->price_num;
            $row['discount']         = $val->discount;
            $row['price_after_disc'] = ($val->discount > 0) ? ($val->price_num - $formula) : 0;
            $row['weight']           = $val->weight;
            $row['unit_id']          = $val->unit_id;
            $row['unit_name']        = $val->unit_name;
            $row['commission']       = $val->commission;
            $row['slug']             = $val->slug;
            $row['is_publish']       = ($val->is_publish) ? true : false;
            $row['is_admin_confirm'] = $val->is_admin_confirm;
            $row['is_sp']            = $val->is_sp;
            $row['sp_file']          = $val->sp_file;

            // Initialize
            $class      = Majors::where('IDCourse', $val->id)->pluck('id');
            $theory     = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate  = Rating::where('course_id', $val->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                 = ($totalRate) ? $totalRate : 0;
            $row['total_customer']               = $totalBuyer;
            $row['course_package_category']      = courseCategory($val->course_package_category);
            $row['course_package_category_id']   = $val->course_package_category;

            // Get Category
            $courseCategory = CourseCategory::where('course_id', $val->id)->first();

            $row['category_id']                  = ($courseCategory) ? $courseCategory->category_id : null;
            $row['is_immovable_object']          = $val->is_immovable_object;
            $row['back_payment_status']          = $val->back_payment_status;
            $row['end_time_min']                 = $val->end_time_min;
            $row['start_time_min']               = $val->start_time_min;
            $row['period_day']                   = $val->period_day;

            // Get Course Termin
            $courseTermin = CourseTermin::where('course_id', $val->id)->first();

            $row['termin_percentage']            = ($courseTermin) ? array_map('intval', $courseTermin->value) : null;
            $row['completion_percentage']        = ($courseTermin) ? array_map('intval', $courseTermin->completion_percentage) : null;
            $row['completion_percentage_detail'] = ($courseTermin) ? $courseTermin->completion_percentage_detail : null;
            $row['dp_duedate_number']            = ($courseTermin) ? (int)$courseTermin->dp_duedate_number : null;
            $row['dp_duedate_name']              = ($courseTermin) ? $courseTermin->dp_duedate_name : null;
            $row['termin_duedate_number']        = ($courseTermin) ? $courseTermin->termin_duedate_number : null;
            $row['termin_duedate_name']          = ($courseTermin) ? $courseTermin->termin_duedate_name : null;
            $row['is_percentage']                = ($courseTermin) ? $courseTermin->is_percentage : null;
            $row['is_hidden']                    = ($courseTermin) ? $courseTermin->is_hidden : null;

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            // Get Custom Document Input
            $customDocumentInputs = CustomDocumentInput::where('course_id', $val->id)->get();

            if ($customDocumentInputs) {
                foreach ($customDocumentInputs as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added
            if ($courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = false;
            } 

            $row['is_question_required']            = ($categoryTransactionAutocomplete) ? true : false;
            $row['custom_document_input']           = $customDocumentInput;
            $row['custom_document_input_required']  = $customDocumentInputRequired;
            $row['create_at']                       = $val->created_at;
            $row['update_at']                       = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $products->currentPage(),
                'from'              => 1,
                'last_page'         => $products->lastPage(),
                'next_page_url'     => $products->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $products->perPage(),
                'prev_page_url'     => $products->previousPageUrl(),
                'total'             => $products->total()
            ]
        ]);
    }

    public function courseInstructor(Request $request, $instructorId) {
        // Initialize
        $seller = User::where('id', $instructorId)->first();
        $data   = [];

        if (!$seller) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Seller dengan ID ('.$instructorId.') tidak ditemukan.'
            ]);
        }

        // Get Data
        $products = DB::table('course')
                    ->join('users', 'users.id', '=', 'course.user_id')
                    ->join('company', 'users.company_id', '=', 'company.ID')
                    ->join('unit', 'course.unit_id', '=', 'unit.id')
                    ->select(
                        'course.*',
                        'users.name as user_name',
                        'company.Name as company_name',
                        'company.Status as company_verify',
                        'unit.name as unit_name'
                    )
                    ->where('course.user_id', $instructorId)
                    ->where('course.is_publish', 1)
                    ->where('course.is_take_down', 0)
                    ->where('course.name', 'LIKE', '%'.request('search').'%')
                    ->where(function ($query) {
                        // Validation for product type (Product or Service)
                        if (request('course_package_category_id') != null) {
                            $query->where('course.course_package_category', request('course_package_category_id'));
                        }
                    })
                    ->where('company.Status', 1)
                    ->latest()
                    ->paginate(20);

        foreach ($products as $val) {
            // Initialize
            $formula = ($val->discount/100) * $val->price_num;
            
            $row['id']               = $val->id;
            $row['name']             = $val->name;
            $row['description']      = $val->description;
            $row['thumbnail']        = $val->thumbnail;
            $row['periode_type']     = $val->periode_type;
            $row['periode']          = $val->periode;
            $row['course_type']      = $val->course_type;
            $row['price']            = $val->price;
            $row['price_num']        = $val->price_num;
            $row['discount']         = $val->discount;
            $row['price_after_disc'] = ($val->discount > 0) ? ($val->price_num - $formula) : 0;
            $row['weight']           = $val->weight;
            $row['unit_id']          = $val->unit_id;
            $row['unit_name']        = $val->unit_name;
            $row['commission']       = $val->commission;
            $row['slug']             = $val->slug;
            $row['is_publish']       = ($val->is_publish) ? true : false;
            $row['is_admin_confirm'] = $val->is_admin_confirm;
            $row['is_sp']            = $val->is_sp;
            $row['sp_file']          = $val->sp_file;

            // Initialize
            $class      = Majors::where('IDCourse', $val->id)->pluck('id');
            $theory     = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate  = Rating::where('course_id', $val->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                 = ($totalRate) ? $totalRate : 0;
            $row['total_customer']               = $totalBuyer;
            $row['course_package_category']      = courseCategory($val->course_package_category);
            $row['course_package_category_id']   = $val->course_package_category;

            // Get Category
            $courseCategory = CourseCategory::where('course_id', $val->id)->first();

            $row['category_id']                  = ($courseCategory) ? $courseCategory->category_id : null;
            $row['is_immovable_object']          = $val->is_immovable_object;
            $row['back_payment_status']          = $val->back_payment_status;
            $row['end_time_min']                 = $val->end_time_min;
            $row['start_time_min']               = $val->start_time_min;
            $row['period_day']                   = $val->period_day;

            // Get Course Termin
            $courseTermin = CourseTermin::where('course_id', $val->id)->first();

            $row['termin_percentage']            = ($courseTermin) ? array_map('intval', $courseTermin->value) : null;
            $row['completion_percentage']        = ($courseTermin) ? array_map('intval', $courseTermin->completion_percentage) : null;
            $row['completion_percentage_detail'] = ($courseTermin) ? $courseTermin->completion_percentage_detail : null;
            $row['dp_duedate_number']            = ($courseTermin) ? (int)$courseTermin->dp_duedate_number : null;
            $row['dp_duedate_name']              = ($courseTermin) ? $courseTermin->dp_duedate_name : null;
            $row['termin_duedate_number']        = ($courseTermin) ? $courseTermin->termin_duedate_number : null;
            $row['termin_duedate_name']          = ($courseTermin) ? $courseTermin->termin_duedate_name : null;
            $row['is_percentage']                = ($courseTermin) ? $courseTermin->is_percentage : null;
            $row['is_hidden']                    = ($courseTermin) ? $courseTermin->is_hidden : null;

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            // Get Custom Document Input
            $customDocumentInputs = CustomDocumentInput::where('course_id', $val->id)->get();

            if ($customDocumentInputs) {
                foreach ($customDocumentInputs as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added
            if ($courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = false;
            } 

            $row['is_question_required']            = ($categoryTransactionAutocomplete) ? true : false;
            $row['custom_document_input']           = $customDocumentInput;
            $row['custom_document_input_required']  = $customDocumentInputRequired;
            $row['create_at']                       = $val->created_at;
            $row['update_at']                       = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data paket kursus.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $products->currentPage(),
                'from'              => 1,
                'last_page'         => $products->lastPage(),
                'next_page_url'     => $products->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $products->perPage(),
                'prev_page_url'     => $products->previousPageUrl(),
                'total'             => $products->total()
            ]
        ]);
    }

    public function courseCategoryOld(Request $request, $id)
    {
        // Initialize
        $search = request('search');
        $cpci   = request('course_package_category_id');

        if ($search) {
            if ($cpci != null) {
                // Initialize
                $courseCategory = CourseCategory::where('category_id', $id)->pluck('course_id');
                $courses        = Course::whereIn('id', $courseCategory)
                                    ->where('name', 'LIKE', '%'.request('search').'%')
                                    ->where([
                                        'is_publish'                => 1,
                                        'course_package_category'   => request('course_package_category_id')
                                    ])
                                    ->get();
            } else {
                // Initialize
                $courseCategory = CourseCategory::where('category_id', $id)->pluck('course_id');
                $courses        = Course::whereIn('id', $courseCategory)->where('name', 'LIKE', '%'.request('search').'%')->where('is_publish', 1)->get();
            }
        } else if ($cpci != null) {
            // Initialize
            $courseCategory = CourseCategory::where('category_id', $id)->pluck('course_id');
            $courses        = Course::whereIn('id', $courseCategory)
                                ->where([
                                            'is_publish'                => 1,
                                            'course_package_category'   => request('course_package_category_id')
                                        ])
                                ->where('is_take_down', '0')
                                ->get();
        } else {
            // Initialize
            $courseCategory = CourseCategory::where('category_id', $id)->pluck('course_id');
            $courses        = Course::whereIn('id', $courseCategory)->where('is_publish', 1)->get();
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
            $row['is_sp']            = $course->is_sp;
            $row['sp_file']          = $course->sp_file;

            // Initialize
            $class      = Majors::where('IDCourse', $course->id)->pluck('id');
            $theory     = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate  = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                = ($totalRate) ? $totalRate : 0;
            $row['total_customer']              = $totalBuyer;
            $row['total_session']               = count($course->majors);
            $row['total_theory']                = $theory;
            $row['min_user_joined']             = $course->min_user_joined;
            $row['max_user_joined']             = $course->max_user_joined;
            $row['commission_min']              = $course->commission_min_user_joined;
            $row['commission_max']              = $course->commission_max_user_joined;
            $row['course_package_category']     = courseCategory($course->course_package_category);
            $row['course_package_category_id']  = $course->course_package_category;

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

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            if ($course->customDocumentInput) {
                foreach ($course->customDocumentInput as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added
            if ($course->courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $course->courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = false;
            } 

            $row['is_question_required']            = ($categoryTransactionAutocomplete) ? true : false;
            $row['custom_document_input']           = $customDocumentInput;
            $row['custom_document_input_required']  = $customDocumentInputRequired;
            $row['create_at']                       = $course->created_at;
            $row['update_at']                       = $course->updated_at;

            $data[] = $row;
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

    public function courseInstructorOld(Request $request, $instructorId)
    {
        // Initialize
        $instructor = User::where('id', $instructorId)->first();

        if (!$instructor) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Lembaga tidak ditemukan'
            ]);

            die;
        }

        // Initialize
        $cpci = request('course_package_category_id');

        if ($cpci) {
            // Initialize
            $courses = Course::where(['is_publish' => 1, 'course_package_category' => request('course_package_category_id')])
                        ->where('is_take_down', '0')
                        ->get();
        } else {
            $courses = Course::where(['user_id' => $instructorId, 'is_publish' => 1])
                        ->where('is_take_down', '0')
                        ->get();
        }
        
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
            $row['is_sp']            = $course->is_sp;
            $row['sp_file']          = $course->sp_file;

            // Initialize
            $class      = Majors::where('IDCourse', $course->id)->pluck('id');
            $theory     = MajorsSubject::whereIn('major_id', $class)->count();
            $totalRate  = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                = ($totalRate) ? $totalRate : 0;
            $row['total_customer']              = $totalBuyer;
            $row['total_session']               = count($course->majors);
            $row['total_theory']                = $theory;
            $row['min_user_joined']             = $course->min_user_joined;
            $row['max_user_joined']             = $course->max_user_joined;
            $row['commission_min']              = $course->commission_min_user_joined;
            $row['commission_max']              = $course->commission_max_user_joined;
            $row['course_package_category']     = courseCategory($course->course_package_category);
            $row['course_package_category_id']  = $course->course_package_category;

            // Termin
            $termin = CourseTermin::where('course_id', $course->id)->first();

            $row['is_termin']           = $course->is_termin;
            $row['instalment_title']    = ($termin) ? $termin->instalment_title : null;
            $row['interval']            = ($termin) ? $termin->interval : null;
            $row['down_payment']        = ($termin) ? $termin->down_payment : null;
            $row['interest']            = ($termin) ? $termin->interest : null;
            $row['back_payment_status'] = $course->back_payment_status;
            $row['end_time_min']        = $course->end_time_min;
            $row['start_time_min']      = $course->start_time_min;
            $row['period_day']          = $course->period_day;

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            if ($course->customDocumentInput) {
                foreach ($course->customDocumentInput as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added
            if ($course->courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $course->courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = false;
            } 

            $row['is_question_required']            = ($categoryTransactionAutocomplete) ? true : false;
            $row['custom_document_input']           = $customDocumentInput;
            $row['custom_document_input_required']  = $customDocumentInputRequired;
            $row['create_at']                       = $course->created_at;
            $row['update_at']                       = $course->updated_at;

            $data[] = $row;
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

    public function listStudent(Request $request, $id)
    {
        // Initialize
        $courses = UserCourse::where('course_id', $id)->latest()->get();
        $data    = [];

        // Custom Paginate
        $courses = $this->paginate($courses, 20, null, ['path' => $request->fullUrl()]);

        foreach ($courses as $val) {
            $row['course_id']       = $val->course_id;
            $row['course_name']     = $val->course->name;
            $row['course_slug']     = $val->course->slug;
            $row['user_id']         = $val->user_id;
            $row['user_name']       = $val->user->name;
            $row['user_email']      = $val->user->email;
            $row['user_avatar']     = $val->user->avatar;
            $row['user_phone']      = $val->user->phone;
            $row['course_start']    = $val->course_start;
            $row['course_expired']  = $val->course_expired;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
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

    public function listStudentCount($id)
    {
        // Initialize
        $data = UserCourse::where('course_id', $id)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $data
        ]);
    }

    public function avgRating($id)
    {
        // Initialize
        $data = Rating::where('course_id', $id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $data
        ]);
    }

    public function coursePackageCategory()
    {
        // Initialize
        $data = [
            [
                'id'    => '0',
                'value' => 'Produk'
            ],
            [
                'id'    => '1',
                'value' => 'Kerja'
            ]
            // ,
            // [
            //     'id'    => '2',
            //     'value' => 'Kerja'
            // ]
        ];

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $data
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
