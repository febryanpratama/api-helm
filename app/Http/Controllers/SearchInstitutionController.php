<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use App\User;
use App\Majors;
use App\Rating;
use App\Course;
use App\UserCourse;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SearchInstitutionController extends Controller
{
    public function index($slug)
    {
        // Initialize
        $institution = Company::where('Name', $slug)->firstOrFail();
        $instructor  = User::where('company_id', $institution->ID)->firstOrFail();
        $class       = Majors::where('IDCompany', $institution->ID)->count();
        $courseId    = Course::where('user_id', $instructor->id)->pluck('id');
        $userJoined  = UserCourse::whereIn('course_id', $courseId)->count();
        $totalRate   = Rating::whereIn('course_id', $courseId)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
        $countCourse = Course::where(['is_publish' => 1, 'user_id' => $instructor->id])->count();

        return view('search.institution.index', compact('institution', 'instructor', 'class', 'userJoined', 'countCourse'));
    }

    public function courses(Request $request)
    {
        if (request('search')) {
            // Initialize
            $courses = Course::with('majors','userCourse')->where(['user_id' => request('user_id'), 'is_publish' => 1])->where('name', 'LIKE', '%'.request('search').'%')->latest()->get();
        } else {
            // Initialize
            $courses = Course::with('majors','userCourse')->where(['user_id' => request('user_id'), 'is_publish' => 1])->latest()->get();
        }

        // Initialize
        $dataFinal = $this->paginate($courses, 20, null, ['path' => $request->fullUrl()]);
        $courses   = $this->_manageData($dataFinal);

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $courses,
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

            $data[] = $row;
        }

        return $data;
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
