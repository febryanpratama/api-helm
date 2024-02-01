<?php

namespace App\Http\Controllers\Api;

use App\Auction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $auction = Auction::whereNull('deleted_at')->latest()->paginate(10);
        if (auth()->user()->role_id == 1) {
            $auction = Auction::where('company_id', auth()->user()->company_id)->whereNull('deleted_at')->latest()->paginate(10);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'list auction',
            'result' => $auction
        ], 200);
    }

    public function store(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'product_id'            => 'required|exists:course,id',
            'open_bid'              => 'required|numeric',
            'min_increase_bid'      => 'required|numeric',
            'file'                  => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240',
            'start_period'          => 'required|date_format:Y-m-d H:i|before_or_equal:end_period',
            'end_period'            => 'required|date_format:Y-m-d H:i',
            'extra_time'            => 'nullable|numeric',
            'deal_option'           => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // Initialze
        $file = null;

        if (request()->file('file')) {
            // Initialize
            $path       = request()->file('file')->store('uploads/auction', 'public');
            $file       = env('SITE_URL').'/storage/'.$path;
        }

        $auction = Auction::create([
            'product_id'            => $request->product_id,
            'company_id'            => auth()->user()->company_id,
            'open_bid'              => $request->open_bid,
            'min_increase_bid'      => $request->min_increase_bid,
            'file'                  => $file,
            'start_period'          => $request->start_period,
            'end_period'            => $request->end_period,
            'extra_time'            => $request->extra_time,
            'deal_option'           => $request->deal_option,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'auction berhasil disimpan',
            'result' => $auction
        ], 200);
    }

    public function show(Auction $auction)
    {
        return response()->json([
            'status'    => true,
            'code'      => 200,
            'message'   => 'Data tersedia',
            'result'    => $auction
        ]);
    }

    public function update(Auction $auction, Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'product_id'            => 'required|exists:course,id',
            'open_bid'              => 'required|numeric',
            'min_increase_bid'      => 'required|numeric',
            'file'                  => 'nullable|mimes:jpg,jpeg,png,mp4|max:10240',
            'start_period'          => 'required|date_format:Y-m-d H:i|before_or_equal:end_period',
            'end_period'            => 'required|date_format:Y-m-d H:i',
            'extra_time'            => 'nullable|numeric',
            'deal_option'           => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // Initialze
        $file = $auction->file;

        if (request()->file('file')) {
            // Initialize
            $path       = request()->file('file')->store('uploads/auction', 'public');
            $file       = env('SITE_URL').'/storage/'.$path;

            // Check Thumbnail
            if ($auction->file) {
                $explodePath = explode('/', $auction->file);

                @unlink('storage/uploads/auction/'.end($explodePath));
            }
        }

        $auction->update([
            'product_id'            => $request->product_id,
            'company_id'            => auth()->user()->company_id,
            'open_bid'              => $request->open_bid,
            'min_increase_bid'      => $request->min_increase_bid,
            'file'                  => $file,
            'start_period'          => $request->start_period,
            'end_period'            => $request->end_period,
            'extra_time'            => $request->extra_time,
            'deal_option'           => $request->deal_option,
        ]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'auction berhasil disimpan',
            'result' => $auction
        ], 200);
    }

    public function destroy(Auction $auction)
    {
        // Check Thumbnail
        if ($auction->file) {
            $explodePath = explode('/', $auction->file);

            @unlink('storage/uploads/auction/'.end($explodePath));
        }

        $auction->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'auction berhasil dihapus',
        ], 200);
    }
}
