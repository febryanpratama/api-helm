<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Project;
use App\MediaProjects;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class ProjectController extends Controller
{
    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }


    // TODO: with list transaction
    public function index(Request $request)
    {
        // Initialize
        if (auth()->user()->role_id == 1) {
            $project = Project::where('user_id', auth()->user()->id)
                        ->orderBy('id', 'desc')
                        ->get();
        } else {
            $project = Project::orderBy('id', 'desc')
                        ->where('is_publish', 1)
                        ->where('status', 0)
                        ->get();
        }

        // Custom Paginate
        $projects = $this->paginate($project, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($projects as $val) {
            // Initialize
            $row['id']                              = $val->id;
            $row['user_id']                         = $val->user_id;
            $row['name']                            = $val->name;
            $row['type_need']                       = $val->type_need;
            $row['is_service']                      = $val->is_service;
            $row['type']                            = $val->is_service == 1 ? 'Jasa' : 'Barang';
            $row['category_id']                     = $val->category_id;
            $row['product_detail_name']             = $val->product_detail_name;
            $row['product_detail_description']      = $val->product_detail_description;
            $row['question_category']               = $val->question_category;
            $row['unit_id']                         = $val->unit_id;
            $row['weight']                          = $val->weight;
            $row['address_id']                      = $val->address_id;
            $row['budget']                          = $val->budget;
            $row['dimension']                       = $val->dimension;
            $row['description']                     = $val->description;
            $row['start_date_time']                 = $val->start_date_time;
            $row['end_date_time']                   = $val->end_date_time;
            $row['payment_type']                    = $val->payment_type;
            $row['bank_name']                       = $val->bank_name;
            $row['no_rek']                          = $val->no_rek;
            $row['code_courier']                    = $val->code_courier;
            $row['is_armada']                       = $val->is_armada;
            $row['status']                          = $val->status;
            $row['status_description']              = $val->status == 1 ? 'Approve' : 'Pending';
            $row['is_publish']                      = $val->is_publish;
            $row['bids']                            = $val->bids;
            $row['created_at']                      = $val->created_at;
            $row['updated_at']                      = $val->created_at;

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

    // TODO: with list transaction
    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data['id']                              = $project->id;
        $data['user_id']                         = $project->user_id;
        $data['name']                            = $project->name;
        $data['type_need']                       = $project->type_need;
        $data['is_service']                      = $project->is_service;
        $data['type']                            = $project->is_service == 1 ? 'Jasa' : 'Barang';
        $data['category_id']                     = $project->category_id;
        $data['product_detail_name']             = $project->product_detail_name;
        $data['product_detail_description']      = $project->product_detail_description;
        $data['question_category']               = $project->question_category;
        $data['unit_id']                         = $project->unit_id;
        $data['weight']                          = $project->weight;
        $data['address_id']                      = $project->address_id;
        $data['budget']                          = $project->budget;
        $data['dimension']                       = $project->dimension;
        $data['description']                     = $project->description;
        $data['start_date_time']                 = $project->start_date_time;
        $data['end_date_time']                   = $project->end_date_time;
        $data['payment_type']                    = $project->payment_type;
        $data['bank_name']                       = $project->bank_name;
        $data['no_rek']                          = $project->no_rek;
        $data['code_courier']                    = $project->code_courier;
        $data['is_armada']                       = $project->is_armada;
        $data['status']                          = $project->status;
        $data['status_description']              = $project->status == 1 ? 'Approve' : 'Pending';
        $data['is_publish']                      = $project->is_publish;
        $data['bids']                            = $project->bids;
        $data['media_projects']                  = $project->mediaProjects;
        $data['created_at']                      = $project->created_at;
        $data['updated_at']                      = $project->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Project.',
            'data'      => $data,
        ]);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'name'                      => 'required|string',
            'is_service'                => 'required|in:0,1',
            'category_id'               => 'required|integer|exists:category,id',
            'product_detail_name'       => 'required|string',
            'product_detail_description'=> 'required|string',
            'question_category'         => 'nullable', // skip
            'unit_id'                   => 'required|integer|exists:unit,id',
            'weight'                    => 'nullable|numeric',
            'address_id'                => 'required|integer|exists:address,id',
            'budget'                    => 'required|numeric',
            'dimension'                 => 'nullable|string',
            'description'               => 'required|string',
            'start_date'                => 'required',
            'start_time'                => 'required',
            'end_date'                  => 'required',
            'end_time'                  => 'required',
            'payment_type'              => 'required|integer|in:1,2,3,4',
            'bank_name'                 => 'required|string',
            'no_rek'                    => 'required|string',
            // 'code_courier'              => 'nullable|string',
            // 'is_armada'                 => 'required|in:0,1',
            'is_publish'                => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $project = Project::create([
            'user_id'                   => auth()->user()->id,
            'name'                      => $request->name,
            'is_service'                => $request->is_service,
            'category_id'               => $request->category_id,
            'product_detail_name'       => $request->product_detail_name,
            'product_detail_description'=> $request->product_detail_description,
            'question_category'         => $request->question_category,
            'unit_id'                   => $request->unit_id,
            'weight'                    => $request->weight,
            'address_id'                => $request->address_id,
            'budget'                    => $request->budget,
            'dimension'                 => $request->dimension,
            'description'               => $request->description,
            'start_date_time'           => $request->start_date . ' ' . $request->start_time,
            'end_date_time'             => $request->end_date . ' ' . $request->end_time,
            'payment_type'              => $request->payment_type,
            'bank_name'                 => $request->bank_name,
            'no_rek'                    => $request->no_rek,
            'is_publish'                => $request->is_publish,
            'type_need'                 => $request->type_need,
            // 'code_courier'              => $request->code_courier,
            // 'is_armada'                 => $request->is_armada,
        ]);

        // Create Media
        $media = request('file');

        if ($media) {
            foreach ($media as $key => $val) {
                // Initialize
                $fileDetail = request()->file('file')[$key];
                $extFile    = $fileDetail->getClientOriginalExtension();

                // Upload File
                $path = $fileDetail->store('uploads/projects', 'public');
                
                MediaProjects::create([
                    'project_id'    => $project->id,
                    'path'          => env('SITE_URL').'/storage/'.$path,
                    'caption'       => (isset(request('caption')[$key])) ? request('caption')[$key] : null,
                    'file_type'     => $extFile
                ]);
            }
        }

        $data['user_id']                    = auth()->user()->id;
        $data['name']                       = $project->name;
        $data['is_service']                 = $project->is_service;
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
        $data['bank_name']                  = $project->bank_name;
        $data['no_rek']                     = $project->no_rek;
        $data['code_courier']               = $project->code_courier;
        $data['is_armada']                  = $project->is_armada;
        $data['is_publish']                 = $project->is_publish;
        $data['media_projects']             = $project->mediaProjects;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);
    }

    // TODO: CHECK JIKA SUDAH ADA YG BID
    public function update($id, Request $request)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }
        
        // Validation
        $validator = Validator::make(request()->all(), [
            'name'                      => 'required|string',
            'is_service'                => 'required|in:0,1',
            'category_id'               => 'required|integer|exists:category,id',
            'product_detail_name'       => 'required|string',
            'product_detail_description'=> 'required|string',
            'question_category'         => 'nullable', // skip
            'unit_id'                   => 'required|integer|exists:unit,id',
            'weight'                    => 'nullable|numeric',
            'address_id'                => 'required|integer|exists:address,id',
            'budget'                    => 'required|numeric',
            'dimension'                 => 'nullable|string',
            'description'               => 'required|string',
            'start_date'                => 'required',
            'start_time'                => 'required',
            'end_date'                  => 'required',
            'end_time'                  => 'required',
            'payment_type'              => 'required|integer|in:1,2,3,4',
            'bank_name'                 => 'required|string',
            'no_rek'                    => 'required|string',
            // 'code_courier'              => 'nullable|string',
            // 'is_armada'                 => 'required|in:0,1',
            'is_publish'                => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $project->update([
            'user_id'                   => auth()->user()->id,
            'name'                      => $request->name,
            'is_service'                => $request->is_service,
            'category_id'               => $request->category_id,
            'product_detail_name'       => $request->product_detail_name,
            'product_detail_description'=> $request->product_detail_description,
            'question_category'         => $request->question_category,
            'unit_id'                   => $request->unit_id,
            'weight'                    => $request->weight,
            'address_id'                => $request->address_id,
            'budget'                    => $request->budget,
            'dimension'                 => $request->dimension,
            'description'               => $request->description,
            'start_date_time'           => $request->start_date . ' ' . $request->start_time,
            'end_date_time'             => $request->end_date . ' ' . $request->end_time,
            'payment_type'              => $request->payment_type,
            'bank_name'                 => $request->bank_name,
            'no_rek'                    => $request->no_rek,
            'is_publish'                => $request->is_publish,
            'type_need'                 => $request->type_need,
            // 'code_courier'              => $request->code_courier,
            // 'is_armada'                 => $request->is_armada,
        ]);

        // Create Media
        $media = request('file');

        if ($media) {
            foreach ($media as $key => $val) {
                // Initialize
                $fileDetail = request()->file('file')[$key];
                $extFile    = $fileDetail->getClientOriginalExtension();

                // Upload File
                $path = $fileDetail->store('uploads/projects', 'public');
                
                MediaProjects::create([
                    'project_id'    => $project->id,
                    'path'          => env('SITE_URL').'/storage/'.$path,
                    'caption'       => (isset(request('caption')[$key])) ? request('caption')[$key] : null,
                    'file_type'     => $extFile
                ]);
            }
        }

        $data['user_id']                    = auth()->user()->id;
        $data['name']                       = $project->name;
        $data['is_service']                 = $project->is_service;
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
        $data['bank_name']                  = $project->bank_name;
        $data['no_rek']                     = $project->no_rek;
        $data['code_courier']               = $project->code_courier;
        $data['is_armada']                  = $project->is_armada;
        $data['is_publish']                 = $project->is_publish;
        $data['media_projects']             = $project->mediaProjects;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);
    }

    public function questionProject($id)
    {
        $req = request()->all();

        $project = Project::find($id);
        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $question['question_details_transaction'] = $req['question'];

        $project->update([
            'question_category' => $question
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $project,
        ]);
    }

    // TODO: CHECK JIKA SUDAH ADA YG BID
    public function delete($id)
    {
        $project = Project::find($id);
        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Check Media
        if ($project->mediaProjects) {
            foreach($project->mediaProjects as $val) {
                // Delete Files
                $explodePath = explode('/', $val->path);

                @unlink('storage/uploads/projects/'.$explodePath[6]);

                // Delete Data
                $val->delete();
            }
        }

        $project->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    // Media Projecs
    public function updateMedia($id)
    {
        // Initialize
        $mediaProjects = MediaProjects::find($id);

        if (!$mediaProjects) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Initialize
        $path    = $mediaProjects->path;
        $extFile = $mediaProjects->file_type;

        if (request('file')) {
            // Delete Files
            $explodePath = explode('/', $mediaProjects->path);
            @unlink('storage/uploads/projects/'.$explodePath[6]);

            // Upload New File
            $fileDetail = request()->file('file');
            $extFile    = $fileDetail->getClientOriginalExtension();
            $path       = env('SITE_URL').'/storage/'.$fileDetail->store('uploads/projects', 'public');
        }

        $mediaProjects->update([
            'path'      => $path,
            'caption'   => (request('caption')) ? request('caption') : $mediaProjects->caption,
            'file_type' => $extFile
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $mediaProjects
        ]);
    }

    public function deleteMedia($id)
    {
        // Initialize
        $mediaProjects = MediaProjects::find($id);

        if (!$mediaProjects) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Delete Files
        $explodePath = explode('/', $mediaProjects->path);

        @unlink('storage/uploads/projects/'.$explodePath[6]);

        // Delete Data
        $mediaProjects->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
