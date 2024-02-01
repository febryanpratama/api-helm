<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Package;

class UpgradeAccountController extends Controller
{
    public function index()
    {
        // Initialize
        $package = Package::all();
        
        return view('upgrade-account.index', compact('package'));
    }

    public function register()
    {
        // Check Company
        $company = \App\Company::where('Name', 'like', '%'.request()->company_name . '%')->where('Address', 'like', '%'.request()->company_address.'%')->where('IsConfirmed', 'y')->first();
        
        if ($company) {
            $status = "FAILED";
            $message = "Instansi " . $company->Name . " yang ber alamat di ". $company->Address. ' sudah terdaftar disistem';
            request()->session()->flash( 'status', $status );
            request()->session()->flash( 'message', $message );
            return redirect()->back();
        }

        $company = \App\Company::create([
            'Name' => request()->company_name,
            'Address' => request()->company_name,
            'Phone' => '-',
            'Email' => '-',
            'Type' => request()->agency,
            'AddedTime' => time(),
            'AddedByIP' => '127.0.0.1',
        ]);

        $package     = \App\Package::find(request()->package);
        $transaction = \App\Transaction::create([
            'IDClient'          => auth()->user()->id,
            'DesktopPriceFinal' => $package->DesktopPriceFinal,
            'IDPackage'         => request()->package,
            'AddedTime'         => time(),
            'AddedByIP'         => '127.0.0.1',
            'StartDateTime'     => time(),
            'EndDateTime'       => time()
        ]);

        if ($transaction) {
            $update_user = \App\User::find(auth()->user()->id);
            $update_user->company_id = $company->ID;
            $update_user->name       = request('name');
            $update_user->is_active  = 'n';
            $update_user->is_demo    = 0;
            $update_user->save();
           
            if (request()->file( 'npwp' )) {
        
                $imagePath = request('npwp')->store('uploads/img/company/npwp', 'public');
                $company->NPWP = env('SITE_URL') . '/storage/' . $imagePath;
                $company->save();
            }

            if (request()->file( 'nppkp' )) {
        
                $imagePath = request('nppkp')->store('uploads/img/company/nppkp', 'public');
                $company->NPPKP = env('SITE_URL') . '/storage/' . $imagePath;
                $company->save();
            }

            if (request()->file( 'sk_pendirian' )) {
        
                $imagePath = request('nppkp')->store('uploads/img/company/sk_pendirian', 'public');
                $company->SKPendirian = env('SITE_URL') . '/storage/' . $imagePath;
                $company->save();
            }

            // Initialize
            $uniqueCode = rand(100, 900);

            $payment = \App\TransactionPayment::create([
                'IDTransaction' => $transaction->ID,
                'PaymentType' => 'bank_transfer',
                'Payment' => ($transaction->DesktopPriceFinal + $uniqueCode),
                'OriginalPayment' => $transaction->DesktopPriceFinal,
                'Currency' => 'IDR',
                'CurrencySign' => 'Rp',
                'PaymentTo' => request()->bank,
                'AddedTime' => time(),
                'AddedByIP' => '127.0.0.1',
                'Status' => 'Pending',
                'UniqueCode' => $uniqueCode
            ]);

            if ($payment) {
                $status = 'OK';
                $message = "Berhasil ditambahkan";
                
                request()->session()->flash( 'status', $status );
                request()->session()->flash( 'message', $message );
                
                return redirect()->route('transaction.detail', $payment->ID);
            }
        }
    }
}
