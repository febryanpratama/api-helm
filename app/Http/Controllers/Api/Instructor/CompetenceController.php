<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CompetenceRequest;
use App\Http\Requests\CompetenceUpdateRequest;
use App\Competence;
use Str;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CompetenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        // Initialize
        $competence = Competence::where('company_id', $id)->get();

        // Custom Paginate
        $competences = $this->paginate($competence, 20, null, ['path' => $request->fullUrl()]);
        $data        = [];

        foreach ($competences as $val) {
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
            'message'   => 'Berhasil mendapatkan data Kompetensi.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $competences->currentPage(),
                'from'              => 1,
                'last_page'         => $competences->lastPage(),
                'next_page_url'     => $competences->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $competences->perPage(),
                'prev_page_url'     => $competences->previousPageUrl(),
                'total'             => $competences->total()
            ]
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
    public function store(CompetenceRequest $request)
    {
        // Initialize
        $slug = Str::slug(request('name'), '-');
        $file = request()->file('file');

        // Upload File
        $extFile = $file->getClientOriginalExtension();

        // Check Extension
        if ($extFile == 'php' || $extFile == 'sql' || $extFile == 'js'|| $extFile == 'gif') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension Competence File Not Supported!'
            ]);

            die;
        }
        
        $pathfile = $file->store('uploads/'.auth()->user()->company->Name.'/competence', 'public');
        $pathfile = env('SITE_URL').'/storage/'.$pathfile;
        $path     = $pathfile;

        $competence = Competence::create([
            'company_id'    => auth()->user()->company_id,
            'name'          => request('name'),
            'file'          => $path,
            'description'   => request('description'),
            'slug'          => $slug
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Kompetensi.',
            'data'      => $competence
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
        $competence = Competence::with('company')->find($id);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Kompetensi.',
            'data'      => $competence
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
    public function update(CompetenceUpdateRequest $request, $id)
    {
        // Initialize
        $competence = Competence::find($id);
        $file       = request()->file('file');
        $path       = $competence->file;

        // Check Upload File
        if ($file) {
            // Initialize
            $extCompetence = $file->getClientOriginalExtension();

            // Check Extension
            if ($extCompetence == 'php' || $extCompetence == 'sql' || $extCompetence == 'js'|| $extCompetence == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Portfolio Photo File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if ($competence->file) {
                // Initialize
                $expCompetence = explode('/', $competence->file);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/competence/'.$expCompetence[7]);
            }

            $pathCompetence = $file->store('uploads/'.auth()->user()->company->Name.'/competence', 'public');
            $pathCompetence = env('SITE_URL').'/storage/'.$pathCompetence;
            $path           = $pathCompetence;
        }

        $competence->update([
            'name'          => request('name'),
            'file'          => $path,
            'description'   => request('description')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data Kompetensi.',
            'data'      => $competence
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
        $competence = Competence::find($id);

        // Unlink File
        if ($competence) {
            // Initialize
            $expCompetence = explode('/', $competence->file);

            @unlink('storage/uploads/'.auth()->user()->company->Name.'/competence/'.$expCompetence[7]);
        }

        $competence->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data Kompetensi.',
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
