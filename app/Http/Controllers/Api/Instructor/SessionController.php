<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SessionRequest;
use App\Course;
use App\Majors;
use App\UserCourse;
use App\ListTitleAutocomplete;
use App\CategoryTitleAutocomplete;
use App\Subject;
use App\MajorsSubject;
use DB;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $courseId = request('course_id');

        if ($courseId) {
            // Initialize
            $course = Course::where('id', $courseId)->first();

            if (!$course) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Paket Kursus tidak ditemukan!',
                    'data'      => [
                        'error_code' => 'no_data_found'
                    ]
                ]);
            }

            $session = Majors::where('IDCourse', $courseId)->get();
            $data    = [];

            // Custom Paginate
            $session = $this->paginate($session, 20, null, ['path' => $request->fullUrl()]);

            foreach ($session as $val) {
                // Initialize
                $row['id']          = $val->ID;
                $row['name']        = $val->Name;
                $row['details']     = $val->Details;
                $row['created_at']  = date('Y-m-d H:i:s', $val->AddedTime);
                $row['updated_at']  = date('Y-m-d H:i:s', $val->EditedTime);

                $data[] = $row;
            }

            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil mendapatkan data Sesi.',
                'data'      => $data,
                'meta'      => [
                    'current_page'      => $session->currentPage(),
                    'from'              => 1,
                    'last_page'         => $session->lastPage(),
                    'next_page_url'     => $session->nextPageUrl(),
                    'path'              => $request->fullUrl(),
                    'per_page'          => $session->perPage(),
                    'prev_page_url'     => $session->previousPageUrl(),
                    'total'             => $session->total()
                ]
            ]);
        }

        return response()->json([
            'message'   => 'The given data was invalid.',
            'errors'    => [
                'course_id' => [
                    'Paket Kursus Id dibutuhkan.'
                ]
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SessionRequest $request)
    {
        // Initialize
        $course = Course::where('id', $request->course_id)->first();

        if (!$course) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Paket Kursus tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if (auth()->user()->id != $course->user_id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                   'error_code' => 'not_accessible'
                ]
            ]);
        }

        // Check File Details Item
        try {
            // Initialize
            $itemDetails = request('file_details_item');

            if ($itemDetails) {
                foreach ($itemDetails as $key => $val) {
                    // Initialize
                    $fileItemDetails = request()->file('file_details_item')[$key];
                    $extFID          = $fileItemDetails->getClientOriginalExtension();

                    // Check Max Size
                    if ($fileItemDetails->getSize() > 10000000) { // 10 MB
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'File ('.$fileItemDetails->getClientOriginalName().') melebihi Max Size File upload. (Max Size File Upload 10MB)'
                        ]);

                        break;
                    }

                    // Check Extension
                    $supportedExt = ['pdf', 'mkv', 'mp4'];

                    if (!in_array($extFID, $supportedExt)) {
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Extension File ('.$fileItemDetails->getClientOriginalName().') tidak didukung. (Supported Extension PDF, MKV and MP4).'
                        ]);

                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage().' In Line '.$e->getLine()
            ]);
        }

        // Check List Title Autocomplete
        $autocomplates = DB::table('list_title_autocomplete')
                        ->leftJoin('category_title_autocomplete', 'list_title_autocomplete.id', '=', 'category_title_autocomplete.list_title_autocomplete_id')
                        ->where('category_title_autocomplete.category_id', $course->courseCategory->category_id)
                        ->where('list_title_autocomplete.name', 'LIKE', '%'.request('name').'%')
                        ->get();

        if (count($autocomplates) == 0) {
            // Create Data
            $ltac = ListTitleAutocomplete::create([
                        'prefix' => null,
                        'name'   => $request->name
                    ]);

            if ($ltac) {
                CategoryTitleAutocomplete::create([
                    'category_id'                   => $course->courseCategory->category_id,
                    'list_title_autocomplete_id'    => $ltac->id
                ]);
            }
        }

        $majors = Majors::create([
            'IDCompany'     => auth()->user()->company->ID,
            'IDCourse'      => $request->course_id,
            'Name'          => $request->name,
            'Details'       => $request->details,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => '',
            'EditedByIP'    => ''
        ]);

        if ($majors) {
            // Initialize
            $itemDetails = request('file_details_item');

            if ($itemDetails) {
                foreach ($itemDetails as $key => $val) {
                    // Initialize
                    $fileItemDetails = request()->file('file_details_item')[$key];
                    $extFID          = $fileItemDetails->getClientOriginalExtension();

                    if ($extFID == 'pdf') {
                        $fileType = '1';
                    } else if ($extFID == 'mp4' || $extFID == 'mkv') {
                        $fileType = '2';
                    }

                    // Upload File
                    $path = $fileItemDetails->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$request->name, 'public');

                    $subject = Subject::create([
                        'IDCompany'     => auth()->user()->company_id,
                        'Name'          => request('caption_details_item')[$key],
                        'FileType'      => $fileType,
                        'FileExtension' => $extFID,
                        'Path'          => env('SITE_URL').'/storage/'.$path,
                        'AddedTime'     => time(),
                        'AddedByIP'     => '127.0.0.1',
                        'EditedTime'    => '',
                        'EditedByIP'    => ''
                    ]);

                    if ($subject) {
                        if ($subject) {
                            MajorsSubject::create([
                                'major_id'    => $majors->ID,
                                'subject_id'  => $subject->ID
                            ]);
                        }
                    }
                }
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data sesi.',
            'data'      => $majors
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
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
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
        $majors = Majors::where('ID', $id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        // Validate Account
        $course = Course::where('id', $majors->IDCourse)->first();

        if (auth()->user()->id != $course->user_id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                   'error_code' => 'not_accessible'
                ]
            ]);
        }


        // Check File Details Item
        try {
            // Initialize
            $itemDetailsId = request('item_details_id');

            if ($itemDetailsId) {
                foreach ($itemDetailsId as $key => $val) {
                    // Check Details ID
                    $subject = Subject::where('ID', $val)->first();

                    if (!$subject) {
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Detail Item dengan ID ('.$val.') tidak ditemukan.'
                        ]);

                        break;
                    }

                    // Check Majors
                    if ($subject->majorsSubjectv2) {
                        if ($subject->majorsSubjectv2->major_id != $id) {
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'Item dengan ID ('.$val.') tidak masuk ke Detail ('.$majors->Name.').'
                            ]);

                            break;
                        }
                    }

                    if (isset(request()->file('file_details_item')[$key])) {
                        // Initialize
                        $fileItemDetails = request()->file('file_details_item')[$key];
                        $extFID          = $fileItemDetails->getClientOriginalExtension();

                        // Check Max Size
                        if ($fileItemDetails->getSize() > 10000000) { // 10 MB
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'File ('.$fileItemDetails->getClientOriginalName().') melebihi Max Size File upload. (Max Size File Upload 10MB)'
                            ]);

                            break;
                        }

                        // Check Extension
                        $supportedExt = ['pdf', 'mkv', 'mp4'];

                        if (!in_array($extFID, $supportedExt)) {
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'Extension File ('.$fileItemDetails->getClientOriginalName().') tidak didukung. (Supported Extension PDF, MKV and MP4).'
                            ]);

                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage().' In Line '.$e->getLine()
            ]);
        }

        // Check List Title Autocomplete
        $autocomplates = DB::table('list_title_autocomplete')
                        ->leftJoin('category_title_autocomplete', 'list_title_autocomplete.id', '=', 'category_title_autocomplete.list_title_autocomplete_id')
                        ->where('category_title_autocomplete.category_id', $course->courseCategory->category_id)
                        ->where('list_title_autocomplete.name', 'LIKE', '%'.request('name').'%')
                        ->get();

        if (count($autocomplates) == 0) {
            // Create Data
            $ltac = ListTitleAutocomplete::create([
                        'prefix' => null,
                        'name'   => $request->name
                    ]);

            if ($ltac) {
                CategoryTitleAutocomplete::create([
                    'category_id'                   => $course->courseCategory->category_id,
                    'list_title_autocomplete_id'    => $ltac->id
                ]);
            }
        }

        $majors->update([
            'Name'          => $request->name,
            'Details'       => $request->details,
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => time(),
            'EditedByIP'    => '127.0.0.1'
        ]);

        // Check File Details Item
        try {
            // Initialize
            $itemDetailsId = request('item_details_id');

            if ($itemDetailsId) {
                foreach ($itemDetailsId as $key => $val) {
                    // Check Details ID
                    $subject = Subject::where('ID', $val)->first();

                    if (!$subject) {
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Detail Item dengan ID ('.$val.') tidak ditemukan.'
                        ]);

                        break;
                    }

                    // Check Majors
                    if ($subject->majorsSubjectv2) {
                        if ($subject->majorsSubjectv2->major_id != $id) {
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'Item dengan ID ('.$val.') tidak masuk ke Detail ('.$majors->Name.').'
                            ]);

                            break;
                        }
                    }

                    if (isset(request()->file('file_details_item')[$key])) {
                        // Initialize
                        $fileItemDetails = request()->file('file_details_item')[$key];
                        $extFID          = $fileItemDetails->getClientOriginalExtension();

                        // Check Max Size
                        if ($fileItemDetails->getSize() > 10000000) { // 10 MB
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'File ('.$fileItemDetails->getClientOriginalName().') melebihi Max Size File upload. (Max Size File Upload 10MB)'
                            ]);

                            break;
                        }

                        // Check Extension
                        $supportedExt = ['pdf', 'mkv', 'mp4'];

                        if (!in_array($extFID, $supportedExt)) {
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'Extension File ('.$fileItemDetails->getClientOriginalName().') tidak didukung. (Supported Extension PDF, MKV and MP4).'
                            ]);

                            break;
                        }

                        // Check File
                        if ($fileItemDetails) {
                            if ($extFID == 'pdf') {
                                $fileType = '1';
                            } else if ($extFID == 'mp4' || $extFID == 'mkv') {
                                $fileType = '2';
                            }

                            // Delete File
                            $explodePath = explode('/', $subject->Path);
                            @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/materi/'.$explodePath[8].'/'.$explodePath[9]);
                            
                            // Upload File
                            $path = $fileItemDetails->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$request->name, 'public');
                            $path = env('SITE_URL').'/storage/'.$path;
                        }
                    }

                    $subject->update([
                        'Name'          => (request('caption_details_item')[$key]) ? request('caption_details_item')[$key] : $subject->Name,
                        'FileType'      => (isset($fileType)) ? $fileType : $subject->fileType,
                        'FileExtension' => (isset($extFID)) ? $extFID : $subject->extFID,
                        'Path'          => (isset($path)) ? $path : $subject->path,
                        // 'Thumbnail'     => $pathTT,
                        // 'Duration'      => ($request->duration) ? $request->duration : $subject->duration, 
                        'EditedTime'    => time(),
                        'EditedByIP'    => '127.0.0.1'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage().' In Line '.$e->getLine()
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data sesi.',
            'data'      => $majors
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
        $majors = Majors::where('ID', $id)->first();

        if (!$majors) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Sesi tidak ditemukan!',
                'data'      => [
                    'error_code' => 'no_data_found'
                ]
            ]);
        }

        if ($majors->IDCompany == auth()->user()->company_id) {
            $majors->delete();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data Sesi.'
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
