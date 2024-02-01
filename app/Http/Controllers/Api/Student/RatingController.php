<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RatingRequest;
use App\Rating;
use App\CheckoutDetail;
use App\UserCourse;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RatingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $courseId)
    {
        // Initialize
        $ratings = Rating::where(['course_id' => $courseId, 'is_take_down' => 0])->get();
        $data    = [];

        foreach($ratings as $val) {
            // Initialize
            $row['id']          = $val->id;
            $row['course_id']   = $val->course_id;
            $row['user']        = $val->user;
            $row['rating']      = $val->rating;
            $row['description'] = $val->description;
            $row['created_at']  = $val->created_at;
            $row['updated_at']  = $val->updated_at;

            $data[] = $row;
        }

        // Initialize
        $ratings = $this->paginate($ratings, 20, null, ['path' => $request->fullUrl()]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data rating.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $ratings->currentPage(),
                'from'              => 1,
                'last_page'         => $ratings->lastPage(),
                'next_page_url'     => $ratings->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $ratings->perPage(),
                'prev_page_url'     => $ratings->previousPageUrl(),
                'total'             => $ratings->total()
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RatingRequest $request)
    {
        // Check Rating By Product Buy
        $history = CheckoutDetail::where([
            'user_id'   => auth()->user()->id,
            'course_id' => request('course_id')
        ])->first();

        if (!$history) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        if ($history->status_delivery != 3) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Terima paket terlebih dahulu, sebelum memberikan rating.'
            ]);
        }

        // Check Exists Rating
        $ratingExists = Rating::where(['user_id' => auth()->user()->id, 'course_id' => request('course_id')])->first();

        if ($ratingExists) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah memberikan Rating',
                'data'      => [
                    'error_code' => ''
                ]
            ]);
        }

        $rating = Rating::create([
            'user_id'       => auth()->user()->id,
            'course_id'     => request('course_id'),
            'rating'        => request('rating'),
            'description'   => request('description')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data Rating.',
            'data'      => $rating
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
        // Initialize
        $rating = Rating::where(['user_id' => auth()->user()->id, 'course_id' => $id])->first();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data',
            'data'      => $rating
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        // Check Exists Rating
        $rating = Rating::where('id', $id)->first();

        if (!$rating) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Rating tidak ditemukan',
                'data'      => [
                    'error_code' => ''
                ]
            ]);
        }

        $rating->update([
            'rating'      => request('rating'),
            'description' => request('description')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data Rating.',
            'data'      => $rating
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
        $rating = Rating::where('id', $id)->first();

        if ($rating) {
            if ($rating->user_id == auth()->user()->id) {
                $rating->delete();

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Berhasil menghapus data Rating.',
                    'data'      => [
                        'id'        => $id,
                        'delete_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            }

            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses!',
                'data'      => [
                    'error_code' => 'not_accessible'
                ]
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Rating tidak ditemukan',
            'data'      => [
                'error_code' => ''
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
