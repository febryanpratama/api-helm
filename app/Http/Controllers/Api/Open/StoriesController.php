<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Story;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StoriesController extends Controller
{
    public function index(Request $request)
    {
        // Initialize
        $story = Story::whereHas('storyMedias')
                ->orderBy('ID', 'desc')
                ->get();
        if ($request->is_popular) {
            $story = Story::whereHas('popular')->whereHas('storyMedias')
                ->orderBy('ID', 'desc')
                ->get();
        }

        // Custom Paginate
        $storys = $this->paginate($story, 20, null, ['path' => $request->fullUrl()]);
        $data   = [];

        foreach ($storys as $val) {
            $row['id']          = $val->ID;
            $row['user_id']     = $val->IDUser;
            $row['user_name']   = $val->user->name;
            $row['caption']     = $val->caption;
            $row['hastag']      = $val->hastag;
            $row['is_popular']  = $val->is_popular;
            $row['media']       = $val->storyMedias;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Toko.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $storys->currentPage(),
                'from'              => 1,
                'last_page'         => $storys->lastPage(),
                'next_page_url'     => $storys->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $storys->perPage(),
                'prev_page_url'     => $storys->previousPageUrl(),
                'total'             => $storys->total()
            ]
        ]);
    }

    public function show(Story $story, Request $request)
    {
        // Initialize
        $row['id']          = $story->ID;
        $row['user_id']     = $story->IDUser;
        $row['user_name']   = $story->user->name;
        $row['caption']     = $story->caption;
        $row['hastag']      = $story->hastag;
        $row['is_popular']  = $story->is_popular;
        $row['media']       = $story->storyMedias;

        $data = $row;

        $response = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Detail story',
            'data'      => $data
        ];

        return response()->json($response, 200);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
