<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Fleet;
use App\MasterLocation;
use App\Company;
use Validator;

class FleetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $fleets = Fleet::where('company_id', auth()->user()->company_id)->latest()->get();
        $data   = [];

        foreach ($fleets as $val) {
            $row['id']              = $val->id;
            $row['origin']          = MasterLocation::where('id', $val->origin)->first();
            $row['destination']     = MasterLocation::where('id', $val->destination)->first();
            $row['shipping_cost']   = $val->shipping_cost;
            $row['count_per_gram']  = $val->count_per_gram;
            $row['etd']             = $val->etd;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
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
        $validated = Validator::make(request()->all(), [
            'origin'            => 'required',
            'destination'       => 'required',
            'shipping_cost'     => 'required',
            'count_per_gram'    => 'required',
            'etd'               => 'required'
        ]);

        if ($validated->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validated->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $data = Fleet::create([
            'company_id'        => auth()->user()->company_id,
            'origin'            => request('origin'),
            'destination'       => request('destination'),
            'shipping_cost'     => request('shipping_cost'),
            'count_per_gram'    => request('count_per_gram'),
            'etd'               => request('etd')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $data
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
        $fleet = Fleet::where('id', $id)->first();
        $data  = [];

        if ($fleet) {
            $row['id']              = $fleet->id;
            $row['origin']          = MasterLocation::where('id', $fleet->origin)->first();
            $row['destination']     = MasterLocation::where('id', $fleet->destination)->first();
            $row['shipping_cost']   = $fleet->shipping_cost;
            $row['count_per_gram']  = $fleet->count_per_gram;
            $row['etd']             = $fleet->etd;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
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
        // Validation
        $validated = Validator::make(request()->all(), [
            'origin'            => 'required',
            'destination'       => 'required',
            'shipping_cost'     => 'required',
            'count_per_gram'    => 'required',
            'etd'               => 'required'
        ]);

        if ($validated->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validated->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $fleet = Fleet::where('id', $id)->first();

        $fleet->update([
            'origin'            => request('origin'),
            'destination'       => request('destination'),
            'shipping_cost'     => request('shipping_cost'),
            'count_per_gram'    => request('count_per_gram'),
            'etd'               => request('etd')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $fleet
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
        $fleet = Fleet::where('id', $id)->first();

        if ($fleet) {
            $fleet->delete();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $id
            ]
        ]);
    }

    public function checkShipping()
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'store_id'      => 'required',
            'destination'   => 'required',
            'weight'        => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check Store
        $store = Company::where('ID', request('store_id'))->first();

        if (!$store) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Toko dengan ID ('.request('store_id').') tidak ditemukan.'
            ]);
        }

        // Initialize
        $fleet = Fleet::where([
                    'company_id'  => request('store_id'),
                    'destination' => request('destination')
                ])
                ->get();

        $data = [];

        foreach ($fleet as $val) {
            // Initialize Formula
            $firstPrice   = $val->shipping_cost; // Ex : 17.000
            $weight       = $val->count_per_gram; // Ex : 200 (Gram)
            $shippingCost = (request('weight') / $val->count_per_gram);

            $row['name']                = 'Armada Toko';
            $row['origin']              = MasterLocation::where('id', $val->origin)->first();
            $row['destination']         = MasterLocation::where('id', $val->destination)->first();
            $row['shipping_cost']       = $val->shipping_cost;
            $row['count_per_gram']      = $val->count_per_gram;
            // $row['shipping_cost_total'] = ($val->shipping_cost * $shippingCost);
            $row['etd']                 = $val->etd;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }
}
