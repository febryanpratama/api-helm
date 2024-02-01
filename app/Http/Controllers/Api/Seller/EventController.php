<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Event;
use App\MediaEvents;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class EventController extends Controller
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
            $event = Event::where('user_id', auth()->user()->id)
                        ->orderBy('id', 'desc')
                        ->get();
        } else {
            $event = Event::orderBy('id', 'desc')
                        ->where('is_publish', 1)
                        ->where('status', 0)
                        ->get();
        }

        // Custom Paginate
        $events = $this->paginate($event, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($events as $val) {
            // Initialize
            $row['id']                              = $val->id;
            $row['user_id']                         = $val->user_id;
            $row['name']                            = $val->name;
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
            $row['type']                            = $val->type;
            $row['rundown_event']                   = $val->rundown_event;
            $row['is_free']                         = $val->is_free;
            $row['visitor_registration']            = $val->visitor_registration;
            $row['event_date']                      = $val->event_date;
            $row['bids']                            = $val->bids;
            $row['created_at']                      = $val->created_at;
            $row['updated_at']                      = $val->created_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Event.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $events->currentPage(),
                'from'              => 1,
                'last_page'         => $events->lastPage(),
                'next_page_url'     => $events->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $events->perPage(),
                'prev_page_url'     => $events->previousPageUrl(),
                'total'             => $events->total()
            ]
        ]);
    }

    // TODO: with list transaction
    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $data['id']                              = $event->id;
        $data['user_id']                         = $event->user_id;
        $data['name']                            = $event->name;
        $data['is_service']                      = $event->is_service;
        $data['type']                            = $event->is_service == 1 ? 'Jasa' : 'Barang';
        $data['category_id']                     = $event->category_id;
        $data['product_detail_name']             = $event->product_detail_name;
        $data['product_detail_description']      = $event->product_detail_description;
        $data['question_category']               = $event->question_category;
        $data['unit_id']                         = $event->unit_id;
        $data['weight']                          = $event->weight;
        $data['address_id']                      = $event->address_id;
        $data['budget']                          = $event->budget;
        $data['dimension']                       = $event->dimension;
        $data['description']                     = $event->description;
        $data['start_date_time']                 = $event->start_date_time;
        $data['end_date_time']                   = $event->end_date_time;
        $data['payment_type']                    = $event->payment_type;
        $data['bank_name']                       = $event->bank_name;
        $data['no_rek']                          = $event->no_rek;
        $data['code_courier']                    = $event->code_courier;
        $data['is_armada']                       = $event->is_armada;
        $data['status']                          = $event->status;
        $data['status_description']              = $event->status == 1 ? 'Approve' : 'Pending';
        $data['is_publish']                      = $event->is_publish;
        $data['type']                            = $event->type;
        $data['rundown_event']                   = $event->rundown_event;
        $data['is_free']                         = $event->is_free;
        $data['visitor_registration']            = $event->visitor_registration;
        $data['event_date']                      = $event->event_date;
        $data['bids']                            = $event->bids;
        $data['media_events']                  = $event->mediaEvents;
        $data['created_at']                      = $event->created_at;
        $data['updated_at']                      = $event->created_at;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Event.',
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

        $event = Event::create([
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

            'event_date'                => date('Y-m-d H:i:s', strtotime($request->event_date)),
            'type'                      => $request->type ? $request->type : 1,
            'rundown_event'             => $request->rundown_event,
            'is_free'                   => $request->is_free ? $request->is_free : 0,
            'visitor_registration'      => $request->visitor_registration,
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
                $path = $fileDetail->store('uploads/events', 'public');
                
                MediaEvents::create([
                    'event_id'    => $event->id,
                    'path'          => env('SITE_URL').'/storage/'.$path,
                    'caption'       => (isset(request('caption')[$key])) ? request('caption')[$key] : null,
                    'file_type'     => $extFile
                ]);
            }
        }

        $data['user_id']                    = auth()->user()->id;
        $data['name']                       = $event->name;
        $data['is_service']                 = $event->is_service;
        $data['category_id']                = $event->category_id;
        $data['product_detail_name']        = $event->product_detail_name;
        $data['product_detail_description'] = $event->product_detail_description;
        $data['question_category']          = $event->question_category;
        $data['unit_id']                    = $event->unit_id;
        $data['weight']                     = $event->weight;
        $data['address_id']                 = $event->address_id;
        $data['budget']                     = $event->budget;
        $data['dimension']                  = $event->dimension;
        $data['description']                = $event->description;
        $data['start_date_time']            = $event->start_date_time;
        $data['end_date_time']              = $event->end_date_time;
        $data['bank_name']                  = $event->bank_name;
        $data['no_rek']                     = $event->no_rek;
        $data['code_courier']               = $event->code_courier;
        $data['is_armada']                  = $event->is_armada;
        $data['is_publish']                 = $event->is_publish;
        $data['media_events']             = $event->mediaEvents;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);
    }

    // TODO: CHECK JIKA SUDAH ADA YG BID
    public function update($id, Request $request)
    {
        $event = Event::find($id);
        if (!$event) {
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

        $event->update([
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

            'event_date'                => date('Y-m-d H:i:s', strtotime($request->event_date)),
            'type'                      => $request->type ? $request->type : 1,
            'rundown_event'             => $request->rundown_event,
            'is_free'                   => $request->is_free ? $request->is_free : 0,
            'visitor_registration'      => $request->visitor_registration,
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
                $path = $fileDetail->store('uploads/events', 'public');
                
                MediaEvents::create([
                    'event_id'    => $event->id,
                    'path'          => env('SITE_URL').'/storage/'.$path,
                    'caption'       => (isset(request('caption')[$key])) ? request('caption')[$key] : null,
                    'file_type'     => $extFile
                ]);
            }
        }

        $data['user_id']                    = auth()->user()->id;
        $data['name']                       = $event->name;
        $data['is_service']                 = $event->is_service;
        $data['category_id']                = $event->category_id;
        $data['product_detail_name']        = $event->product_detail_name;
        $data['product_detail_description'] = $event->product_detail_description;
        $data['question_category']          = $event->question_category;
        $data['unit_id']                    = $event->unit_id;
        $data['weight']                     = $event->weight;
        $data['address_id']                 = $event->address_id;
        $data['budget']                     = $event->budget;
        $data['dimension']                  = $event->dimension;
        $data['description']                = $event->description;
        $data['start_date_time']            = $event->start_date_time;
        $data['end_date_time']              = $event->end_date_time;
        $data['bank_name']                  = $event->bank_name;
        $data['no_rek']                     = $event->no_rek;
        $data['code_courier']               = $event->code_courier;
        $data['is_armada']                  = $event->is_armada;
        $data['is_publish']                 = $event->is_publish;
        $data['media_events']             = $event->mediaEvents;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $data,
        ]);
    }

    public function questionEvent($id)
    {
        $req = request()->all();

        $event = Event::find($id);
        if (!$event) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        $question['question_details_transaction'] = $req['question'];

        $event->update([
            'question_category' => $question
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $event,
        ]);
    }

    // TODO: CHECK JIKA SUDAH ADA YG BID
    public function delete($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Check Media
        if ($event->mediaEvents) {
            foreach($event->mediaEvents as $val) {
                // Delete Files
                $explodePath = explode('/', $val->path);

                @unlink('storage/uploads/events/'.$explodePath[6]);

                // Delete Data
                $val->delete();
            }
        }

        $event->delete();

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
        $mediaEvents = MediaEvents::find($id);

        if (!$mediaEvents) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Initialize
        $path    = $mediaEvents->path;
        $extFile = $mediaEvents->file_type;

        if (request('file')) {
            // Delete Files
            $explodePath = explode('/', $mediaEvents->path);
            @unlink('storage/uploads/events/'.$explodePath[6]);

            // Upload New File
            $fileDetail = request()->file('file');
            $extFile    = $fileDetail->getClientOriginalExtension();
            $path       = env('SITE_URL').'/storage/'.$fileDetail->store('uploads/events', 'public');
        }

        $mediaEvents->update([
            'path'      => $path,
            'caption'   => (request('caption')) ? request('caption') : $mediaEvents->caption,
            'file_type' => $extFile
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil disimpan',
            'data'      => $mediaEvents
        ]);
    }

    public function deleteMedia($id)
    {
        // Initialize
        $mediaEvents = MediaEvents::find($id);

        if (!$mediaEvents) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan',
            ]);
        }

        // Delete Files
        $explodePath = explode('/', $mediaEvents->path);

        @unlink('storage/uploads/events/'.$explodePath[6]);

        // Delete Data
        $mediaEvents->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
