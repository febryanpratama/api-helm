<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TheoryRequest;
use App\Http\Resources\TheoryResource;
use App\Majors;
use App\MajorsSubject;
use App\Subject;
use App\Course;
use App\TheoryLock;
// use App\UserCourse;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TheoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!request('session_id')) {
            return response()->json([
                'message'   => 'The given data was invalid.',
                'errors'    => [
                    'session_id' => [
                        'Session Id dibutuhkan.'
                    ]
                ]
            ]);
        }

        // Initialize
        $subjects = MajorsSubject::where('major_id', request('session_id'))->get();
        $major    = Majors::where('ID', request('session_id'))->first();

        if (!$major) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        $course = Course::where('id', $major->IDCourse)->first();
        $data   = [];

        // Custom Paginate
        $subjects = $this->paginate($subjects, 20, null, ['path' => $request->fullUrl()]);

        foreach ($subjects as $key => $val) {
            // Initialize
            $subject = $val->subject;

            $row['id']              = $subject->ID;
            $row['name']            = $subject->Name;
            $row['file_type']       = fileTypes($subject->FileType);
            $row['file_extension']  = $subject->FileExtension;
            $row['duration']        = $subject->Duration;
            $row['path']            = $subject->Path;
            $row['thumbnail']       = $subject->Thumbnail;
            $row['created_at']      = date('Y-m-d H:i:s', $subject->AddedTime);

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Materi.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $subjects->currentPage(),
                'from'              => 1,
                'last_page'         => $subjects->lastPage(),
                'next_page_url'     => $subjects->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $subjects->perPage(),
                'prev_page_url'     => $subjects->previousPageUrl(),
                'total'             => $subjects->total()
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
    public function store(TheoryRequest $request)
    {
        // Initialize
        $majors = Majors::where('ID', $request->session_id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Initialize
        $fileTheory         = request()->file('upload_file');
        $thumbnailTheory    = request()->file('thumbnail');
        $extFT              = $fileTheory->getClientOriginalExtension();
        $pathTT             = false;

        // Check Max Size
        if ($fileTheory->getSize() > 100000000) { // 100 MB
            return response()->json([
                'status'    => false,
                'message'   => 'Max Size File Materi 100 MB'
            ]);
        }

        // Check Extension
        if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif' || $extFT == 'docx') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension File Not Supported!'
            ]);
        }

        if ($extFT == 'pdf') {
            $fileType = '1';
        } else if ($extFT == 'mp4' || $extFT == 'mkv') {
            $fileType = '2';
        } else if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
            $fileType = '3';
        }

        // Initialize
        $majorDetail = Majors::where('ID', $request->session_id)->first();

        // Upload File
        $path = $fileTheory->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$majorDetail->Name, 'public');

        if ($thumbnailTheory) {
            // Upload File Thumbnail
            $pathTT = $thumbnailTheory->store('uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$majorDetail->Name, 'public');
        }

        $subject = Subject::create([
            'IDCompany'     => auth()->user()->company_id,
            'Name'          => $request->name,
            'FileType'      => $fileType,
            'FileExtension' => $extFT,
            'Duration'      => $request->duration,
            'Path'          => env('SITE_URL').'/storage/'.$path,
            'Thumbnail'     => ($pathTT) ? env('SITE_URL').'/storage/'.$pathTT : null,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => '',
            'EditedByIP'    => ''
        ]);

        if ($subject) {
            MajorsSubject::create([
                'major_id'    => $request->session_id,
                'subject_id'  => $subject->ID
            ]);
        }

        return new TheoryResource($subject);
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
        $subject = Subject::where('ID', $id)->first();

        if (!$subject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Materi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        return new TheoryResource($subject);
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
        $subject = Subject::where('ID', $id)->first();

        if (!$subject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Materi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Initialize
        $fileTheory         = request()->file('upload_file');
        $thumbnailTheory    = request()->file('thumbnail');
        $majorDetail        = Majors::where('ID', $subject->majorsSubject->major_id)->first();
        $path               = $subject->Path;
        $pathTT             = $subject->Thumbnail;
        $fileType           = $subject->FileType;
        $extFT              = $subject->FileExtension;
        
        // Check File
        if ($fileTheory) {
            // Delete File
            $explodePath = explode('/', $subject->Path);
            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/materi/'.$explodePath[8].'/'.$explodePath[9]);
            
            // Initialize 
            $extFT = $fileTheory->getClientOriginalExtension();

            // Check Max Size
            if ($fileTheory->getSize() > 100000000) { // 100 MB
                return response()->json([
                    'status'    => false,
                    'message'   => 'Max Size File Materi 100 MB'
                ]);

                die;
            }

            // Check Extension
            if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif' || $extFT == 'docx') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension File Not Supported!'
                ]);
            }

            if ($extFT == 'docx' || $extFT == 'pdf') {
                $fileType = '1';
            } else if ($extFT == 'mp4' || $extFT == 'mkv') {
                $fileType = '2';
            } else if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
                $fileType = '3';
            }

            // Upload File
            $path = $fileTheory->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$majorDetail->Name, 'public');
            $path = env('SITE_URL').'/storage/'.$path;
        }

        if ($thumbnailTheory) {
            // Delete File
            $explodePath = explode('/', $subject->Thumbnail);
            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$explodePath[8].'/'.$explodePath[9]);
            
            // Upload File
            $pathTT = $thumbnailTheory->store('uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$majorDetail->Name, 'public');
            $pathTT = env('SITE_URL').'/storage/'.$pathTT;
        }

        $subject->update([
            'Name'          => $request->name,
            'FileType'      => $fileType,
            'FileExtension' => $extFT,
            'Path'          => $path,
            'Thumbnail'     => $pathTT,
            'Duration'      => ($request->duration) ? $request->duration : $subject->duration, 
            'EditedTime'    => time(),
            'EditedByIP'    => '127.0.0.1'
        ]);

        return new TheoryResource($subject);
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
        $subject = Subject::where('ID', $id)->first();

        if (!$subject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Initialize
        $explodePath  = explode('/', $subject->Path);
        $explodePathT = explode('/', $subject->Thumbnail);
        
        @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/materi/'.$explodePath[8].'/'.$explodePath[9]);
        @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$explodePathT[8].'/'.$explodePathT[9]);

        $subject->delete();

        return response()->json([
            'status'    => 'Success',
            'message'   => 'Berhasil menghapus data.'
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
