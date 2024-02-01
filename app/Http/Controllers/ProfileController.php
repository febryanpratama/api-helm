<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lang;
use App\Division;
use App\Company;
use App\Course;
use App\UserCourse;
use App\User;
use App\HintWidget;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function edit(\App\User $user)
	{
		return view('profile.update', compact('user'));
	}

    public function update(\App\User $user)
    {
        if (request('name')) {
            $user->update([
                'name' => request()->name
            ]);
        } elseif (request('phone_no')) {
            $user->update([
                'phone' => request()->phone_no
            ]);
        } else {
            $user->update([
                'name' => request()->name,
                'phone' => request()->phone_no,
            ]);
        }

        // Check ajax request
        if(request()->ajax()){
            // Initialize
            $field = request('name');

            if (!$field) {
                $message = Lang::get('label.phone');
            } else {
                $message = Lang::get('label.name');
            }

            return response()->json([
                'status'    => true,
                'message'   => ucfirst($message).' berhasil diperbarui'
            ]);

            die;
        }

        if ($user->is_active == 'n') {
            return redirect()->route('transaction.agency');
        }

        return redirect()->route('home');
    }


    public function userToPeople()
    {
        $user = \App\User::all();

        foreach ($user as $key => $value) {
            
            $check = \App\People::where('IDUser', $value->id)->first();

            if (!$check) {
                $people = \App\People::create([
                    'IDUser' => $value->id,
                    'IDCompany' => 2,
                    'Email' => $value->email,
                    'IDRole' => $value->role_id,
                    'AddedTime' => time(),
                    'AddedByIP' => $_SERVER["REMOTE_ADDR"],
                ]);
            }
        }
    }

    public function company()
    {
        $company = null;
        
        return view('company.create', compact('company'));
    }

    public function editCompany()
    {
        // Initialize
        $company            = null;
        $totalCourse        = 0;
        $totalSJoin         = 0;
        $pagePackageCourse  = HintWidget::where(['user_id' => auth()->user()->id, 'page' => 'course-package-page'])->first();

        if (request()->has('company')) {
            // Initialize
            $company     = Company::find(request()->get('company'));
            $course      = Course::where('user_id', auth()->user()->id);
            $totalCourse = $course->count();
            $courseId    = $course->pluck('id');
            $totalSJoin  = UserCourse::whereIn('course_id', $courseId)->count();
        }

        return view('company.create', compact('company','totalCourse','totalSJoin','pagePackageCourse'));
    }

    public function companyStore()
    {
        // Initialize
        $status = "FAILED";
        $message = "Failed Created";
        
        $company = Company::updateOrCreate([
            'ID' => request()->id
        ],
        [
            'Name' => request()->institution_name,
            'Address' => request()->institution_address,
            'Phone' => request()->institution_phone,
            'Email' => request()->institution_email,
            // 'Type' => request()->type,
            // 'RequiredPhoto' => request()->required_foto,
            // 'Color' => request()->color,
            // 'TotalCheck' => request()->total_check,
            // 'OpenTime' => request()->start,
            // 'ClosedTime' => request()->end,
            // 'MemoText' => request()->MemoText,
            // 'TaskText' => request()->TaskText,
            // 'ToDoText' => request()->ToDoText,
            // 'ProjectText' => request()->ProjectText,
            // 'NoteText' => request()->NoteText,
            'AddedTime' => time(),
            'AddedByIP' => '127.0.0.1',
        ]);

        if ($company) {
            $status = "OK";
            $message = "Success Update";
            if (request()->has('create')) {
                $status = "OK";
                $message = "Silakan mengisi data user di tempat kerja Anda";
            }
            if (request()->file( 'background' )) {
        
                $imagePath = request('background')->store('uploads/img/company/background', 'public');
                $company->Background = env('SITE_URL') . '/storage/' . $imagePath;
                $company->save();
            }

            if (request()->file( 'logo' )) {
        
                $imagePath = request('logo')->store('uploads/img/company/logo', 'public');
                $company->Logo = env('SITE_URL') . '/storage/' . $imagePath;
                $company->save();
            }

            if (!request()->id) {
                $user = User::find(auth()->user()->id);
                $user->company_id = $company->ID;
                $user->phone = $request->phone;
                $user->save();
                $status = "OK";
                $message = "Success Created";
            }

            request()->session()->flash( 'status', $status );
		    request()->session()->flash( 'message', $message );

            if (request()->has('create')) {
                // Check ajax request
                if(request()->ajax()){
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Perusahaan berhasil disimpan'
                    ]);

                    die;
                }

                return redirect()->route('user.data');
            } else {
                // Check ajax request
                if(request()->ajax()){
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Perusahaan berhasil diperbarui'
                    ]);

                    die;
                }
                
                // return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
                return redirect()->back();
            }
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Perusahaan berhasil diperbarui'
            ]);

            die;
        }

        request()->session()->flash( 'status', $status );
        request()->session()->flash( 'message', $message );

        return redirect()->back();
    }

    public function uploadFile()
    {
        // Initialize
        $type = request('type');

        if (request()->file('file')) {
            // Initialize
            $path       = 'uploads/img/profile/';
            $imagePath  = request('file')->store($path.$type, 'public');
            $fullPath   = env('SITE_URL') . '/storage/' . $imagePath;

            // Unlink
            if (auth()->user()->$type) {
                $explodePath = explode('/', auth()->user()->$type);

                @unlink('storage/'.$path.$explodePath[7].'/'.$explodePath[8]);
            }

            $user = \App\User::where('id', auth()->user()->id)->update([$type => $fullPath]);
        }

        if ($type == 'thumbnail') {
            $message = Lang::get('label.thumbnail').' berhasil diperbarui';
        } else {
            $message = Lang::get('label.avatar').' berhasil diperbarui';
        }

        return response()->json([
            'status'    => true,
            'message'   => $message
        ]);
    }
}
