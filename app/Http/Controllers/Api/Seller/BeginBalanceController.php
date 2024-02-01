<?php

namespace App\Http\Controllers\Api\Seller;

use App\BeginBalance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class BeginBalanceController extends Controller
{
    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function index(Request $request)
    {
        // Initialize
        $begin_balance = BeginBalance::orderBy('ID', 'DESC')->get();


        // Custom Paginate
        $begin_balance = $this->paginate($begin_balance, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($begin_balance as $val) {
            // Initialize
            $row['id'] = $val->ID;
            $row['account_id'] = $val->IDAccount;
            $row['currency_id'] = $val->IDCurrency;
            $row['rate'] = $val->Rate;
            $row['start_period'] = $val->StartPeriod;
            $row['end_period'] = $val->EndPeriod;
            $row['method'] = $val->Method;
            $row['balance'] = $val->Balance;
            $row['account'] = $val->account->formatData();

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data begin balance.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $begin_balance->currentPage(),
                'from'              => 1,
                'last_page'         => $begin_balance->lastPage(),
                'next_page_url'     => $begin_balance->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $begin_balance->perPage(),
                'prev_page_url'     => $begin_balance->previousPageUrl(),
                'total'             => $begin_balance->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'currency_id'           => 'required|integer',
            'rate'                  => 'required|numeric',
            'account_id'            => 'required|integer|exists:accounts,ID',
            'start_period'          => 'required|date_format:Y-m-d',
            'end_period'            => 'required|date_format:Y-m-d|after:start_period',
            'method'                => 'required|in:0,1', // 0 perpetual, 1 periodik
            'balance'               => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // CHECK PERIOD
        // $check = BeginBalance::where('StartPeriod', '>=', $request->start_period)->where('EndPeriod', '<=', $request->end_period)->first();
        $check = BeginBalance::where('EndPeriod', '>=', $request->start_period)->first();

        if ($check) {
            $data = [
                'status'    => 'error',
                'message'   => 'Untuk periode ' . date('d M Y', strtotime($check->StartPeriod)) . ' - ' . date('d M Y', strtotime($check->EndPeriod)) . ' sudah ada',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $begin_balance = BeginBalance::create([
            'IDCurrency'            => $request->currency_id,
            'Rate'                  => $request->rate,
            'IDAccount'             => $request->account_id,
            'StartPeriod'           => $request->start_period,
            'EndPeriod'             => $request->end_period,
            'Method'                => $request->method,
            'Balance'               => $request->balance,
            'AddedTime'             => time(),
            'AddedBy'               => auth()->user()->id,
            'AddedByIP'             => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 201,
            'message'   => 'Berhasil disimpan',
            'data'    => $begin_balance,
        ], 201);
    }

    public function show(BeginBalance $begin_balance)
    {
        
        $res['id'] = $begin_balance->ID;
        $res['account_id'] = $begin_balance->IDAccount;
        $res['currency_id'] = $begin_balance->IDCurrency;
        $res['rate'] = $begin_balance->Rate;
        $res['start_period'] = $begin_balance->StartPeriod;
        $res['end_period'] = $begin_balance->EndPeriod;
        $res['method'] = $begin_balance->Method;
        $res['balance'] = $begin_balance->Balance;
        $res['account'] = $begin_balance->account->formatData();

        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'detail journal',
            'data'    => $res
        ];

        return response()->json($data, 200);
    }

    public function update(BeginBalance $begin_balance, Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'currency_id'           => 'required|integer',
            'rate'                  => 'required|numeric',
            'account_id'            => 'required|integer|exists:accounts,ID',
            'start_period'          => 'required|date_format:Y-m-d',
            'end_period'            => 'required|date_format:Y-m-d|after:start_period',
            'method'                => 'required|in:0,1', // 0 perpetual, 1 periodik
            'balance'               => 'required|numeric'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // CHECK PERIOD
        $check = BeginBalance::where('EndPeriod', '>=', $request->start_period)->first();

        if ($check) {
            $data = [
                'status'    => 'error',
                'message'   => 'Untuk periode ' . date('d M Y', strtotime($check->StartPeriod)) . ' - ' . date('d M Y', strtotime($check->EndPeriod)) . ' sudah ada',
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $begin_balance->update([
            'IDCurrency'            => $request->currency_id,
            'Rate'                  => $request->rate,
            'IDAccount'             => $request->account_id,
            'StartPeriod'           => $request->start_period,
            'EndPeriod'             => $request->end_period,
            'Method'                => $request->method,
            'Balance'               => $request->balance,
            'EditedTime'            => time(),
            'EditedBy'              => auth()->user()->id,
            'EditedByIP'            => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dirubah',
            'data'    => $begin_balance,
        ], 200);
    }

    public function destroy(BeginBalance $begin_balance)
    {
        $begin_balance->delete();

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dihapus',
        ], 200);
    }
}
