<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;
use App\Division;
use App\DivisionSubject;
use App\Majors;
use App\MajorsSubject;
use App\Course;
use App\UserCourse;
use App\TheoryLock;
// use FFMpeg;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $subjects = MajorsSubject::where('major_id', request('majorId'))->get();
        $major    = Majors::where('ID', request('majorId'))->first();
        $course   = Course::where('id', $major->IDCourse)->first();

        // Check User Course
        $userCourse = UserCourse::where(['user_id' => auth()->user()->id, 'course_id' => $course->id])->first();

        $data = [];
        foreach ($subjects as $key => $val) {
            // Initialize
            $subject = $val->subject;

            $row['ID']           = $subject->ID;
            $row['Name']         = $subject->Name;
            $row['FileType']     = $subject->FileType;
            $row['MajorId']      = $val->major_id;
            $row['courseExists'] = ($userCourse) ? 'y' : 'n';
            $row['slug']         = $course->slug;

            // Course Lock
            $theoryLock = TheoryLock::where(['user_id' => auth()->user()->id, 'subject_id' => $subject->ID])->first();
            
            if ($theoryLock) {
                $row['unlock'] = true;
            } else {
                $row['unlock'] = false;
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
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
        // Initialize
        $fileTheory  = request()->file('upload_file');
        $extFT       = $fileTheory->getClientOriginalExtension();

        // Check Max Size
        if ($fileTheory->getSize() > 100000000) { // 100 MB
            return response()->json([
                'status'    => false,
                'message'   => 'Max Size File Materi 100 MB'
            ]);

            die;
        }

        // Check Extension
        if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif' || $extFT == 'png' || $extFT == 'jpg' || $extFT == 'docx') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension Theory File Not Supported!'
            ]);

            die;
        }

        if ($extFT == 'pdf') {
            $fileType = '1';
        } else if ($extFT == 'mp4' || $extFT == 'mkv') {
            $fileType = '2';
        }

        // Initialize
        $majorDetail = Majors::where('ID', $request->majors_id)->first();

        // Upload File
        $path = $fileTheory->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$majorDetail->Name, 'public');

        $subject = Subject::create([
            'IDCompany'     => auth()->user()->company_id,
            'Name'          => $request->Name,
            'FileType'      => $fileType,
            'FileExtension' => $extFT,
            'Path'          => env('SITE_URL').'/storage/'.$path,
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1',
            'EditedTime'    => '',
            'EditedByIP'    => ''
        ]);

        if ($subject) {
            MajorsSubject::create([
                'major_id'    => $request->majors_id,
                'subject_id'  => $subject->ID
            ]);
        }

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
    public function edit(Subject $subject)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $subject
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Subject $subject)
    {
        // Initialize
        $fileTheory  = request()->file('upload_file');
        $majorDetail = Majors::where('ID', $request->majors_id)->first();
        $path        = $subject->Path;
        $fileType    = $subject->FileType;
        // $fileTheoryT = request()->file('upload_file_thumbnail');
        // $thumbnail   = $subject->Thumbnail;

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
            if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js'|| $extFT == 'gif' || $extFT == 'png' || $extFT == 'jpg') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Theory File Not Supported!'
                ]);

                die;
            }

            if ($extFT == 'docx' || $extFT == 'pdf') {
                $fileType = '1';
            } else if ($extFT == 'mp4' || $extFT == 'mkv') {
                $fileType = '2';
            }

            // Upload File
            $path = $fileTheory->store('uploads/course/'.auth()->user()->company->Name.'/materi/'.$majorDetail->Name, 'public');
            $path = env('SITE_URL').'/storage/'.$path;
        }

        // if ($fileTheoryT) {
        //     // Delete File
        //     $explodePathT = explode('/', $subject->Thumbnail);
        //     @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$explodePathT[8].'/'.$explodePathT[9]);

        //     $thumbnail = $fileTheoryT->store('uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$majorDetail->Name, 'public');
        //     $thumbnail = env('SITE_URL').'/storage/'.$thumbnail;
        // }

        $subject = $subject->update([
            'Name'          => $request->Name,
            'FileType'      => $fileType,
            'Path'          => $path,
            // 'Thumbnail'     => $thumbnail,
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
    public function destroy(Subject $subject)
    {
        // Check User Course
        $theorySession = MajorsSubject::where('subject_id', $subject->ID)->first();

        if ($theorySession) {
            // Initialize
            $major = Majors::where('ID', $theorySession->major_id)->first();

            if ($major) {
                // Initialize
                $userCourse = UserCourse::where('course_id', $major->IDCourse)->count();

                if ($userCourse > 0) {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'Materi tidak bisa di hapus'
                    ]);

                    die;
                }
            }
        }

        // Initialize
        $explodePath  = explode('/', $subject->Path);
        $explodePathT = explode('/', $subject->Thumbnail);
        
        @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/materi/'.$explodePath[8].'/'.$explodePath[9]);
        @unlink('storage/uploads/course/'.auth()->user()->company->Name.'/thumbnail/'.$explodePathT[8].'/'.$explodePathT[9]);

        $subject->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil dihapus'
        ]);
    }

    public function destroySubjectRelation(Division $division)
    {
        $division->subject()->detach(request('subjectId'));

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Subjek berhasil dihapus'
            ]);

            die;
        }

        return redirect()->back();
    }
}
