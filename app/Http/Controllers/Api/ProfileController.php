<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProfileResource;
use Auth;
use App\User;
use App\Company;
use App\CourseCategory;
use App\Category;
use App\Course;

class ProfileController extends Controller
{
    public function show()
    {
        // Initialize
        $user = auth()->user();

        return new ProfileResource($user);
    }

    public function update(Request $request, User $user)
    {
        // Validation
        if ($user->id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                    'error_code' => 'not_accessible'
                ]
            ]);
        }

        if ($request->password != $request->password_confirm) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Password tidak cocok.',
            ], 400);
        }

        // Intialize
        $avatar = request()->file('avatar');
        $path   = $user->avatar;

        if ($avatar) {
            // Unlink File
            if (auth()->user()->avatar) {
                // Initialize
                $expPath = explode('/', auth()->user()->avatar);

                @unlink('storage/uploads/avatar/'.$expPath[6]);
            }

            $path = $avatar->store('uploads/avatar', 'public');
            $path = env('SITE_URL').'/storage/'.$path;
        }

        // Initialize
        // $curriculumVitae = request()->file('curriculum_vitae');
        // $pathCV          = $user->curriculum_vitae;

        // if ($curriculumVitae) {
        //     // Check Extension
        //     $extFT = $curriculumVitae->getClientOriginalExtension();

        //     if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif') {
        //         return response()->json([
        //             'status'    => false,
        //             'message'   => 'Extension CV File Not Supported!'
        //         ]);
        //     }

        //     // Unlink File
        //     if (auth()->user()->curriculum_vitae) {
        //         // Initialize
        //         $expPath = explode('/', auth()->user()->curriculum_vitae);

        //         @unlink('storage/uploads/cv/'.$expPath[6]);
        //     }

        //     $pathCV = $curriculumVitae->store('uploads/cv', 'public');
        //     $pathCV = env('SITE_URL').'/storage/'.$pathCV;
        // }

        $user->update([
            'name'                  => request('name'),
            'phone'                 => request('phone'),
            'avatar'                => $path,
            'password'              => bcrypt(request('password')),
            'password_backup'       => bcrypt(request('password')),
            'is_validate_password'  => 1
            // 'curriculum_vitae'  => $pathCV
        ]);

        return new ProfileResource($user);
    }

    public function ownedCategory(Request $request)
    {
        // Check Store
        if (!auth()->user()->company_id || auth()->user()->company_id == null) {
            return null;
        }

        // Initialize
        $store = Company::where('ID', auth()->user()->company_id)->first();

        if (!$store) {
            return null;
        }

        // Check Course by store and Category
        $courses        = Course::where('user_id', auth()->user()->id)->pluck('id');
        $category       = Category::where('name', 'LIKE','%'.request('category_name').'%')->pluck('id');
        $courseCategory = CourseCategory::whereIn('course_id', $courses)->whereIn('category_id', $category)->get();

        if (count($courseCategory) > 0) {
            $data    = true;
            $message = 'Kategori ('.request('category_name').') Dimiliki';
        } else {
            $data    = false;
            $message = 'Kategori ('.request('category_name').') Tidak Dimiliki';
        }

        return response()->json([
            'status'    => 'success',
            'message'   => $message,
            'data'      => $data
        ]);
    }

    public function destroyCV()
    {
        // Unlink File
        if (auth()->user()->curriculum_vitae) {
            // Initialize
            $expPath = explode('/', auth()->user()->curriculum_vitae);

            @unlink('storage/uploads/cv/'.$expPath[6]);

            // Update Val
            $user = User::find(auth()->user()->id);
            $user->update(['curriculum_vitae' => null]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus CV'
        ]);
    }
}
