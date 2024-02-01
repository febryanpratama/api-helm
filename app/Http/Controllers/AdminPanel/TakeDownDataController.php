<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\User;
use App\Company;
use App\Subject;

class TakeDownDataController extends Controller
{
    public function coursePackage(Request $request)
    {
        if (request('search')) {
            // Initialize
            $courses = Course::where('name', 'LIKE', '%'.request('search').'%')->latest()->paginate(20);
        } else {
            // Initialize
            $courses = Course::latest()->paginate(20);
        }

        return view('admin-panel.take-down-data.course-package', compact('courses'));
    }

    public function users(Request $request)
    {
        if (request('search')) {
            // Initialize
            $users = User::whereNotIn('role_id', [10])->where('name', 'LIKE', '%'.request('search').'%')->orWhere('email', 'LIKE', '%'.request('search').'%')->latest()->paginate(20);
        } else {
            // Initialize
            $users = User::whereNotIn('role_id', [10])->latest()->paginate(20);
        }

        return view('admin-panel.take-down-data.users', compact('users'));
    }

    public function institution()
    {
        if (request('search')) {
            // Initialize
            $institution = Company::with('user')->where('Name', 'LIKE', '%'.request('search').'%')->orWhere('Email', 'LIKE', '%'.request('search').'%')->paginate(20);
        } else {
            // Initialize
            $institution = Company::with('user')->paginate(20);
        }

        return view('admin-panel.take-down-data.institution', compact('institution'));   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCourse(Request $request, Course $course)
    {
        if (!$course) {
            return response()->json([
                'status'    => false,
                'message'   => 'Course not found'
            ]);
        }

        // Initialize
        $takeDown = 1;

        if ($course->is_take_down == 1) {
            $takeDown = 0;
        }

        $course->update(['is_take_down' => $takeDown]);

        return response()->json([
            'status'  => true,
            'message' => 'Data berhasil diperbarui'
        ]);
    }

    public function updateUser(User $user)
    {
        if (!$user) {
            return response()->json([
                'status'    => false,
                'message'   => 'User not found'
            ]);
        }

        // Initialize
        $takeDown = 1;

        if ($user->is_take_down == 1) {
            $takeDown = 0;
        }

        $user->update(['is_take_down' => $takeDown]);

        return response()->json([
            'status'  => true,
            'message' => 'Data berhasil diperbarui'
        ]);
    }

    public function updateInstitution(Company $company)
    {
        if (!$company) {
            return response()->json([
                'status'    => false,
                'message'   => 'Institution not found'
            ]);
        }

        // Initialize
        $takeDown = 1;

        if ($company->IsTakeDown == 1) {
            $takeDown = 0;
        }

        $company->update(['IsTakeDown' => $takeDown]);

        return response()->json([
            'status'  => true,
            'message' => 'Data berhasil diperbarui'
        ]);
    }

    public function video()
    {
        // Initialize
        $subjects = Subject::where('FileType', 2)->get();

        return view('admin-panel.take-down-data.video', compact('subjects'));   
    }

    public function updateVideo(Subject $subject)
    {
        if (!$subject) {
            return response()->json([
                'status'    => false,
                'message'   => 'Subject not found'
            ]);
        }

        // Initialize
        $takeDown = 1;

        if ($subject->IsTakeDown == 1) {
            $takeDown = 0;
        }

        $subject->update(['IsTakeDown' => $takeDown]);

        return response()->json([
            'status'  => true,
            'message' => 'Data berhasil diperbarui'
        ]);
    }
}
