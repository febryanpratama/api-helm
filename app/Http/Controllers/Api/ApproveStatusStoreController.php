<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Company;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ApproveStatusStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (auth()->user()->is_admin_access) {
            // Initialize
            $store = $this->_store();
        } else {
            // Initialize
            $store = $this->_store();

            Company::where([
                        'status'    => 2,
                        'city_id'   => (auth()->user()->company) ? auth()->user()->company->ID : null
                    ])
                    ->orderBy('ID', 'DESC')
                    ->get();
        }

        $listData = $this->paginate($store, 20, null, ['path' => $request->fullUrl()]);
        $data     = [];
        
        foreach($listData as $val) {
            // Initialize
            $row['store_id']        = $val->ID;
            $row['store_name']      = $val->Name;
            $row['phone']           = $val->Phone;
            $row['address']         = $val->Address;
            $row['email']           = $val->Email;
            $row['logo']            = $val->Logo;
            $row['status']          = $val->status;
            $row['status_detail']   = companyStatus($val->status);
            $row['is_verified']     = $val->is_verified;
            $row['city_id']         = $val->city_id;
            $row['city']            = ($val->city) ? $val->city : '-';
            $row['social_media']    = [
                'facebook'  => $val->facebook,
                'instagram' => $val->instagram,
                'youtube'   => $val->youtube,
                'linkedin'  => $val->linkedin
            ];
            $row['admin']           = $val->admin;

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
        $store = Company::where('ID', $id)->first();

        if (!$store) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Toko dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if (!auth()->user()->is_admin_access) {
            // Check City Id
            if (!auth()->user()->company->city_id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Toko anda belum mendaftarkan wilayah.',
                ]);
            }

            if (auth()->user()->company->city_id != $store->city_id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak memiliki akses, untuk meng-Approve toko ('.$store->Name.')',
                ]);
            }

            if (auth()->user()->company->status != 1) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak memiliki akses.',
                ]);
            }
        }

        $store->update([
            'is_verified'        => request('is_verified'),
            'reason_for_refusal' => request('reason_for_refusal')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil diperbarui.',
            'data'      => $store
        ]);
    }

    private function _store()
    {
        // Initialize
        $store = (new Company)->newQuery();

        if (auth()->user()->is_admin_access) {
            if (request('city_id')) {
                $store->where('city_id', request('city_id'));
            }

            $store->where('is_verified', 0);
        } else {
            $store->where('city_id', (auth()->user()->company) ? auth()->user()->company->city_id : 0);
            $store->where('ID', '!=', auth()->user()->company_id);

            if (request('is_verified')) {
                $store->where('is_verified', 'LIKE', '%'.request('is_verified').'%');
            } else {
                $store->where('is_verified', 0);
            }
        }

        if (request('status')) {
            $store->where('status', 'LIKE', '%'.request('status').'%');
        }

        // Initialize
        return $store->orderBy('ID', 'DESC')->get();
    }

    public function reSubmission()
    {
        // Check Store
        $store = auth()->user()->company;

        if (!$store) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda belum memiliki toko.'
            ]);
        }
        
        if ($store->status == 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Status toko anda sudah di approve admin.'
            ]);
        }

        $store->update([
            'is_verified'        => 0,
            'reason_for_refusal' => null
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Pengajuan berhasil.',
            'data'      => $store
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
