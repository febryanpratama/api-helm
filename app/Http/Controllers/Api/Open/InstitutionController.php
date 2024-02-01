<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\InstitutioResource;
use App\Company;
use App\Course;
use App\Rating;
use App\Transaction;
use App\MasterLocation;
use Str;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InstitutionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (request('search')) {
            // Initialize
            $store = Company::orderBy('Name', 'ASC')
                        ->where('Name', 'LIKE', '%'.request('search').'%')
                        ->where('is_verified', 1)
                        ->get();
        } else {
            // Initialize
            $store = Company::orderBy('Name', 'ASC')
                    ->where('is_verified', 1)
                    ->get();
        }

        // Custom Paginate
        $stores = $this->paginate($store, 20, null, ['path' => $request->fullUrl()]);
        $data   = [];

        foreach ($stores as $val) {
            // Initialize
            $courses        = Course::where('user_id', $val->user->id)->pluck('id');
            $totalRate      = Rating::whereIn('course_id', $courses)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $transaction    = Transaction::where('store_id', $val->ID)->count();
            $masterLocation = MasterLocation::where('id', $val->city_id)->first();
            $cityName       = '';
            $storeId        = Crypt::encrypt($val->ID);
            
            if ($masterLocation) {
                $cityName = $masterLocation->kota;
            }

            // Initialize
            $row['id']              = $val->ID;
            $row['name']            = $val->Name;
            $row['slug']            = Str::slug($val->Name.'-'.$cityName, '-').'-'.$storeId;
            // $row['encrypt_id']      = $storeId;
            $row['logo']            = $val->Logo;
            $row['seller_id']       = $val->user->id;
            $row['seller_name']     = $val->user->name;
            $row['store_details']   = [
                'store_id'      => $val->ID,
                'store_name'    => $val->Name,
                'store_address' => $val->Address,
                'store_city'    => (isset($val->city)) ? $val->city : null,
                'store_phone'   => $val->Phone,
                'store_logo'    => $val->Logo,
                'social_media'  => [
                    'facebook'      => $val->facebook,
                    'instagram'     => $val->instagram,
                    'youtube'       => $val->youtube,
                    'linked'        => $val->linked
                ]
            ];
            
            $row['total_product']   = count($courses);
            $row['total_rating']    = ($totalRate) ? $totalRate : 0;
            $row['total_customer']  = $transaction;
            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Toko.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $stores->currentPage(),
                'from'              => 1,
                'last_page'         => $stores->lastPage(),
                'next_page_url'     => $stores->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $stores->perPage(),
                'prev_page_url'     => $stores->previousPageUrl(),
                'total'             => $stores->total()
            ]
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        // Initialize
        $explodeSlug = explode('-', $slug);
        $endExplode  = end($explodeSlug);

        if (count($explodeSlug) > 1) {
            try {
                $decrypted = Crypt::decrypt($endExplode);
            } catch (DecryptException $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            $decrypted = $slug;
        }

        $institution = Company::where('ID', $decrypted)->first();

        if (!$institution) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Toko dengan id ('.$id.') tidak ditemukan.'
            ]);
        }

        return new InstitutioResource($institution);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
