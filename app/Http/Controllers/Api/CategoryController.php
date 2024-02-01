<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Http\Resources\CategoryCollection;
use DB;
use Validator;

class CategoryController extends Controller
{
    public function index()
    {
        if (request('random-results')) {
            // Initialize
            $category = DB::table('category')
                        ->select('id','name','description','thumbnail','banner')
                        ->inRandomOrder()
                        ->get();
        } else {
            // Initialize
            $category = DB::table('category')
                        ->select('id','name','description','thumbnail','banner')
                        ->orderBy('name', 'ASC')
                        ->get();
        }

        // Initialize
        $data = [];

        foreach($category as $val) {
            $row['id']          = $val->id;
            $row['name']        = $val->name;
            $row['description'] = $val->description;
            $row['thumbnail']   = $val->thumbnail;
            $row['banner']      = env('SITE_URL').'/'.$val->banner;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data kategori.',
            'data'      => $data
        ]);
    }

    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'thumbnail' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $thumbnail = null;

        if ($request->file('thumbnail')) {
            $thumbnail = $request->file('thumbnail')->store('uploads/category/thumbnail', 'public');
            $thumbnail = 'storage/' . $thumbnail;
        }

        $banner = null;

        if ($request->file('banner')) {
            $banner = $request->file('banner')->store('uploads/category/banner', 'public');
            $banner = 'storage/' . $banner;
        }

        $category = Category::create([
            'thumbnail' => $thumbnail,
            'name' => $request->name,
            'description' => $request->description,
            'banner' => $banner,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'category berhasil disimpan',
            'result' => $category
        ], 200);
    }

    public function detail(Category $category, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail category web',
            'result' => $category
        ], 200);
    }

    public function update(Category $category, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'thumbnail' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            'banner' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $thumbnail = $category->thumbnail;

        if ($request->file('thumbnail')) {
            $thumbnail = $request->file('thumbnail')->store('uploads/category/thumbnail', 'public');
            $thumbnail = 'storage/' . $thumbnail;

            // Check Thumbnail
            if ($category->thumbnail) {
                $explodePath = explode('/', $category->thumbnail);

                @unlink('storage/uploads/category/thumbnail/'.end($explodePath));
            }
        }

        $banner = $category->banner;

        if ($request->file('banner')) {
            $banner = $request->file('banner')->store('uploads/category/banner', 'public');
            $banner = 'storage/' . $banner;

            // Check banner
            if ($category->banner) {
                $explodePath = explode('/', $category->banner);

                @unlink('storage/uploads/category/banner/'.end($explodePath));
            }
        }

        $category->update([
            'thumbnail' => $thumbnail,
            'name' => $request->name,
            'description' => $request->description,
            'banner' => $banner,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'category berhasil disimpan',
            'result' => $category
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Category $category)
    {
        // Check Thumbnail
        if ($category->thumbnail) {
            $explodePath = explode('/', $category->thumbnail);

            @unlink('storage/uploads/category/thumbnail/'.end($explodePath));
        }

        if ($category->banner) {
            $explodePath = explode('/', $category->banner);

            @unlink('storage/uploads/category/banner/'.end($explodePath));
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'category berhasil dihapus',
        ], 200);
    }
}
