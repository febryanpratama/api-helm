<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CheckShippingController extends Controller
{
    public function index(Request $request)
    {
        // Set validation
        $validator = Validator::make(request()->all(), [
            "origin"            => "required|integer",
            "destination"       => "required|integer",
            "weight"            => "required|numeric",
            "courier"           => "required|string",
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => false,
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $origin             = $request->origin;
        $origin_type        = 'subdistrict';
        $destination        = $request->destination;
        $destination_type   = 'subdistrict';
        $weight             = $request->weight;
        $courier            = strtolower($request->courier);

        // Call Function
        $data = $this->_curlPOST($origin, $origin_type, $destination, $destination_type, $weight, $courier);

        if (!$data) {
            return response()->json($data, 400);
        }

        // Initialize
        $data = json_decode($data, true);

        if ($data['rajaongkir']['status']['code'] == 400) {
            return response()->json($data);
        }

        // Initialize
        $results  = $data['rajaongkir']['results'];
        $shipping = [];

        foreach($results as $val) {
            $row['code']  = $val['code'];
            $row['name']  = $val['name'];
            $row['costs'] = $val['costs'];

            $shipping[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Ongkir didapatkan',
            'data'      => $shipping
        ]);
    }

    private function _curlPOST($origin, $origin_type, $destination, $destination_type, $weight, $courier)
    {
        // Initialize
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://pro.rajaongkir.com/api/cost",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "origin=".$origin."&originType=".$origin_type."&destination=".$destination."&destinationType=".$destination_type."&weight=".$weight."&courier=".$courier,
        CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            "key: 6d92efcffa971adb9b93cb7d7a69b16d"
        ),
        ));

        $data = curl_exec($curl);
        $err  = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $data = [
                'rajaongkir'    => 'error',
                'status'        => false,
                'code'          => 400,
                'message'       => 'error',
                'result'        => $err
            ];

            return $data;
        }

        return $data;   
    }
}
