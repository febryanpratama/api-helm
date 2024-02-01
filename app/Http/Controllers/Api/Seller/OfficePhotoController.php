<?php

namespace App\Http\Controllers\Api\Seller;

use App\Company;
use App\Http\Controllers\Controller;
use App\OfficePhoto;
use Illuminate\Http\Request;
use Str;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OfficePhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        // Initialize
        $office_photo = OfficePhoto::where('company_id', $id)->get();

        // Custom Paginate
        // $office_photos = $this->paginate($office_photo, 20, null, ['path' => $request->fullUrl()]);
        $data        = [];

        foreach ($office_photo as $val) {
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
            'message'   => 'Berhasil mendapatkan data Foto Kantor.',
            'data'      => $data
        ]);

        // return response()->json([
        //     'status'    => 'success',
        //     'message'   => 'Berhasil mendapatkan data Foto Kantor.',
        //     'data'      => $data,
        //     'meta'      => [
        //         'current_page'      => $office_photos->currentPage(),
        //         'from'              => 1,
        //         'last_page'         => $office_photos->lastPage(),
        //         'next_page_url'     => $office_photos->nextPageUrl(),
        //         'path'              => $request->fullUrl(),
        //         'per_page'          => $office_photos->perPage(),
        //         'prev_page_url'     => $office_photos->previousPageUrl(),
        //         'total'             => $office_photos->total()
        //     ]
        // ]);
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
        // Initialize
        $company = Company::find(auth()->user()->company_id);
        
        if (count($company->officePhotos) == 10) {
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
                'message'   => 'Extension OfficeFoto File Not Supported!'
            ]);

            die;
        }
        
        $pathfile = $file->store('uploads/'.auth()->user()->company->Name.'/office-foto', 'public');
        $pathfile = env('SITE_URL').'/storage/'.$pathfile;
        $path     = $pathfile;

        $office_photo = OfficePhoto::create([
            'company_id'    => auth()->user()->company_id,
            'name'          => request('name'),
            'file'          => $path,
            'description'   => request('description'),
            'slug'          => $slug
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Foto Kantor.',
            'data'      => $office_photo
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
        $office_photo = OfficePhoto::with('company')->find($id);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Foto Kantor.',
            'data'      => $office_photo
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
        // Initialize
        $office_photo = OfficePhoto::find($id);
        $file       = request()->file('file');
        $path       = $office_photo->file;

        // Check Upload File
        if ($file) {
            // Initialize
            $extOfficeFoto = $file->getClientOriginalExtension();

            // Check Extension
            if ($extOfficeFoto == 'php' || $extOfficeFoto == 'sql' || $extOfficeFoto == 'js'|| $extOfficeFoto == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($office_photo->file) {
                // Initialize
                $expOfficeFoto = explode('/', $office_photo->file);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/office-foto/'.$expOfficeFoto[7]);
            }

            $pathOfficeFoto = $file->store('uploads/'.auth()->user()->company->Name.'/office-foto', 'public');
            $pathOfficeFoto = env('SITE_URL').'/storage/'.$pathOfficeFoto;
            $path           = $pathOfficeFoto;
        }

        $office_photo->update([
            'name'          => request('name'),
            'file'          => $path,
            'description'   => request('description')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data Foto Kantor.',
            'data'      => $office_photo
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
        $office_photo = OfficePhoto::find($id);

        // Unlink File
        if ($office_photo) {
            // Initialize
            $expOfficeFoto = explode('/', $office_photo->file);

            @unlink('storage/uploads/'.auth()->user()->company->Name.'/office-foto/'.$expOfficeFoto[7]);
        }

        $office_photo->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data Foto Kantor.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
