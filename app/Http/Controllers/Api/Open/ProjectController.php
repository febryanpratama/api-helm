<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Project;
use App\MediaProjects;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $project = Project::orderBy('id', 'desc')->where('is_publish', 1)->get();

        // Custom Paginate
        $projects = $this->paginate($project, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($projects as $val) {
            // Initialize
            $row['id']                         = $val->id;
            $row['user_id']                    = $val->user_id;
            $row['name']                       = $val->name;
            $row['is_service']                 = $val->is_service;
            $row['type']                       = $val->is_service == 1 ? 'Jasa' : 'Barang';
            $row['category_id']                = $val->category_id;
            $row['product_detail_name']        = $val->product_detail_name;
            $row['product_detail_description'] = $val->product_detail_description;
            $row['question_category']          = $val->question_category;
            $row['unit_id']                    = $val->unit_id;
            $row['weight']                     = $val->weight;
            $row['address_id']                 = $val->address_id;
            $row['budget']                     = $val->budget;
            $row['dimension']                  = $val->dimension;
            $row['description']                = $val->description;
            $row['start_date_time']            = $val->start_date_time;
            $row['end_date_time']              = $val->end_date_time;
            $row['payment_type']               = $val->payment_type;
            $row['bank_name']                  = $val->bank_name;
            $row['no_rek']                     = $val->no_rek;
            $row['code_courier']               = $val->code_courier;
            $row['is_armada']                  = $val->is_armada;
            $row['status']                     = $val->status;
            $row['status_description']         = $val->status == 1 ? 'Approve' : 'Pending';
            $row['is_publish']                 = $val->is_publish;
            $row['bids']                       = $val->bids;
            $row['created_at']                 = $val->created_at;
            $row['updated_at']                 = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Project.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $projects->currentPage(),
                'from'              => 1,
                'last_page'         => $projects->lastPage(),
                'next_page_url'     => $projects->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $projects->perPage(),
                'prev_page_url'     => $projects->previousPageUrl(),
                'total'             => $projects->total()
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
        // Initialize
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data['id']                         = $project->id;
        $data['user_id']                    = $project->user_id;
        $data['name']                       = $project->name;
        $data['is_service']                 = $project->is_service;
        $data['type']                       = $project->is_service == 1 ? 'Jasa' : 'Barang';
        $data['category_id']                = $project->category_id;
        $data['product_detail_name']        = $project->product_detail_name;
        $data['product_detail_description'] = $project->product_detail_description;
        $data['question_category']          = $project->question_category;
        $data['unit_id']                    = $project->unit_id;
        $data['weight']                     = $project->weight;
        $data['address_id']                 = $project->address_id;
        $data['budget']                     = $project->budget;
        $data['dimension']                  = $project->dimension;
        $data['description']                = $project->description;
        $data['start_date_time']            = $project->start_date_time;
        $data['end_date_time']              = $project->end_date_time;
        $data['payment_type']               = $project->payment_type;
        $data['bank_name']                  = $project->bank_name;
        $data['no_rek']                     = $project->no_rek;
        $data['code_courier']               = $project->code_courier;
        $data['is_armada']                  = $project->is_armada;
        $data['status']                     = $project->status;
        $data['status_description']         = $project->status == 1 ? 'Approve' : 'Pending';
        $data['is_publish']                 = $project->is_publish;
        $data['bids']                       = $project->bids;
        $data['media_projects']             = $project->mediaProjects;
        $data['created_at']                 = $project->created_at;
        $data['updated_at']                 = $project->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Project.',
            'data'      => $data
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
