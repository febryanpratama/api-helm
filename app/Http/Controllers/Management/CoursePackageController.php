<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\CoursePartner;
use App\CourseUserPartner;
use DB;

class CoursePackageController extends Controller
{
    public function index()
    {
        // Initialize
        // $courses = DB::table('course_partner')
        //             ->leftJoin('course', 'course_partner.course_id', '=', 'course.id')
        //             ->leftJoin('users', 'course.user_id', '=', 'users.id')
        //             ->leftJoin('user_course', 'course.id', '=', 'user_course.course_id')
        //             ->select('course_partner.*', 'course.id as cId', 'course.name', 'course.price', 'course.periode_type', 'course.periode', 'users.name as instructor', 'course.slug', DB::raw('count(*) as total_users, user_course.course_id'))
        //             ->where('user_course.partner_id', auth()->user()->partner->id)
        //             ->latest()
        //             ->get();
                    
        $coursesId = CoursePartner::where('partner_id', auth()->user()->partner->id)->pluck('course_id');
        $courses   = Course::whereIn('id', $coursesId)->latest()->get();
        $data      = [];

        foreach ($courses as $val) {
            $row['id']              = $val->id;
            $row['name']            = $val->name;
            $row['slug']            = $val->slug;
            $row['price']           = $val->price;
            $row['instructor']      = $val->user->name;
            $row['company']         = $val->user->company->Name;
            $row['partner_id']      = auth()->user()->partner->id;
            $row['members']         = CourseUserPartner::where(['partner_id' => auth()->user()->partner->id, 'course_id' => $val->id])->count();
            $row['periode']         = $val->periode;
            $row['periode_type']    = $val->periode_type;

            $data[] = $row;
        }
        
        $courses = $data;

        return view('management.course-package.index', compact('courses'));
    }
}
