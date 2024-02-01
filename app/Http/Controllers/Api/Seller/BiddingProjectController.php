<?php

namespace App\Http\Controllers\Api\Seller;

use App\BiddingProject;
use App\BiddingProjectTermin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Project;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class BiddingProjectController extends Controller
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
        $bid = BiddingProject::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();

        // Custom Paginate
        $bid = $this->paginate($bid, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($bid as $val) {
            // Initialize
            $row['id']                              = $val->id;
            $row['user_id']                         = $val->user_id;
            $row['project_id']                      = $val->project_id;
            $row['price']                           = $val->price;
            $row['service_date']                    = date('d F Y', strtotime($val->service_date));
            $row['status']                          = $val->status;
            $row['status_details']                  = biddingStatus($val->status);
            $row['project']                         = $val->project_json;
            $row['termin']                          = $val->termin;
            $row['created_at']                      = date('d F Y', strtotime($val->created_at));
            $row['updated_at']                      = date('d F Y', strtotime($val->updated_at));

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Bid Project.',
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
            'project_id' => 'required|exists:projects,id',
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

        // Check Project
        $project = Project::where('id', request('project_id'))->first();

        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Projek dengan ID ('.request('project_id').') tidak ditemukan.'
            ]);
        }

        if ($project->status == 1) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Tidak bisa melakukan penawaran untuk Projek ('.$project->name.').'
            ]);
        }

        // check BID
        $check_bid = BiddingProject::where('user_id', auth()->user()->id)->where('project_id', $project->id)->whereIn('status', [0,1])->first(); // check bid dgn status bila pending/approve

        if ($check_bid) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda sudah melakukan bid untuk project ini.'
            ]);
        }

        if ($project->is_service == 1) {
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

        $bid = BiddingProject::create([
            'user_id'           => auth()->user()->id,
            'project_id'        => $project->id,
            'price'             => $request->price,
            'service_date'      => $request->service_date . ' ' . $request->service_time,
            'project_json'      => $project
        ]);
        
        if ($request->is_termin == 1) {
            // Initialize
            $NOP        = $request->instalment_title;
            $totalVal   = 0;
            $total      = $bid->price;

            // Create Course Termin
            $termin = BiddingProjectTermin::create([
                'bidding_project_id'            => $bid->id,
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
            'message'   => 'Berhasil melakukan bid Project.',
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
        $bid = BiddingProject::where('id', $id)->first();

        if (!$bid) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak ditemukan.'
            ]);
        }

        // Initialize
        $data['id']                              = $bid->id;
        $data['user_id']                         = $bid->user_id;
        $data['project_id']                      = $bid->project_id;
        $data['price']                           = $bid->price;
        $data['service_date']                    = date('d F Y', strtotime($bid->service_date));
        $data['status']                          = $bid->status;
        $data['project']                         = $bid->project_json;
        $data['termin']                          = $bid->termin;
        $data['created_at']                      = date('d F Y', strtotime($bid->created_at));
        $data['updated_at']                      = date('d F Y', strtotime($bid->updated_at));

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Bid Project.',
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
        $biddingProject = BiddingProject::where('id', $id)->first();

        if (!$biddingProject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data Bidding dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($biddingProject->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk menghapus data ini.'
            ]);
        }

        if ($biddingProject->status != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data tidak bisa di hapus. Hanya Bidding berstatus (Menunggu Persetujuan) yang bisa di hapus.'
            ]);
        }

        $biddingProject->update([
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
