<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Division;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Check ajax request
        if(request()->ajax()){
            // Initialize
            $divisions  = Division::where('IDCompany', auth()->user()->company->ID)->orderBy('Name', 'DESC')->get();
            $html       = '';

            foreach ($divisions as $division) {
                // Initialize
                $selected = '';

                if (request('divisionId') == $division->ID) {
                    // Initialize
                    $selected = 'selected';
                }

                $html .= '<option value="'.$division->ID.'" '.$selected.'>'.$division->Name.'</option>';
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Divisi tersedia',
                'data'      => $html
            ]);
        }
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
        $division = Division::create([
                        'IDCompany'     => auth()->user()->company->ID,
                        'Name'          => $request->name,
                        'Details'       => $request->details,
                        'PhoneNo'       => $request->phone_no,
                        'Address'       => $request->address,
                        'AddedTime'     => time(),
                        'AddedByIP'     => '127.0.0.1',
                        'EditedTime'    => '',
                        'EditedByIP'    => ''
                    ]);

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Divisi berhasil disimpan',
                'data'      => $division
            ]);

            die;
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Division $division)
    {
        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Data tersedia',
                'type'      => 'division',
                'data'      => $division->subject
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
    public function update(Request $request, Division $division)
    {
        $division->update([
            'IDCompany'     => auth()->user()->company->ID,
            'Name'          => $request->name,
            'Details'       => $request->details,
            'PhoneNo'       => $request->phone_no,
            'Address'       => $request->address,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => time(),
            'EditedByIP'    => '127.0.0.1'
        ]);

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Divisi berhasil diperbaharui'
            ]);

            die;
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Division $division)
    {
        $division->delete();

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Divisi berhasil dihapus'
            ]);

            die;
        }

        return redirect()->back();
    }

    public function member(Division $division)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil didapatkan',
            'data'      => $division->user
        ]);
    }
}
