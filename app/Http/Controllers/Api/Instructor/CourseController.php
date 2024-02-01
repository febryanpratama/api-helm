<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Cart as AppCart;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCourse;
use App\Http\Requests\UpdateCourse;
use App\Http\Resources\InstructorCourseResource;
use App\Http\Resources\InstructorCourseStoreResource;
use App\Http\Resources\InstructorCourseUpdateResource;
use App\Http\Resources\InstructorCourseUpdateStatusResource;
use App\Course;
use App\CourseCategory;
use App\PendingCommission;
use App\Wallet;
// use App\UserCourse;
use App\Rating;
use App\Majors;
use App\MajorsSubject;
use App\Company;
use App\CourseQuota;
use App\CourseTermin;
use App\CustomDocumentInput;
use App\LandingPromo;
use App\TransactionDetails;
use Str;
use Chat;
use DB;
// use Cart;
use Validator;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $cpci = request('course_package_category_id');

        if ($cpci) {
            // Initialize
            $courses = Course::where(['user_id' => auth()->user()->id, 'course_package_category' => request('course_package_category_id')])->latest()->get();
        } else {
            // Initialize
            $courses = Course::where('user_id', auth()->user()->id)->latest()->get();
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
            $row['price']            = $course->price;
            $row['price_rupiah']     = rupiah($course->price_num);
            $row['price_num']        = $course->price_num;
            $row['discount']         = $course->discount;
            $row['price_after_disc'] = ($course->discount > 0) ? ($course->price_num - $formula) : 0;
            $row['weight']           = $course->weight;
            $row['unit_id']          = $course->unit_id;
            $row['unit_name']        = ($course->unit_id != null) ? $course->unit->name : null;
            $row['commission']       = $course->commission;
            $row['slug']             = $course->slug;
            $row['is_publish']       = ($course->is_publish) ? true : false;
            $row['is_take_down']     = ($course->is_take_down) ? true : false;
            $row['is_admin_confirm'] = $course->is_admin_confirm;

            // Initialize
            $totalRate  = Rating::where('course_id', $course->id)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $totalBuyer = 0;

            $row['total_rating']                 = ($totalRate) ? $totalRate : 0;
            $row['total_customer']               = $totalBuyer;
            $row['is_sp']                        = $course->is_sp;
            $row['sp_file']                      = $course->sp_file;
            $row['course_package_category']      = courseCategory($course->course_package_category);
            $row['course_package_category_id']   = $course->course_package_category;
            $row['category_id']                  = ($course->courseCategory) ? $course->courseCategory->category_id : null;
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
            $row['termin_duedate_number']        = ($course->courseTermin) ? $course->courseTermin->termin_duedate_number : null;
            $row['termin_duedate_name']          = ($course->courseTermin) ? $course->courseTermin->termin_duedate_name : null;
            $row['is_percentage']                = ($course->courseTermin) ? $course->courseTermin->is_percentage : null;
            $row['is_hidden']                    = ($course->courseTermin) ? $course->courseTermin->is_hidden : null;

            // Initialize
            $customDocumentInput = [];

            if ($course->customDocumentInput) {
                foreach ($course->customDocumentInput as $key => $valCDI) {
                    $cdi['course_id']   = $valCDI->course_id;    
                    $cdi['value']       = json_decode($valCDI->value, true);
                    $cdi['created_at']  = $valCDI->created_at;
                    $cdi['updated_at']  = $valCDI->updated_at;

                    $customDocumentInput[] = $cdi;
                }
            }

            $row['custom_document_input'] = $customDocumentInput;
            $row['promotion']             = LandingPromo::where('product_id', $course->id)->where('end_period', '>=', date('Y-m-d H:i:s'))->where('start_period', '<=', date('Y-m-d H:i:s'))->get();
            $row['create_at']             = $course->created_at;
            $row['update_at']             = $course->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Produk.',
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

    public function store(StoreCourse $request)
    {
        if (request('course_package_category') == 0 && request('is_immovable_object') == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'weight' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        if (request('course_package_category') == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'stock' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        // Check Suspend Institution
        $company = Company::where('ID', auth()->user()->company_id)->first();

        if (!$company) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda belum mendaftar sebagai Seller.'
            ]);
        }

        if ($company->IsTakeDown == 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tidak bisa menambahkan Produk! Seller terkena suspend, silahkan hubungi Admin untuk informasi lebih lanjut.'
            ]);
        }

        // Termin
        if ($request->is_termin == 1) {
            $validator = Validator::make(request()->all(), [
                'instalment_title'              => 'required|integer',
                // 'interval'          => 'required|integer|in:1,2,3,4,5,6,7,8,9',
                'dp_duedate_number'             => 'nullable|numeric|min:1',
                'dp_duedate_name'               => 'nullable|in:hari,minggu,bulan,tahun',
                'termin_persentage'             => 'required|array',
                'termin_persentage.*'           => 'required|numeric',
                'down_payment'                  => 'required|numeric|min:0',
                'completion_percentage'         => 'nullable|array',
                'completion_percentage_detail'  => 'nullable|array',
                'termin_duedate_number'         => 'nullable|array',
                'termin_duedate_number.*'       => 'nullable|numeric|min:0|not_in:0',
                'termin_duedate_name'           => 'nullable|array',
                'termin_duedate_name.*'         => 'nullable|in:hari,minggu,bulan,tahun',
                'is_percentage'                 => 'required|in:0,1',
                'is_hidden'                     => 'required|in:0,1',
            ]);
    
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
    
                return response()->json($data, 400);
            }

            if ($request->is_percentage == 1) { // persentase
                $dp_percentage = array($request->down_payment);
            
                $percentage = array_merge($request->termin_persentage, $dp_percentage);
            
                if (array_sum($percentage) < 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($percentage) > 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }
            
            if ($request->is_percentage == 0) { // value/nominal
                $price      = $request->price;
                $priceNum   = str_replace('.', '', $price);
                $dp = array($request->down_payment);
            
                $total = array_merge($request->termin_persentage, $dp);
            
                if (array_sum($total) < $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($total) > $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }
        }

        // if ($request->is_sp == 1) {
        //     $validated = $request->validate([
        //         'sp_file'  => 'required'
        //     ]);
        // }

        // Initialize
        $thumbnail      = request()->file('thumbnail');
        $thumbnailPath  = null;
        $path           = null;
        $price          = $request->price;
        $priceNum       = str_replace('.', '', $price);
        $commission     = 5;
        // $spFile     = request()->file('sp_file');
        // $pathSp     = null;

        if ($thumbnail) {
            // Check Max Size
            if ($thumbnail->getSize() > 100000000) { // 10 MB
                return response()->json([
                    'status'    => false,
                    'message'   => 'Max Size Cover 10 MB'
                ]);
            }

            // Initialize
            $path = env('SITE_URL'). '/storage/'.request('thumbnail')->store('uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        } else {
            $validator = Validator::make(request()->all(), [
                'thumbnail_path'    => 'required|array',
                // 'thumbnail_path.*'  => 'required|array'
            ]);
            
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
            
                return response()->json($data, 400);
            }

            // Initialize
            $thumbnailPath = [];

            foreach(request('thumbnail_path') as $tp) {
                if (preg_match('/\bjpg\b/', $tp)) {
                    $path = $tp;

                    break;
                } elseif (preg_match('/\bjpeg\b/', $tp)) {
                    $path = $tp;

                    break;
                } elseif (preg_match('/\bpng\b/', $tp)) {
                    $path = $tp;

                    break;
                }
            }

            foreach(request('thumbnail_path') as $key => $tp) {
                $data = [
                    'key'   => $key,
                    'value' => $tp
                ];

                array_push($thumbnailPath, $data);
            }

            if (!$path) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Sertakan minimal 1 File berformat (jpg/jpeg/png)'
                ]);
            }

            $thumbnailPath = json_encode($thumbnailPath);
        }

        // if ($spFile) {
        //     // Check Max Size
        //     if ($spFile->getSize() > 100000000) { // 10 MB
        //         return response()->json([
        //             'status'    => false,
        //             'message'   => 'Max Size Surat Perjanjian 10 MB'
        //         ]);
        //     }

        //     // Initialize
        //     $pathSp = env('SITE_URL'). '/storage/'.request('sp_file')->store('uploads/course/sp/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        // }

        // Initialize
        $data = [
            'name'                      => $request->name,
            'user_id'                   => auth()->user()->id,
            'description'               => ($request->description) ? $request->description : '-',
            'price'                     => $price,
            'price_num'                 => $priceNum,
            'is_publish'                => ($request->is_publish) ? $request->is_publish : 0,
            'thumbnail'                 => $path,
            'thumbnail_path'            => $thumbnailPath,
            'slug'                      => Str::slug($request->name.'-'.auth()->user()->company->Name.'-'.auth()->user()->id.date('Yds'), '-'),
            'user_quota_join'           => ($request->stock) ? $request->stock : 0,
            'weight'                    => ($request->weight) ? $request->weight : 0,
            'unit_id'                   => $request->unit_id,
            'discount'                  => $request->discount,
            'cashback'                  => $request->cashback,
            'commission'                => $commission,
            'course_package_category'   => $request->course_package_category,
            // 'is_sp'                     => (request('is_sp')) ? request('is_sp') : 0,
            // 'sp_file'                   => $pathSp,
            'is_termin'                 => $request->is_termin,
            'is_immovable_object'       => ($request->is_immovable_object) ? $request->is_immovable_object : 0,
            'period_day'                => $request->period_day,
            'start_time_min'            => strtotime($request->start_time_min),
            'end_time_min'              => strtotime($request->end_time_min),
            'back_payment_status'       => $request->back_payment_status ? $request->back_payment_status : 0,
        ];

        $course = Course::create($data);

        if (request('category_id')) {
            CourseCategory::create([
               'course_id'     => $course->id,
               'category_id'   => $request->category_id
           ]);
        }

        if ($course) {
            // Adding Quota
            CourseQuota::create([
                'course_id'      => $course->id,
                'previous_quota' => ($request->stock) ? $request->stock : 0,
                'last_quota'     => ($request->stock) ? $request->stock : 0,
                'quota_now'      => ($request->stock) ? $request->stock : 0
            ]);

            // Termin
            if ($request->is_termin == 1) {
                // Initialize
                $NOP        = $request->instalment_title;
                $totalVal   = 0;
                $total      = $priceNum;

                // Check Discount
                if ($request->discount > 0) {
                    // Initialize
                    $priceAfterDisc = discountFormula($course->discount, $priceNum);
                    $total          = $priceAfterDisc;
                }

                
                // Create Course Termin
                $courseTermin = CourseTermin::create([
                    'course_id'                     => $course->id,
                    'instalment_title'              => $request->instalment_title,
                    'down_payment'                  => $request->down_payment, // percentage
                    'dp_duedate_number'             => $request->dp_duedate_number,
                    'dp_duedate_name'               => $request->dp_duedate_name,
                    'number_of_payment'             => $NOP,
                    'interval'                      => $request->interval,
                    'value'                         => $request->termin_persentage,
                    'completion_percentage'         => $request->completion_percentage,
                    'completion_percentage_detail'  => $request->completion_percentage_detail,
                    'termin_duedate_number'         => $request->termin_duedate_number,
                    'termin_duedate_name'           => $request->termin_duedate_name,
                    'installment_amount'            => $total,
                    'admin_fee'                     => null,
                    'is_percentage'                 => $request->is_percentage,
                    'is_hidden'                     => $request->is_hidden,
                ]);
            }

            // Custom Document Input
            if (request('custom_document_input')) {
                $customDocument = [];

                foreach (request('custom_document_input') as $key => $val) {
                    $data = [
                        'name'          => $val,
                        'is_required'   => request('custom_document_input_required')[$key]
                    ];

                    array_push($customDocument, $data);
                }

                if (count($customDocument) > 0) {
                    CustomDocumentInput::create([
                        'course_id' => $course->id,
                        'value'     => json_encode($customDocument)
                    ]);
                }
            }
        }

        return new InstructorCourseStoreResource($course);
    }

    public function show($courseId)
    {
        // Initialize
        $course = Course::where('id', $courseId)->first();
        
        // Validate Exists
        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validation Auth
        if ($course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                    'error_code' => 'not_accessible'
                ]
            ]);
        }

        return new InstructorCourseResource($course);
    }

    public function update(UpdateCourse $request, $courseId)
    {
        if (request('course_package_category') == 0 && request('is_immovable_object') == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'weight' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        if (request('course_package_category') == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'stock' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }
        
        // Check Suspend Institution
        $company = Company::where('ID', auth()->user()->company_id)->first();

        if ($company->IsTakeDown == 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tidak bisa mengedit Produk! Seller terkena suspend, silahkan hubungi Admin untuk informasi lebih lanjut.'
            ]);
        }

        // Check Course
        $course = Course::where('id', $courseId)->first();

        // Validate Exists
        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validation Auth
        if ($course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                    'error_code' => 'not_accessible'
                ]
            ]);
        }

        // Termin
        if ($request->is_termin == 1) {

            $validator = Validator::make(request()->all(), [
                'instalment_title'  => 'required|integer',
                // 'interval'          => 'required|integer|in:1,2,3,4,5,6,7,8,9',
                'dp_duedate_number' => 'nullable|numeric|min:1',
                'dp_duedate_name'   => 'nullable|in:hari,minggu,bulan,tahun',
                'termin_persentage' => 'required|array',
                'termin_persentage.*' => 'required|numeric',
                'down_payment'      => 'required|numeric|min:0',
                'completion_percentage' => 'nullable|array',
                'completion_percentage_detail' => 'nullable|array',
                'termin_duedate_number' => 'nullable|array',
                'termin_duedate_number.*' => 'nullable|numeric|min:0|not_in:0',
                'termin_duedate_name' => 'nullable|array',
                'termin_duedate_name.*' => 'nullable|in:hari,minggu,bulan,tahun',
                'is_percentage'         => 'required|in:0,1',
                'is_hidden'         => 'required|in:0,1'
            ]);
    
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
    
                return response()->json($data, 400);
            }

            if ($request->is_percentage == 1) { // persentase
                $dp_percentage = array($request->down_payment);
            
                $percentage = array_merge($request->termin_persentage, $dp_percentage);
            
                if (array_sum($percentage) < 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($percentage) > 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }
            
            if ($request->is_percentage == 0) { // value/nominal
                $price      = $request->price;
                $priceNum   = str_replace('.', '', $price);
                $dp = array($request->down_payment);
            
                $total = array_merge($request->termin_persentage, $dp);
            
                if (array_sum($total) < $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($total) > $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }

            if (!$request->instalment_title) {
                return response()->json([
                    'message'   => 'The given data was invalid.',
                    'errors'    => [
                            'instalment_title' => [
                                'instalment_title dibutuhkan.'
                            ]
                        ]
                ]);
            }

            // if (!$request->interval) {
            //     return response()->json([
            //         'message'   => 'The given data was invalid.',
            //         'errors'    => [
            //                 'interval' => [
            //                     'interval dibutuhkan.'
            //                 ]
            //             ]
            //     ]);
            // }

            
        }

        // Initialize
        $courseQuota    = $course->user_quota_join;
        $thumbnail      = request()->file('thumbnail');
        $thumbnailPath  = $course->thumbnail_path;
        $path           = $course->thumbnail;
        $price          = $request->price;
        $priceNum       = str_replace('.', '', $price);
        $commission     = 5;
        // $spFile         = request()->file('sp_file');
        // $pathSp         = $course->sp_file;

        if ($thumbnail) {
            // Check Max Size
            if ($thumbnail->getSize() > 100000000) { // 10 MB
                return response()->json([
                    'status'    => false,
                    'message'   => 'Max Size Thumbnail 10 MB'
                ]);
            }

            // Unlink File
            $explodePath = explode('/', $course->thumbnail);

            if (count($explodePath) >= 6) {
                @unlink('storage/uploads/global/'.$explodePath[6]);
            } else {
                @unlink('storage/uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[7]);
            }

            // Initialize
            $path = env('SITE_URL'). '/storage/'.request('thumbnail')->store('uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        } else {
            $validator = Validator::make(request()->all(), [
                'thumbnail_path'    => 'required|array',
                // 'thumbnail_path.*'  => 'required|array'
            ]);
            
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
            
                return response()->json($data, 400);
            }

            // Initialize
            $thumbnailPath = [];

            foreach(request('thumbnail_path') as $tp) {
                if (preg_match('/\bjpg\b/', $tp)) {
                    $path = $tp;

                    break;
                } elseif (preg_match('/\bjpeg\b/', $tp)) {
                    $path = $tp;

                    break;
                } elseif (preg_match('/\bpng\b/', $tp)) {
                    $path = $tp;

                    break;
                }
            }

            foreach(request('thumbnail_path') as $key => $tp) {
                $data = [
                    'key'   => $key,
                    'value' => $tp
                ];

                array_push($thumbnailPath, $data);
            }

            if (!$path) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Sertakan minimal 1 File berformat (jpg/jpeg/png)'
                ]);
            }

            $thumbnailPath = json_encode($thumbnailPath);
        }

        // if ($spFile) {
        //     // Check Max Size
        //     if ($spFile->getSize() > 100000000) { // 10 MB
        //         return response()->json([
        //             'status'    => false,
        //             'message'   => 'Max Size Surat Perjanjian 10 MB'
        //         ]);
        //     }

        //     @unlink('storage/uploads/course/sp/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[8]);

        //     // Initialize
        //     $pathSp = env('SITE_URL'). '/storage/'.request('sp_file')->store('uploads/course/sp/'.auth()->user()->company->Name.'-'.auth()->user()->id, 'public');
        // }

        foreach(request('thumbnail_path') as $tp) {
            if (preg_match('/\bjpg\b/', $tp)) {
                $path = $tp;

                break;
            } elseif (preg_match('/\bjpeg\b/', $tp)) {
                $path = $tp;

                break;
            } elseif (preg_match('/\bpng\b/', $tp)) {
                $path = $tp;

                break;
            }
        }

        // Initialize
        $data = [
            'name'                      => $request->name,
            'user_id'                   => auth()->user()->id,
            'description'               => ($request->description) ? $request->description : '-',
            'price'                     => $price,
            'price_num'                 => $priceNum,
            'is_publish'                => $request->is_publish,
            'thumbnail'                 => $path,
            'thumbnail_path'            => $thumbnailPath,
            'user_quota_join'           => ($request->stock) ? $request->stock : 0,
            'weight'                    => ($request->weight) ? $request->weight : 0,
            'unit_id'                   => $request->unit_id,
            'discount'                  => $request->discount,
            'commission'                => $commission,
            // 'is_sp'                     => (request('is_sp')) ? request('is_sp') : 0,
            // 'sp_file'                   => $pathSp,
            'is_termin'                 => $request->is_termin,
            'course_package_category'   => $request->course_package_category,
            'is_immovable_object'       => $request->is_immovable_object,
            'back_payment_status'       => $request->back_payment_status ? $request->back_payment_status : 0,
        ];

        $course->update($data);

        if ($course) {
            if ($request->stock) {
                // Adding Quota
                CourseQuota::create([
                    'course_id'       => $course->id,
                    'previous_quota'  => $courseQuota,
                    'last_quota'      => ($request->stock) ? $request->stock : 0,
                    'quota_now'       => ($request->stock) ? $request->stock : 0
                ]);
            }
        }

        if (request('category_id')) {
            // Delete All Category
            CourseCategory::where('course_id', $course->id)->delete();

            CourseCategory::create([
                'course_id'     => $course->id,
                'category_id'   => $request->category_id
            ]);
        } else {
            // Delete All Category
            CourseCategory::where('course_id', $course->id)->delete();
        }

        // Termin
        if ($request->is_termin == 1) {
            // Initialize
            $NOP        = $request->instalment_title;
            $totalVal   = 0;
            $total      = $priceNum;

            // Check Discount
            if ($request->discount > 0) {
                // Initialize
                $priceAfterDisc = discountFormula($course->discount, $priceNum);
                $total          = $priceAfterDisc;
            }

            // Get Termin
            $courseTermin = CourseTermin::where('course_id', $course->id)->first();

            if ($courseTermin) {
                    $courseTermin->update([
                    'instalment_title'      => $request->instalment_title,
                    'number_of_payment'     => $NOP,
                    'down_payment'          => $request->down_payment, // percentage
                    'dp_duedate_number'             => $request->dp_duedate_number,
                    'dp_duedate_name'               => $request->dp_duedate_name,
                    'interval'              => $request->interval,
                    'value'                 => $request->termin_persentage,
                    'installment_amount'    => $total,
                    'completion_percentage'         => $request->completion_percentage,
                    'completion_percentage_detail'  => $request->completion_percentage_detail,
                    'termin_duedate_number'         => $request->termin_duedate_number,
                    'termin_duedate_name'           => $request->termin_duedate_name,
                    'is_percentage'                 => $request->is_percentage,
                    'is_hidden'                     => $request->is_hidden,
                ]);
            } else {
                CourseTermin::create([
                    'course_id'             => $course->id,
                    'instalment_title'      => $request->instalment_title,
                    'number_of_payment'     => $NOP,
                    'down_payment'          => $request->down_payment, // percentage
                    'dp_duedate_number'             => $request->dp_duedate_number,
                    'dp_duedate_name'               => $request->dp_duedate_name,
                    'interval'              => $request->interval,
                    'value'                 => $request->termin_persentage,
                    'installment_amount'    => $total,
                    'completion_percentage'         => $request->completion_percentage,
                    'completion_percentage_detail'  => $request->completion_percentage_detail,
                    'termin_duedate_number'         => $request->termin_duedate_number,
                    'termin_duedate_name'           => $request->termin_duedate_name,
                    'is_percentage'                 => $request->is_percentage,
                    'is_hidden'                     => $request->is_hidden,
                ]);
            }
        }

        if (request('custom_document_input')) {
            // Custom Document Input
            $customDocument = [];

            foreach (request('custom_document_input') as $key => $val) {
                $data = [
                    'name'          => $val,
                    'is_required'   => request('custom_document_input_required')[$key]
                ];

                array_push($customDocument, $data);
            }

            if (count($customDocument) > 0) {
                // Check Data Document Input
                $existsDataCDI = CustomDocumentInput::where('course_id', $course->id)->first();

                if ($existsDataCDI) {
                    $existsDataCDI->delete();
                }
                
                CustomDocumentInput::create([
                    'course_id' => $course->id,
                    'value'     => json_encode($customDocument)
                ]);
            }
        }

        return new InstructorCourseUpdateResource($course);
    }

    public function delete($courseId)
    {
        // Check Course
        $course = Course::where('id', $courseId)->first();

        // Validate Exists
        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validation Auth
        // if ($course->user_id != auth()->user()->id) {
        //     return response()->json([
        //         'status'    => 'error',
        //         'message'   => 'Anda tidak memiliki akses!',
        //         'data'      => [
        //             'error_code' => 'not_accessible'
        //         ]
        //     ]);
        // }

        // Check User Transaction
        $userTransaction = TransactionDetails::where('course_id', $course->id)->count();

        if ($userTransaction > 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Produk tidak bisa di hapus'
            ]);
        }

        // Unlink File
        $explodePath = explode('/', $course->thumbnail);
        
        if (count($explodePath) >= 7) {
            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[7]);
        } else {
            @unlink('storage/uploads/global/'.$explodePath[6]);
        }

        // Check Thumbnail Path
        if ($course->thumbnail_path != null) {
            foreach(json_decode($course->thumbnail_path, true) as $valTP) {
                // Unlink File
                $explodePath = explode('/', $valTP['value']);
                
                @unlink('storage/uploads/global/'.$explodePath[6]);
            }
        }

        $course->delete();

        // Delete Product In Cart
        AppCart::where('course_id', $courseId)->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data Produk.',
            'data'      => [
                'id'        => $courseId,
                'delete_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    public function publishUnpublish($courseId)
    {
        // Initialize
        $course = Course::where('id', $courseId)->first();

        // Validation Exists
        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'  => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validation Auth
        if ($course->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                    'error_code' => 'not_accessible'
                ]
            ]);
        }

        if (request('status') == null) {
            return response()->json([
                'message'   => 'The given data was invalid.',
                'errors'    => [
                    'status' => [
                        'Status dibutuhkan.'
                    ]
                ]
            ]);
        }

        // Check Category
        if (request('status') == 1) {
            if (!$course->courseCategory) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Silahkan tambahkan Kategori Produk terlebih dahulu.'
                ]);
            }
        }

        $course->update(['is_publish' => request('status')]);

        return new InstructorCourseUpdateStatusResource($course);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
