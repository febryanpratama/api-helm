<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Chat;
use Musonza\Chat\Eventing\AllParticipantsClearedConversation as ConversationDelete;
use Musonza\Chat\Eventing\AllParticipantsDeletedMessage;
use DB;
use App\Company;
use App\Transaction;
use App\TransactionPayment;
use App\User;

class TransactionController extends Controller
{
    public function registerTransaction()
    {
        return view('transaction.register');
    }

    public function registerStore()
    {
        // Initialize
        $companyName = str_replace([',','.','`',"'",'"','-','_','(',')','*','^','&','$','#','@','!','+','=','~','?','/','|'], '', request()->company_name);

        if (!$companyName) {
            // Initialize
            $status  = "FAILED";
            $message = "Nama Lembaga Kursus tidak boleh kosong atau menggunakan Karakter Unik/Simbol (Koma,Titik,Strip,Backtick Dan Lain-lain)";

            request()->session()->flash( 'status', $status );
            request()->session()->flash( 'message', $message );

            return redirect()->back();
        }

        // Check Company
        $company = Company::where('Name', 'like', '%'.$companyName. '%')->first();

        if ($company) {
            // Initialize
            $status  = "FAILED";
            $message = "Nama Lembaga Kursus (".request()->company_name.") sudah terdaftar";

            request()->session()->flash( 'status', $status );
            request()->session()->flash( 'message', $message );
            
            return redirect()->back();
        }

        $company = Company::create([
            'Name'          => $companyName,
            'Address'       => request()->company_address,
            'Phone'         => request()->no_hp_account,
            'Email'         => '-',
            'AddedTime'     => time(),
            'AddedByIP'     => '127.0.0.1'
        ]);

        $transaction = Transaction::create([
            'IDClient'              => auth()->user()->id,
            'DesktopPriceFinal'     => 0,
            'IDPackage'             => 1,
            'AddedTime'             => time(),
            'AddedByIP'             => '127.0.0.1',
            'StartDateTime'         => time(),
            'EndDateTime'           => time()
        ]);

        if ($transaction) {
            // Initialize
            $update_user                = User::find(auth()->user()->id);
            $update_user->company_id    = $company->ID;
            $update_user->name          = request()->name;
            $update_user->phone         = request()->no_hp_account;
            $update_user->is_active     = 'y';
            $update_user->save();
           
            // Initialize
            $uniqueCode = rand(100, 900);

            $payment = TransactionPayment::create([
                'IDTransaction'     => $transaction->ID,
                'PaymentType'       => 'bank_transfer',
                'Payment'           => ($transaction->DesktopPriceFinal + $uniqueCode),
                'OriginalPayment'   => $transaction->DesktopPriceFinal,
                'Currency'          => 'IDR',
                'CurrencySign'      => 'Rp',
                'PaymentTo'         => '-',
                'AddedTime'         => time(),
                'AddedByIP'         => '127.0.0.1',
                'Status'            => 'Paid',
                'UniqueCode'        => $uniqueCode,
                'MootaStatus'       => 1,
                'IsVerified'        => 'y'
            ]);

            if ($payment) {
                $status  = 'OK';
                $message = "Berhasil ditambahkan";

                request()->session()->flash( 'status', $status );
                request()->session()->flash( 'message', $message );

                return redirect()->route('home', $payment->ID);
            }
        }
    }
}
