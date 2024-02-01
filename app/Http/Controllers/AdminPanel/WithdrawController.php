<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Wallet;

class WithdrawController extends Controller
{
    public function update(Request $request)
    {
        // Initialize
        $path               = request()->file('evidence_of_transfer');
        $evidenceOfTransfer = '';

        if ($path) {
            // Initialize
            $evidenceOfTransfer = env('SITE_URL'). '/storage/'.request('evidence_of_transfer')->store('uploads/evidence-of-transfer', 'public');
        }
        
        if (auth()->user()->role_id == 10) {
            // Initialize
            $wallet = Wallet::where('id', request('id'))->first();

            if ($wallet) {
                $wallet->update([
                    'is_verified'           => 1,
                    'evidence_of_transfer'  => $evidenceOfTransfer
                ]);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Withdraw berhasil di Approve'
        ]);
    }
}
