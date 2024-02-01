<?php

namespace App\Http\Controllers\Api;

use App\Checkout;
use App\FleetTrackingLog;
use App\Http\Controllers\Controller;
use App\Transaction;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;
use Validator;

class TrackingController extends Controller
{
    public function tracking(Transaction $transaction)
    {

        if ($transaction->receipt) { // resi
            $client = new \GuzzleHttp\Client;
    
            $url = "https://pro.rajaongkir.com/api/waybill";

            $code_expedition = \DB::table('master_courier_expeditions')->where('name', $transaction->expedition)->first();
            $params['form_params'] = array('key' => env('RAJAONGKIR_KEY'), 'waybill' => $transaction->receipt, 'courier' => strtolower($code_expedition->code));

            try {

                $response = $client->post($url, $params);
            
            } catch (RequestException $e) {

                
                // To catch exactly error 400 use 
                if ($e->hasResponse()){


                    $data = [
                        'status' => 'error',
                        'code' => $e->getResponse()->getStatusCode(),
                        'message' => 'Resi salah atau belum terdaftar.',
                        'data' => null
                    ];
                    return response()->json($data, $e->getResponse()->getStatusCode());
                }
            
                
            }
    
            $response = json_decode($response->getBody(), true);


            $result['rajaongkir'] = $response['rajaongkir']['result'];
            $result['fleet'] = null;

            if ($response['rajaongkir']['status']['code'] == 200) {

                // auto sampai tujuan saat cek tracking (auto update status received)
                if ($response['rajaongkir']['result']['delivered'] == true && $transaction->status == 3) {
                    $transaction->update([
                        'status' => 4,
                        'time_received' => date('Y-m-d H:i:s')
                    ]);
                }

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Resi ditemukan',
                    'data' => $result
                ];
                return response()->json($data, 200);
            }

            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Resi tidak ditemukan',
                'data' => $result
            ];
            return response()->json($data, 400);
        } else { // armada

            if (count($transaction->fleetPosition) > 0) {
            
                $result['rajaongkir'] = null;
                $result['fleet'] = $transaction->fleetPosition;
                $data = [
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'tracking ditemukan',
                    'data'      => $result
                ];
        
                return response()->json($data, 200);
            }

            $data = [
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'tracking tidak ditemukan atau belum ada data',
            ];
    
            return response()->json($data, 404);
        }

        $data = [
            'status' => false,
            'code' => 400,
            'message' => 'Resi tidak ditemukan',
            'result' => null
        ];
        return response()->json($data, 400);
    }


    public function positionReportFleet(Transaction $transaction, Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'photo'         => 'required|mimes:jpeg,png,jpg|max:2048',
            'location'      => 'required|string',
            // 'date'          => 'required|date_format:Y-m-d',
            // 'time'          => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->expedition) {
            $data = [
                'status'    => 'error',
                'message'   => 'Gagal, Transaksi ini menggunakan kurir ekspedisi',
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }


        $track_log = FleetTrackingLog::create([
            'transaction_id'    => $transaction->id,
            'photo'             => $request->file('photo')->store('uploads/transaction/tracking/'.$transaction->id.'/', 'public'),
            'location'          => $request->location,
            // 'datetime'          => date('Y-m-d', strtotime($request->date)) . ' ' . date('H:i:s', strtotime($request->time))
        ]);


        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Upload lokasi berhasil'
        ];

        return response()->json($data, 200);
    }

    public function listPositionReportFleet(Transaction $transaction, Request $request)
    {

        if ($transaction->store_id != auth()->user()->company_id) {
            $data = [
                'status'    => 'error',
                'message'   => 'Anda tidak bisa mengakses data ini',
                'code'      => 403
            ];
            return response()->json($data, 403);
        }


        if (count($transaction->fleetPosition) > 0) {
            
            $data = [
                'status'    => 'success',
                'code'      => 200,
                'message'   => 'List lokasi',
                'data'      => $transaction->fleetPosition
            ];
    
            return response()->json($data, 200);
        }

        $data = [
            'status'    => 'error',
            'code'      => 404,
            'message'   => 'data tidak ada',
        ];

        return response()->json($data, 404);
    }
}
