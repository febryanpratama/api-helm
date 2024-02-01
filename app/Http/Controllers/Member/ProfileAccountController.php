<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class ProfileAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('member.profile.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // Intialize
        $avatar          = request()->file('avatar');
        $curriculumVitae = request()->file('curriculum_vitae');
        $path            = null;
        $pathCV          = $user->curriculum_vitae;

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

        if ($curriculumVitae) {
            // Check Extension
            $extFT = $curriculumVitae->getClientOriginalExtension();

            if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension CV File Not Supported!'
                ]);
            }

            // Unlink File
            if (auth()->user()->curriculum_vitae) {
                // Initialize
                $expPath = explode('/', auth()->user()->curriculum_vitae);

                @unlink('storage/uploads/cv/'.$expPath[6]);
            }

            $pathCV = $curriculumVitae->store('uploads/cv', 'public');
            $pathCV = env('SITE_URL').'/storage/'.$pathCV;
        }

        $user->update([
            'name'              => request('name'),
            'avatar'            => $path,
            'curriculum_vitae'  => $pathCV
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Profil Berhasil Diperbaharui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy()
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
            'status'    => true,
            'message'   => 'Berhasil menghapus CV'
        ]);
    }
}
