<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\ConfigExpedition;

class ExpeditionControler extends Controller
{
    public function index()
    {
        if (request('store_id')) {
            // Initialize
            $configExpedition = ConfigExpedition::where('store_id', request('store_id'))->first();

            if ($configExpedition) {
                // Initialize
                $code = [];

                foreach (json_decode($configExpedition->expedition, true) as $val) {
                    if ($val['status'] == 'enabled') {
                        array_push($code, $val['code']);
                    }
                }

                // Initialize
                $data = DB::table('master_courier_expeditions')->whereIn('code', $code)->orderBy('name', 'ASC')->get();
            } else {
                // Initialize
                $data = DB::table('master_courier_expeditions')->orderBy('name', 'ASC')->get();    
            }
        } else {
            // Initialize
            $data = DB::table('master_courier_expeditions')->orderBy('name', 'ASC')->get();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }
}
