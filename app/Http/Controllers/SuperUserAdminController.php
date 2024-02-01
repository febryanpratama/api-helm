<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperUserAdminController extends Controller
{
    public function index()
    {
        return view('super_admin.index');
    }

    public function listTransaction()
    {
        $transaction = \App\Transaction::paginate(10);
        return view('super_admin.list_transaction', compact('transaction'));
    }

    public function listCompany()
    {
        $company = \App\Company::paginate(10);
        return view('super_admin.list_company', compact('company'));
    }

    public function detailTransaction(\App\TransactionPayment $payment)
    {
        return view('super_admin.detail_transaction', compact('payment'));
    }

    public function detailCompany(\App\Company $company)
    {
        return view('super_admin.detail_company', compact('company'));
    }

    public function paymentVerify($payment)
    {
        // dd(request()->all());
        $status = 'FAILED';
        $message = "Failed verify";
        $payment = \App\TransactionPayment::find($payment);
        if ($payment) {

            if (request()->file( 'file' )) {
        
                $imagePath = request('file')->store('uploads/img/transaction', 'public');
    
                $payment->Location = env('SITE_URL') . '/storage/' . $imagePath;

                $payment->Status = 'Paid';
                $payment->IsVerified = 'y';
                $payment->save();

                $user = \App\User::find($payment->transaction->user->id);
                $user->is_active = 'y';
                $user->save();
                $company = \App\Company::find($user->company->ID);
                $company->IsConfirmed = 'y';
                $company->save();
                $user->notify(new \App\Notifications\TransactionApprove($user->id, auth()->user(), $company));
                $status = 'OK';
		        $message = "Success";

                if ($user->company->ExpiredDate != null) {
                    if (date('Y-m-d') > $user->company->ExpiredDate) { // subscribe saat expired
                        $duration = $payment->transaction->package->Subscribe;
                        $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", time()));
                        $company->save();
                    } else {
                        $duration = $payment->transaction->package->Subscribe;
                        $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", strtotime($user->company->ExpiredDate)));
                        $company->save();
                    }
                } else {
                    $duration = $payment->transaction->package->Subscribe;
                    $company->ExpiredDate = date("Y-m-d", strtotime("+ $duration month", time()));
                    $company->save();
                }

            }
        }
        request()->session()->flash( 'status', $status );
        request()->session()->flash( 'message', $message );
        return redirect()->back();
    }
}
