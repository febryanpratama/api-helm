<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Subject;
use App\Company;
use App\MajorsSubject;
use App\Majors;
use App\Course;

class MainPageController extends Controller
{
    public function carousel()
    {
        // Initialize
        $carousel = DB::table('carousel')->inRandomOrder()->get();
        $data     = [];

        foreach ($carousel as $val) {
            $row['id']              = $val->id;
            $row['banner']          = env('SITE_URL').'/img/carousel-apps/'.$val->banner;
            $row['carousel_name']   = $val->carousel_name;
            $row['created_at']      = $val->created_at;
            $row['updated_at']      = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function videohighlights()
    {
        // Initialize
        $majorSubject = MajorsSubject::pluck('subject_id');
        $subjects     = Subject::whereIn('ID', $majorSubject)->where(['FileType' => 2, 'IsTakeDown' => 0])->inRandomOrder()->limit(5)->get();
        $data         = [];

        foreach($subjects as $val) {
            $row['id']          = $val->ID;
            $row['title']       = $val->Name;
            $row['path']        = $val->Path;
            $row['IsTakeDown']  = $val->IsTakeDown;

            // Initialize
            $store    = Company::where('ID', $val->IDCompany)->first();
            $mSubject = MajorsSubject::where('subject_id', $val->ID)->first();

            if ($store) {
                $row['store'] = $store;
            }

            if ($mSubject) {
                // Initialize
                $major = Majors::where('ID', $mSubject->major_id)->first();

                $row['session'] = $major;

                if ($major) {
                    // Initialize
                    $course = Course::where('id', $major->IDCourse)->first();

                    if ($course) {
                        // Initialize
                        $formula = ($course->discount/100) * $course->price_num;
                        
                        $attribute['id']                          = $course->id;
                        $attribute['name']                        = $course->name;
                        $attribute['thumbnail']                   = $course->thumbnail;
                        $attribute['price']                       = $course->price;
                        $attribute['price_num']                   = $course->price_num;
                        $attribute['discount']                    = $course->discount;
                        $attribute['price_after_disc']            = ($course->discount > 0) ? ($course->price_num - $formula) : 0;
                        $attribute['weight']                      = $course->weight;
                        $attribute['unit_id']                     = $course->unit_id;
                        $attribute['unit_name']                   = ($course->unit_id != null) ? $course->unit->name : null;
                        $attribute['slug']                        = $course->slug;
                        $attribute['course_package_category']     = courseCategory($course->course_package_category);
                        $attribute['course_package_category_id']  = $course->course_package_category;
                        $attribute['category_id']                 = ($course->courseCategory) ? $course->courseCategory->category_id : null;
                        $attribute['is_immovable_object']         = $course->is_immovable_object;
                        $attribute['create_at']                   = $course->created_at;
                        $attribute['update_at']                   = $course->updated_at;

                        $row['course'] = $attribute;
                    }
                }
            } else {
                $row['session'] = null;
                $row['course']  = null;
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }
}
