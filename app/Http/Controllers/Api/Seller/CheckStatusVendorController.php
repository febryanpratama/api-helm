<?php

namespace App\Http\Controllers\Api\Seller;

use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class CheckStatusVendorController extends Controller
{
    public function checkPlatinumExist(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'city_id'   => 'required|exists:master_kota,id'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];
            return response()->json($data, 400);
        }

        // check if platinum exists
        $company = Company::where('city_id', $request->city_id)->where('status', 1)->first();

        if ($company) {
            $data = [
                'status'    => 'error',
                'message'   => 'Platinum sudah ada, silahkan daftar premium',
                'code'      => 400
            ];
    
            return response()->json($data, 400);
        }

        $data = [
            'status'    => 'success',
            'message'   => 'Platinum Tersedia',
            'code'      => 200
        ];

        return response()->json($data, 200);


    }
}
