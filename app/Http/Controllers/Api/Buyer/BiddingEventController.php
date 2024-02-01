<?php

namespace App\Http\Controllers\Api\Buyer;

use App\BiddingEvent;
use App\BiddingEventTermin;
use App\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class BiddingEventController extends Controller
{
    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $bid = BiddingEvent::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();

        // Custom Paginate
        $bid = $this->paginate($bid, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($bid as $val) {
            // Initialize
            $row['id']                              = $val->id;
            $row['user_id']                         = $val->user_id;
            $row['event_id']                      = $val->event_id;
            $row['price']                           = $val->price;
            $row['service_date']                    = date('d F Y', strtotime($val->service_date));
            $row['status']                          = $val->status;
            $row['status_details']                  = biddingStatus($val->status);
            $row['event']                         = $val->event_json;
            $row['termin']                          = $val->termin;
            $row['created_at']                      = date('d F Y', strtotime($val->created_at));
            $row['updated_at']                      = date('d F Y', strtotime($val->updated_at));

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Bid event.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $bid->currentPage(),
                'from'              => 1,
                'last_page'         => $bid->lastPage(),
                'next_page_url'     => $bid->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $bid->perPage(),
                'prev_page_url'     => $bid->previousPageUrl(),
                'total'             => $bid->total()
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
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'event_id' => 'required|exists:events,id',
            'price' => 'required|numeric',
            'is_termin' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check event
        $event = Event::where('id', request('event_id'))->first();

        if (!$event) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Event dengan ID ('.request('event_id').') tidak ditemukan.'
            ]);
        }

        if ($event->status == 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tidak bisa melakukan penawaran untuk Event ('.$event->name.').'
            ]);
        }

        // check BID
        $check_bid = BiddingEvent::where('user_id', auth()->user()->id)->where('event_id', $event->id)->whereIn('status', [0,1])->first(); // check bid dgn status bila pending/approve

        if ($check_bid) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan bid untuk event ini.'
            ]);
        }

        if ($event->is_service == 1) {
             // Validation
            $validator = Validator::make(request()->all(), [
                'service_date' => 'required',
                'service_time' => 'required',
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

        if ($request->is_termin == 1) {
            $validator = Validator::make(request()->all(), [
                'instalment_title'              => 'required|integer',
                'termin_persentage'             => 'required|array',
                'termin_persentage.*'           => 'required|numeric',
                'down_payment'                  => 'required|numeric|min:0',
                'completion_percentage'         => 'required|array',
                'completion_percentage.*'       => 'required|numeric|min:0|not_in:0',
                'completion_percentage_detail'  => 'required|array',
                'is_percentage'                 => 'required|in:0,1',
            ]);
    
            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];
    
                return response()->json($data, 400);
            }

            if ($request->is_percentage == 1) { // persentase
                $dp_percentage = array($request->down_payment);
            
                $percentage = array_merge($request->termin_persentage, $dp_percentage);
            
                if (array_sum($percentage) < 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($percentage) > 100) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah persentasi termin & dp harus 100% total yang anda input adalah ' . array_sum($percentage) . '%',
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }
            
            if ($request->is_percentage == 0) { // value/nominal
                $price      = $request->price;
                $priceNum   = str_replace('.', '', $price);
                $dp = array($request->down_payment);
            
                $total = array_merge($request->termin_persentage, $dp);
            
                if (array_sum($total) < $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            
                if (array_sum($total) > $priceNum) {
                    $data = [
                        'status'    => 'error',
                        'message'   => 'Jumlah Total termin & dp harus ' . $price . ' total yang anda input adalah ' . rupiah(array_sum($total)),
                        'code'      => 400
                    ];
            
                    return response()->json($data, 400);
                }
            }

        }

        $bid = BiddingEvent::create([
            'user_id'           => auth()->user()->id,
            'event_id'        => $event->id,
            'price'             => $request->price,
            'service_date'      => $request->service_date . ' ' . $request->service_time,
            'event_json'      => $event
        ]);
        
        if ($request->is_termin == 1) {
            // Initialize
            $NOP        = $request->instalment_title;
            $totalVal   = 0;
            $total      = $bid->price;

            // Create Course Termin
            $termin = BiddingEventTermin::create([
                'bidding_event_id'            => $bid->id,
                'instalment_title'              => $request->instalment_title,
                'down_payment'                  => $request->down_payment, // percentage
                'number_of_payment'             => $NOP,
                'value'                         => $request->termin_persentage,
                'completion_percentage'         => $request->completion_percentage,
                'completion_percentage_detail'  => $request->completion_percentage_detail,
                'installment_amount'            => $total,
                'is_percentage'                 => $request->is_percentage,
            ]);
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil melakukan bid event.',
            'data'      => $bid,
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
        // Check bid
        $bid = BiddingEvent::where('id', $id)->first();

        if (!$bid) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        // Initialize
        $data['id']                              = $bid->id;
        $data['user_id']                         = $bid->user_id;
        $data['event_id']                      = $bid->event_id;
        $data['price']                           = $bid->price;
        $data['service_date']                    = date('d F Y', strtotime($bid->service_date));
        $data['status']                          = $bid->status;
        $data['event']                         = $bid->event_json;
        $data['termin']                          = $bid->termin;
        $data['created_at']                      = date('d F Y', strtotime($bid->created_at));
        $data['updated_at']                      = date('d F Y', strtotime($bid->updated_at));

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Bid event.',
            'data'      => $data,
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
        //
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
        $biddingEvent = BiddingEvent::where('id', $id)->first();

        if (!$biddingEvent) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data Bidding dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($biddingEvent->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk menghapus data ini.'
            ]);
        }

        if ($biddingEvent->status != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak bisa di hapus. Hanya Bidding berstatus (Menunggu Persetujuan) yang bisa di hapus.'
            ]);
        }

        $biddingEvent->update([
            'status' => 3
        ]);

        return response()->json([
            'status'    => 'status',
            'message'   => 'Bidding Berhasil dibatalkan.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
