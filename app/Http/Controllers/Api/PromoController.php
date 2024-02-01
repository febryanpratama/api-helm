<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LandingPromo;
use Illuminate\Http\Request;
use Validator;

class PromoController extends Controller
{
    // PROMO
    public function promo(Request $request)
    {
        $promo = LandingPromo::latest()->paginate(10);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'promo web',
            'result' => $promo
        ], 200);
    }

    public function myPromo(Request $request)
    {
        $promo = LandingPromo::latest()->paginate(10);

        if (auth()->user()->role_id == 10) {
            # code...
            $promo = LandingPromo::whereNull('company_id')->latest()->paginate(10);
        }

        if (auth()->user()->role_id == 1) {
            $promo = LandingPromo::where('company_id', auth()->user()->company_id)->latest()->paginate(10);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'promo web',
            'result' => $promo
        ], 200);
    }

    public function promoStore(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'type' => 'required|in:1,2,3,4,5,6,7,8,9', // 1 = discount, 2 = voucher/coupon, 3 = flashsale, 4 = free/gift, 5 = cashback, 6 = challenge, 7 = loyalty, 8 = referral, 9 = donation
            'file' => 'required|mimes:jpg,jpeg,png,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($request->type == 2) { // voucher
            //set validation
            $validator = Validator::make(request()->all(), [
                'category_id' => 'required|exists:category,id'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 1 || $request->type == 4) { // discount
            //set validation
            $validator = Validator::make(request()->all(), [
                'product_id' => 'required|exists:course,id'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 5) { // cashback
            //set validation
            $validator = Validator::make(request()->all(), [
                'cashback' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 6) { // challenge
            //set validation
            $validator = Validator::make(request()->all(), [
                'challenge_name' => 'required',
                'point' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 7 || $request->type == 8) { // loyalty & referral
            //set validation
            $validator = Validator::make(request()->all(), [
                'point' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        if ($request->type == 9) { // donation
            //set validation
            $validator = Validator::make(request()->all(), [
                'donation_purpose' => 'required',
                'donation_value' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                    'code' => 400
                ];
                return response()->json($data, 400);
            }
        }

        $file_path = null;

        if ($request->file('file')) {
            $file_path = $request->file('file')->store('uploads/promo', 'public');
            $file_path = env('SITE_URL') . '/storage/' . $file_path;
        }

        $promo = LandingPromo::create([
            'company_id' => auth()->user()->role_id == 1 ? auth()->user()->company_id : null,
            'file' => $file_path,
            'type' => $request->type,
            'category_id' => $request->category_id,
            'product_id' => $request->product_id,
            'start_period' => date('Y-m-d H:i:s', strtotime($request->start_period)),
            'end_period' => date('Y-m-d H:i:s', strtotime($request->end_period)),
            'percentage' => $request->percentage,
            'coupon_code' => $request->coupon_code,
            'cashback' => $request->cashback,
            'challenge_name' => $request->challenge_name,
            'point' => $request->point,
            'donation_value' => $request->donation_value,
            'donation_purpose' => $request->donation_purpose,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'promo web berhasil disimpan',
            'result' => $promo
        ], 200);
    }

    public function promoDetail(LandingPromo $promo, Request $request)
    {
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'detail promo web',
            'result' => $promo
        ], 200);
    }

    public function promoDelete(LandingPromo $promo, Request $request)
    {

        if ($promo->company_id && auth()->user()->company_id != $promo->company_id) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => 'Anda tidak memiliki akses untuk data ini',
            ], 400);
        }
        // Unlink file_photo
        if ($promo->file) {
            // Initialize
            $background = explode('/', $promo->file);

            @unlink('storage/uploads/promo/'.end($background));
        }
        
        $promo->delete();
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'hapus promo web',
        ], 200);
    }
}
