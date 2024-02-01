<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\GlobalFile;
use Validator;

class UploadGlobalFileController extends Controller
{
    public function store()
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'file'      => 'required|mimes:jpg,png,jpeg,gif,pdf,docx,mp4,mkv',
            'category'  => 'in:0,1'
        ]);

        if (request('category') == 1) {
            $validator = Validator::make(request()->all(), [
                'key'       => 'required',
                'course_id' => 'required|integer'
            ]); 
        }

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $fileData = request()->file('file');
        $extFT    = $fileData->getClientOriginalExtension();
        
        // Check Extension
        if ($extFT == 'php' || $extFT == 'sql' || $extFT == 'js' || $extFT == 'zip' || $extFT == 'rar') {
            return response()->json([
                'status'    => false,
                'message'   => 'Extension File Not Supported!'
            ]);
        }

        // Uploaded
        $path = $fileData->store('uploads/global', 'public');

        $data = GlobalFile::create([
            'user_id'   => auth()->user()->id,
            'path'      => env('SITE_URL').'/storage/'.$path,
            'category'  => (request('category')) ? request('category') : 0,
            'key'       => (request('key')) ? request('key') : null,
            'course_id' => (request('course_id')) ? request('course_id') : null
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Upload file berhasil',
            'data'      => [
                'detail' => $data,
                'path'   => $data->path
            ]
        ]);
    }

    public function show()
    {
        // Initialize
        $data = GlobalFile::where(['course_id' => request('course_id'), 'category' => 1])->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function destroy(Request $request)
    {
        if (count($request->remove_thumbnail)) {
            foreach ($request->remove_thumbnail as $val) {
                // Explode
                $explodePath = explode('/', $val);
    
                @unlink('storage/uploads/global/'.$explodePath[6]);
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.'
        ]);
    }
}
