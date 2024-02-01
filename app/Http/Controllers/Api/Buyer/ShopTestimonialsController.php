<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ShopTestimonials;
use App\Company;
use Validator;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopTestimonialsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (auth()->check()) {
            if (auth()->user()->role_id == 1) {
                // Initialize
                $shopTestimonials = ShopTestimonials::where(['store_id' => auth()->user()->company_id, 'status' => 1])->latest()->get();
            } else if (auth()->user()->role_id == 6) {
                // Initialize
                $shopTestimonials = ShopTestimonials::where(['store_id' => request('store_id'), 'status' => 1])->latest()->get();
            }
        } else {
            if (request('store_id')) {
                $shopTestimonials = ShopTestimonials::where(['store_id' => request('store_id'), 'status' => 1])->latest()->get();
            } else {
                $shopTestimonials = ShopTestimonials::where(['status' => 1])->latest()->get();
            }
        }

        $listData           = $this->paginate($shopTestimonials, 20, null, ['path' => $request->fullUrl()]);
        $data               = [];

        foreach($shopTestimonials as $val) {
            // Initialize
            $row['id']                   = $val->id;
            $row['store_id']             = $val->store_id;
            $row['user_id']              = $val->user_id;
            $row['name']                 = $val->name;
            $row['phone']                = $val->phone;
            $row['type']                 = $val->type;
            $row['position']             = $val->position;
            $row['skill']                = $val->skill;
            $row['project_address']      = $val->project_address;
            $row['description_project']  = $val->description_project;
            $row['testimonial_details']  = $val->testimonial_details;
            $row['store']                = $val->company;
            $row['status']               = $val->status;
            $row['created_at']           = $val->created_at;
            $row['updated_at']           = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $listData->currentPage(),
                'from'              => 1,
                'last_page'         => $listData->lastPage(),
                'next_page_url'     => $listData->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $listData->perPage(),
                'prev_page_url'     => $listData->previousPageUrl(),
                'total'             => $listData->total()
            ]
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
        // Validation
        $validator = Validator::make(request()->all(), [
            'store_id'              => 'required',
            'name'                  => 'required',
            'phone'                 => 'required',
            'type'                  => 'required|in:0,1',
            'testimonial_details'   => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request('type') == 0) {
            $validator = Validator::make(request()->all(), [
                'position' => 'required',
                'skill'    => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        } else {
            $validator = Validator::make(request()->all(), [
                'project_address'     => 'required',
                'description_project' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        // Check Store
        $store = Company::where('ID', request('store_id'))->first();

        if (!$store) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Toko dengan ID ('.request('store_id').') tidak ditemukan.'
            ]);
        }

        // Check Exists Data
        $existsData = ShopTestimonials::where(['store_id' => request('store_id'), 'user_id' => auth()->user()->id])->first();

        if ($existsData) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan penilaian, untuk toko ini ('.$store->Name.')'
            ]);
        }

        // Check Shop Owner
        if (auth()->user()->company_id) {
            if (request('store_id') == auth()->user()->company_id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak bisa menilai Toko sendiri.'
                ]);
            }
        }

        $data = ShopTestimonials::create([
            'user_id'               => auth()->user()->id,
            'store_id'              => request('store_id'),
            'name'                  => request('name'),
            'phone'                 => request('phone'),
            'type'                  => request('type'),
            'position'              => request('position'),
            'skill'                 => request('skill'),
            'project_address'       => request('project_address'),
            'description_project'   => request('description_project'),
            'testimonial_details'   => request('testimonial_details')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Penilaian berhasil.',
            'data'      => $data
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
        // Check Data
        $shopTestimonials = ShopTestimonials::with('company')->where('id', $id)->first();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $shopTestimonials
        ]);
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
            'name'                  => 'required',
            'phone'                 => 'required',
            'type'                  => 'required|in:0,1',
            'testimonial_details'   => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        if (request('type') == 0) {
            $validator = Validator::make(request()->all(), [
                'position' => 'required',
                'skill'    => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        } else {
            $validator = Validator::make(request()->all(), [
                'project_address'     => 'required',
                'description_project' => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        // Check Exists Data
        $shopTestimonials = ShopTestimonials::where('id', $id)->first();

        if (!$shopTestimonials) {
            return respons()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        // Check Access
        if ($shopTestimonials->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk mengubah data ini.'
            ]);
        }

        $shopTestimonials->update([
            'name'                  => request('name'),
            'phone'                 => request('phone'),
            'type'                  => request('type'),
            'position'              => request('position'),
            'skill'                 => request('skill'),
            'project_address'       => request('project_address'),
            'description_project'   => request('description_project'),
            'testimonial_details'   => request('testimonial_details'),
            'status'                => ($shopTestimonials->status == 2) ? 0 : $shopTestimonials->status
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil diperbarui.',
            'data'      => $shopTestimonials
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
        // Check Data
        $shopTestimonials = ShopTestimonials::where('id', $id)->first();

        if (!$shopTestimonials) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        // Check Access
        if ($shopTestimonials->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk menghapus data ini.'
            ]);
        }

        $shopTestimonials->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil dihapus.',
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
