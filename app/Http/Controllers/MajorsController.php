<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Majors;
use App\UserCourse;

class MajorsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role_id == 6) {
            $majors = Majors::where(['IDCourse' => request('course_id')])->orderBy('ID', 'ASC')->get();
        } else{
            $majors = Majors::where(['IDCompany' => auth()->user()->company->ID, 'IDCourse' => request('course_id')])->orderBy('ID', 'ASC')->get();
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $majors
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Majors::create([
            'IDCompany'     => auth()->user()->company->ID,
            'IDCourse'      => $request->course_id,
            'Name'          => $request->name,
            'Details'       => $request->details,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => '',
            'EditedByIP'    => ''
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil disimpan'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Majors $majors)
    {
        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Data tersedia',
                'type'      => 'majors',
                'data'      => $majors->subject
            ]);

            die;
        }

        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Majors $majors)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $majors
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Majors $majors)
    {
        $majors->update([
            'IDCompany'     => auth()->user()->company->ID,
            'Name'          => $request->name,
            'Details'       => $request->details,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => time(),
            'EditedByIP'    => '127.0.0.1'
        ]);

       return response()->json([
           'status'    => true,
           'message'   => 'Data berhasil diperbarui'
       ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Majors $majors)
    {
        if ($majors->IDCompany == auth()->user()->company_id) {
            $majors->delete();
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }
}
