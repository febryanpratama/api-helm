<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\Partner;
use App\CourseTransactionPartner;
use DB;

class UsersController extends Controller
{
    public function index(Course $course, Partner $partner)
    {
        // Initialize
        $users = DB::table('course_user_partner')
                    ->leftJoin('users', 'users.id','=','course_user_partner.user_id')
                    ->select('course_user_partner.*', 'users.id as uId', 'users.name', 'users.email', 'users.phone', 'users.role_id')
                    ->orderBy('users.name', 'ASC')
                    ->where(['course_id' => $course->id, 'partner_id' => $partner->id])
                    // ->where('users.role_id', '!=', '2')
                    ->get();

        $courseTransactionPartner = CourseTransactionPartner::where(['partner_id' => $partner->id, 'course_id' => $course->id])->first();

        return view('management.users.index', compact('users', 'course', 'partner', 'courseTransactionPartner'));
    }
}
