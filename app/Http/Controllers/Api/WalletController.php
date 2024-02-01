<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\WithdrawRequest;
use App\Wallet;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletController extends Controller
{
    public function index()
    {
        // Initialize
        $wallet     = Wallet::where('user_id', auth()->user()->id);
        $balance    = $wallet->sum('balance');
        $balanceIn  = $wallet->where('balance_type', 0)->sum('balance');
        $balanceOut = Wallet::where(['user_id' => auth()->user()->id, 'balance_type' => 1])->sum('balance');

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data wallet',
            'data'      => [
                'balance'           => $balance,
                'balance_rupiah'    => rupiah($balance),
                'balanceIn'         => $balanceIn,
                'balanceOut'        => $balanceOut
            ]
        ]);
    }

    public function history(Request $request)
    {
        // Initialize
        $wallet  = Wallet::where('user_id', auth()->user()->id)->latest()->get();
        $history = $this->paginate($wallet, 20, null, ['path' => $request->fullUrl()]);
        $data    = [];

        foreach($history as $val) {
            $row['id']                  = $val['id'];
            $row['user_id']             = $val['user_id'];
            $row['account_number']      = $val['account_number'];
            $row['account_holder_name'] = $val['account_holder_name'];
            $row['bank_name']           = $val['bank_name'];
            $row['balance']             = $val['balance'];
            $row['is_verified']         = $val['is_verified'];
            $row['balance_type']        = $val['balance_type'];
            $row['apps_commission']     = $val['apps_commission'];
            $row['original_balance']    = $val['original_balance'];
            $row['unique_code']         = $val['unique_code'];
            $row['details']             = $val['details'];
            $row['created_at']          = $val['created_at'];
            $row['updated_at']          = $val['updated_at'];
            
            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data histori',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $history->currentPage(),
                'from'              => 1,
                'last_page'         => $history->lastPage(),
                'next_page_url'     => $history->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $history->perPage(),
                'prev_page_url'     => $history->previousPageUrl(),
                'total'             => $history->total()
            ]
        ]);
    }

    public function withdraw(WithdrawRequest $request)
    {
        // Initialize
        $balanceInput = str_replace('.', '', request('balance'));

        // Check Nominal Balance
        if ($balanceInput < 250000) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Minimal Transfer Rp.250.000'
            ]);

            die;
        }

        // if ($balanceInput < 1) {
        //     return response()->json([
        //         'status'    => 'error',
        //         'message'   => 'Minimal Transfer Rp.1'
        //     ]);

        //     die;
        // }

        // Check Total Balance Users
        $currentBalance = Wallet::where('user_id', auth()->user()->id)->sum('balance');

        if ($balanceInput >= $currentBalance) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Saldo yang di tarik melebihi total saldo saat ini'
            ]);

            die;
        }

        $wallet = Wallet::create([
            'user_id'               => auth()->user()->id,
            'account_number'        => request('account_number'),
            'account_holder_name'   => request('account_holder_name'),
            'bank_name'             => request('bank_name'),
            'balance'               => '-'.$balanceInput,
            'is_verified'           => 0,
            'balance_type'          => 1,
            'apps_commission'       => 0,
            'original_balance'      => '-'.$balanceInput,
            'unique_code'           => 0,
            'details'               => 'Penarikan'
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Withdraw berhasil',
            'data'      => $wallet
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
