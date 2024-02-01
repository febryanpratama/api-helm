<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Company;
use App\ShopTestimonials;

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
        // Validate
        if (!auth()->user()->is_admin_access) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        // Initialize
        $shopTestimonials   = $this->_shopTestimonials();
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
        // Check Data
        $shopTestimonials = ShopTestimonials::where('id', $id)->first();

        if (!$shopTestimonials) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        $shopTestimonials->update([
            'status' => request('status')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil diperbarui.',
            'data'      => $shopTestimonials
        ]);
    }

    private function _shopTestimonials()
    {
        // Initialize
        $query = (new ShopTestimonials)->newQuery();

        if (request('store_id')) {
            $query->where('store_id', request('store_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        } else {
            $query->where('status', 0);
        }

        return $query->latest()->get();
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
