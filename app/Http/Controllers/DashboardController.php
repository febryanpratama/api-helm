<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Course;
use App\UserCourse;

class DashboardController extends Controller
{
    public function index()
    {
        // Initialize
        $course      = Course::where('user_id', auth()->user()->id);
        $totalCourse = $course->count();
        $courseId    = $course->pluck('id');
        // $totalSJoin  = UserCourse::whereIn('course_id', $courseId)->count();
        $totalSJoin  = 0;

        return view('dashboard.index', compact('totalCourse','totalSJoin'));
    }
}
