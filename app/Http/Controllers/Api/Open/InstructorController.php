<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Course;
// use App\UserCourse;
use App\Rating;
use App\Transaction;
use App\MasterLocation;
use Crypt;
use Str;
use DB;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $data   =[];
        $stores = DB::table('company')
                    ->join('users', 'company.ID', '=', 'users.company_id')
                    ->select(
                        'company.*',
                        'users.name as username',
                        'users.id as user_id'
                    )
                    ->where('company.Name', 'LIKE', '%'.request('search').'%')
                    ->paginate(20);

        foreach($stores as $val) {
            // Initialize
            $masterLocation = MasterLocation::where('id', $val->city_id)->first();
            $cityName       = '';
            $storeId        = Crypt::encrypt($val->ID);
            
            if ($masterLocation) {
                $cityName = $masterLocation->kota;
            }

            $row['company_name']    = $val->Name;
            $row['slug']            = Str::slug($val->Name.'-'.$cityName, '-').'-'.$storeId;
            $row['id']              = $val->user_id;
            $row['instructor_name'] = $val->username;
            $row['avatar']          = ($val->Logo) ? $val->Logo : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg';

            // Initialize
            $courses        = Course::where('user_id', $val->user_id)->pluck('id');
            $totalRate      = Rating::whereIn('course_id', $courses)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $transaction    = Transaction::where('store_id', $val->ID)->count();

            $row['total_product']  = count($courses);
            $row['total_rating']   = ($totalRate) ? $totalRate : 0;
            $row['total_customer'] = $transaction;
            $row['store_details']  = [
                'store_id'      => $val->ID,
                'store_name'    => $val->Name,
                'store_address' => $val->Address,
                // 'store_city'    => (isset($val->city)) ? $val->city : null,
                'store_phone'   => $val->Phone,
                'store_logo'    => $val->Logo,
                'social_media'  => [
                    'facebook'      => $val->facebook,
                    'instagram'     => $val->instagram,
                    'youtube'       => $val->youtube,
                    'linked'        => $val->linkedin
                ]
            ];

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data mentor.',
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

    public function indexOld(Request $request)
    {
        if (request('search')) {
            // Initialize
            $users = User::with('courses','company')
                        ->where('is_instructor', '1')
                        ->where('name', 'LIKE', '%'.request('search').'%')
                        ->whereNotNull('company_id')
                        ->orderBy('name', 'ASC')
                        ->get();
        } else {
            // Initialize
            $users = User::with('courses','company')
                    ->where('is_instructor', '1')
                    ->whereNotNull('company_id')
                    ->orderBy('name', 'ASC')
                    ->get();
        }

        // Custom Paginate
        $users = $this->paginate($users, 20, null, ['path' => $request->fullUrl()]);

        $data = [];
        foreach ($users as $val) {
            // Initialize
            $masterLocation = MasterLocation::where('id', $val->city_id)->first();
            $cityName       = '';
            $storeId        = Crypt::encrypt($val->company_id);
            
            if ($masterLocation) {
                $cityName = $masterLocation->kota;
            }

            $row['company_name']    = $val->company->Name;
            $row['slug']            = Str::slug($val->company->Name.'-'.$cityName, '-').'-'.$storeId;
            $row['id']              = $val->id;
            $row['instructor_name'] = $val->name;
            // $row['avatar']          = ($val->avatar) ? $val->avatar : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg';
            $row['avatar']          = ($val->company->Logo) ? $val->company->Logo : 'https://st4.depositphotos.com/4329009/19956/v/600/depositphotos_199564354-stock-illustration-creative-vector-illustration-default-avatar.jpg';

            // Initialize
            $courses        = Course::where('user_id', $val->id)->pluck('id');
            $totalRate      = Rating::whereIn('course_id', $courses)->selectRaw('SUM(rating)/COUNT(course_id) AS avg_rating')->first()->avg_rating;
            $transaction    = Transaction::where('store_id', $val->company_id)->count();

            $row['total_product']  = count($val->courses);
            $row['total_rating']   = ($totalRate) ? $totalRate : 0;
            $row['total_customer'] = $transaction;
            $row['store_details']  = [
                'store_id'      => $val->company->ID,
                'store_name'    => $val->company->Name,
                'store_address' => $val->company->Address,
                'store_city'    => (isset($val->company->city)) ? $val->company->city : null,
                'store_phone'   => $val->company->Phone,
                'store_logo'    => $val->company->Logo,
                'social_media'  => [
                    'facebook'      => $val->company->facebook,
                    'instagram'     => $val->company->instagram,
                    'youtube'       => $val->company->youtube,
                    'linked'        => $val->company->linked
                ]
            ];

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data mentor.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $users->currentPage(),
                'from'              => 1,
                'last_page'         => $users->lastPage(),
                'next_page_url'     => $users->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $users->perPage(),
                'prev_page_url'     => $users->previousPageUrl(),
                'total'             => $users->total()
            ]
        ]);
    }

    public function show($userId)
    {
        // Initiailze
        $user = User::with('courses','company')
                ->where('id', $userId)
                // ->where('is_instructor', 1)
                ->where('company_id', '!=', null)
                ->first();

        if ($user) {
            // Initialize
            $response = [
                'status'    => 'success',
                'message'   => 'Berhasil mendapatkan data.',
                'data'      => $user
            ];
        } else {
            // Initialize
            $response = [
                'status'    => 'error',
                'message'   => 'Data toko tidak ditemukan.',
            ];
        }

        return response()->json($response);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
