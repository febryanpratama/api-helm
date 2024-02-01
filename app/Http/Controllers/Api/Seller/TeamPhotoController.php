<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\TeamPhoto;
use Str;
use Validator;

class TeamPhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        // Initialize
        $teamPhoto = TeamPhoto::where('company_id', $id)->get();
        $data      = [];

        foreach ($teamPhoto as $val) {
            // Initialize
            $row['id']              = $val->id;
            $row['company_id']      = $val->company_id;
            $row['company']         = $val->company;
            $row['name']            = $val->name;
            $row['file']            = $val->file;
            $row['description']     = $val->description;
            $row['created_at']      = $val->created_at;
            $row['updated_at']      = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Foto Tim.',
            'data'      => $data
        ]);
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
        // Validation
        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:jpeg,jpg,png'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $checkCountData = TeamPhoto::where('company_id', auth()->user()->company_id)->count();
        
        if ($checkCountData == 10) {
            return response()->json([
                'status'    => false,
                'message'   => 'Max 10 photo'
            ]);
        }

        // Initialize
        $slug = Str::slug(request('name'), '-');
        $file = request()->file('file');

        // Upload File
        $extFile = $file->getClientOriginalExtension();

        // Check Extension
        if ($extFile == 'php' || $extFile == 'sql' || $extFile == 'js'|| $extFile == 'gif') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension Team Photo File Not Supported!'
            ]);

            die;
        }
        
        $pathfile = $file->store('uploads/'.auth()->user()->company->Name.'/team-photo', 'public');
        $pathfile = env('SITE_URL').'/storage/'.$pathfile;
        $path     = $pathfile;

        $teamPhoto = TeamPhoto::create([
            'company_id'    => auth()->user()->company_id,
            'name'          => request('name'),
            'file'          => $path,
            'description'   => request('description'),
            'slug'          => $slug
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Foto Tim.',
            'data'      => $teamPhoto
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Initialize
        $teamPhoto = TeamPhoto::with('company')->find($id);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Foto Tim.',
            'data'      => $teamPhoto
        ]);
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
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:jpeg,jpg,png'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }
        
        // Initialize   
        $teamPhoto  = TeamPhoto::find($id);
        $file       = request()->file('file');
        $path       = $teamPhoto->file;

        // Check Upload File
        if ($file) {
           // Initialize
           $extOfficeFoto = $file->getClientOriginalExtension();

           // Check Extension
           if ($extOfficeFoto == 'php' || $extOfficeFoto == 'sql' || $extOfficeFoto == 'js'|| $extOfficeFoto == 'gif') {
               return response()->json([
                   'status'    => false,
                   'message'   => 'Extension Team Photo File Not Supported!'
               ]);

               die;
           }

           // Unlink File
           if ($teamPhoto->file) {
               // Initialize
               $expOfficeFoto = explode('/', $teamPhoto->file);

               @unlink('storage/uploads/'.auth()->user()->company->Name.'/team-photo/'.$expOfficeFoto[7]);
           }

           $pathOfficeFoto = $file->store('uploads/'.auth()->user()->company->Name.'/team-photo', 'public');
           $pathOfficeFoto = env('SITE_URL').'/storage/'.$pathOfficeFoto;
           $path           = $pathOfficeFoto;
        }

        $teamPhoto->update([
           'name'          => request('name'),
           'file'          => $path,
           'description'   => request('description')
        ]);

        return response()->json([
           'status'    => 'success',
           'message'   => 'Berhasil mengubah data Foto Tim.',
           'data'      => $teamPhoto
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Initialize
        $teamPhoto = TeamPhoto::find($id);

        // Unlink File
        if ($teamPhoto) {
            // Initialize
            $expTeamFoto = explode('/', $teamPhoto->file);

            @unlink('storage/uploads/'.auth()->user()->company->Name.'/team-photo/'.$expTeamFoto[7]);
        }

        $teamPhoto->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data Foto Tim.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
