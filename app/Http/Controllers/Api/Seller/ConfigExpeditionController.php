<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ConfigExpedition;
use DB;

class ConfigExpeditionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $configExpedition = ConfigExpedition::where('store_id', auth()->user()->company_id)->first();

        if (!$configExpedition) {
            $configExpedition = $this->_expedition();
        }

        // Initialize
        $row['id']         = $configExpedition->id;
        $row['store_id']   = $configExpedition->store_id;
        $row['expedition'] = json_decode($configExpedition->expedition, true);
        
        $configExpedition = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $configExpedition
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Initialize
        $requestData = request()->all();

        foreach($requestData['expedition'] as $val) {
            $row['name']    = $val['name'];
            $row['code']    = $val['code'];
            $row['status']  = $val['status'];

            $data[] = $row;
        }
        
        // Initialize
        $configExpedition = ConfigExpedition::where('store_id', auth()->user()->company_id)->first();

        if ($configExpedition) {
            $configExpedition->update([
                'store_id'   => auth()->user()->company_id,
                'expedition' => json_encode($data)
            ]);
        } else {
            $configExpedition = $this->_expedition();
        }

        // Initialize
        $row['id']         = $configExpedition->id;
        $row['store_id']   = $configExpedition->store_id;
        $row['expedition'] = json_decode($configExpedition->expedition, true);
        
        $configExpedition = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $configExpedition
        ]);
    }

    private function _expedition()
    {
        // Get Master Expedition
        $masterExpedition = DB::table('master_courier_expeditions')->orderBy('name', 'ASC')->get();
        $expedition       = [];

        foreach ($masterExpedition as $val) {
            $row['id']      = $val->id;
            $row['name']    = $val->name;
            $row['code']    = $val->code;
            $row['status']  = 'disabled';

            $expedition[] = $row;
        }

        $configExpedition = ConfigExpedition::create([
            'store_id'      => auth()->user()->company_id,
            'expedition'    => json_encode($expedition)
        ]);

        return $configExpedition;
    }
}
