<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Wallet;
use App\HistoryTransfer;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role_id != '10') {
            // Initialize
            $wallet     = Wallet::where('user_id', auth()->user()->id);
            $balance    = $wallet->sum('balance');
            $history    = $wallet->latest()->get();
            $balanceIn  = $wallet->where('balance_type', 0)->sum('balance');
            $balanceOut = Wallet::where(['user_id' => auth()->user()->id, 'balance_type' => 1])->sum('balance');
            
            return view('wallet.index', compact('balance','history','balanceIn','balanceOut'));
        } else {
            // System
            $totalBalance = HistoryTransfer::sum('total_for_system');

            // Initialize
            $balance    = Wallet::sum('balance');
            $balanceIn  = Wallet::where('balance_type', 0)->sum('balance');
            $balanceOut = Wallet::where('balance_type', 1)->sum('balance');
            $history    = Wallet::where(['balance_type' => 1])->latest()->paginate(20);

            return view('admin-panel.wallet.index', compact('balance','balanceIn','balanceOut','totalBalance','history'));
        }
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
        // Initialize
        $balanceInput = str_replace('.', '', request('balance'));

        // Check Nominal Balance
        if ($balanceInput < 250000) {
            return response()->json([
                'status'    => false,
                'message'   => 'Minimal Transfer Rp.250.000'
            ]);

            die;
        }

        // Check Total Balance Users
        $currentBalance = Wallet::where('user_id', auth()->user()->id)->sum('balance');

        if ($balanceInput >= $currentBalance) {
            return response()->json([
                'status'    => false,
                'message'   => 'Saldo yang di tarik melebihi total saldo saat ini'
            ]);

            die;
        }

        Wallet::create([
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
            'status'    => true,
            'message'   => 'Withdraw berhasil'
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
