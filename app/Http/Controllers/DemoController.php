<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\TransactionPayment;
use App\Transaction;
use App\User;
use App\Company;
use Auth;

class DemoController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
        }
        
        // Create Session For Demo Account
        request()->session()->put([
            'is_demo' => true
        ]);

        // Initialize
        $textMenu = 'Masuk Demo';

        return view('auth.login', compact('textMenu'));
    }

    public function activeAccount()
    {
        // Check Account
        if (auth()->user()->is_demo == 1) {
            $transactionPayment = TransactionPayment::where('id', request('paymentId'))->first();

            // Update Status TransactionPayment
            $transactionPayment->update([
                'IsVerified'    => 'y',
                'Status'        => 'Paid',
                'MootaStatus'   => '1'
            ]);

            // Initialize
            $transaction = Transaction::where('id', $transactionPayment->IDTransaction)->first();
            $user        = User::where('id', $transaction->IDClient)->first();

            // Update Status User
            $user->update([
                'is_active' => 'y'
            ]);

            // Get Company
            $company = Company::find($user->company_id);

            if ($user->company->ExpiredDate != null) {
                if (date('Y-m-d') > $user->company->ExpiredDate) { // subscribe saat expired
                    $duration = $transactionPayment->transaction->package->Subscribe;
                    $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", time()));
                    $company->IsConfirmed = 'y';
                    $company->save();
                } else {
                    $duration = $transactionPayment->transaction->package->Subscribe;
                    $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", strtotime($user->company->ExpiredDate)));
                    $company->IsConfirmed = 'y';
                    $company->save();
                }
            } else {
                $duration = $transactionPayment->transaction->package->Subscribe;
                $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", time()));
                $company->IsConfirmed = 'y';
                $company->save();
            }

            return response()->json([
                'status'    => true,
                'message'   => 'Selamat AKUN DEMO anda sudah aktif!'
            ]);

            die;
        }

        return response()->json([
            'status'    => false,
            'message'   => 'Yuk buat akun demo terlebih dahulu!'
        ]);
    }
}
