<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LandingTemplate;
use Illuminate\Http\Request;
use Validator;

class TemplateController extends Controller
{
    public function index()
    {
        // Initialize
        $template = LandingTemplate::latest()->paginate(10);


        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list template',
            'result' => $template
        ], 200);
    }


    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'file'  => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // Initialze
        $image = null;

        if (request()->file('file')) {
            // Initialize
            $path       = request()->file('file')->store('uploads/template', 'public');
            $image  = env('SITE_URL').'/storage/'.$path;
        }

        $template = LandingTemplate::create([
            'name'         => $request->name,
            'status'        => $request->status,
            'image'     => $image,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'template berhasil disimpan',
            'result' => $template
        ], 200);
    }


    public function show(LandingTemplate $template)
    {
        return response()->json([
            'status'    => true,
            'code' => 200,
            'message'   => 'Data tersedia',
            'result'      => $template
        ]);
    }

    public function update(Request $request, LandingTemplate $template)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'file'  => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'status' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // Initialize
        $image = $template->image;

        if (request()->file('file')) {
            // Initialize
            $path      = request()->file('file')->store('uploads/template', 'public');
            $image = env('SITE_URL').'/storage/'.$path;

            // Check image
            if ($template->image) {
                $explodePath = explode('/', $template->image);

                @unlink('storage/uploads/template/'.$explodePath[6]);
            }
        }

        $template->update([
            'name'         => $request->name,
            'status'        => $request->status,
            'image'     => $image,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'template berhasil disimpan',
            'result' => $template
        ], 200);
    }

    public function destroy(LandingTemplate $template)
    {
        // Check Thumbnail
        if ($template->image) {
            $explodePath = explode('/', $template->image);

            @unlink('storage/uploads/template/'.$explodePath[6]);
        }

        $template->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'template berhasil dihapus',
        ], 200);
    }

}
