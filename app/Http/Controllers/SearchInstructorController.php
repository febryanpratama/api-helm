<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Course;
use App\UserCourse;

class SearchInstructorController extends Controller
{
    public function index()
    {
        // Initialize
        $totalInstructor = User::where('is_instructor', '1')->whereNotNull('company_id')->count();

        return view('search.instructor.index', compact('totalInstructor'));
    }

    public function instructor()
    {
        if (request('q')) {
            // Initialize
            $users = User::with('courses','company')->where('is_instructor', '1')->where('name', 'LIKE', '%'.request('q').'%')->whereNotNull('company_id')->orderBy('name', 'ASC')->paginate(30);
        } else {
            // Initialize
            $users = User::with('courses','company')->where('is_instructor', '1')->whereNotNull('company_id')->orderBy('name', 'ASC')->paginate(30);
        }

        $data = [];
        foreach ($users as $val) {
            $row['id']              = $val->id;
            $row['instructor_name'] = $val->name;
            $row['avatar']          = ($val->avatar) ? $val->avatar : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg';
            $row['company_name']    = $val->company->Name;
            $row['course_package']  = count($val->courses);

            // Initialize
            $courses    = Course::where('user_id', $val->id)->pluck('id');
            $userCourse = UserCourse::whereIn('course_id', $courses)->count();

            $row['student_joined']  = $userCourse;

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }
}
