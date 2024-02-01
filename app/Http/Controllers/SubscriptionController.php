<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function package()
    {
        $package = \App\Package::all();
        $company = array();
        if (request()->has('status')) {
            $company = \App\Company::where('Name', 'like', '%'.request()->search)->where('Type', request()->get('agency'))->get();
        }
        if (request()->has('agency')) {
            $data_user['agency'] = request()->get('agency');
            request()->session()->put($data_user);
        }
        return view('subscribe.package', compact('package', 'company'));
    }

    public function transaction()
    {
        // Initialize
        $package    = \App\Package::find(request()->get('package'));
        $uniqueCode = rand(100, 999);
        
        return view('subscribe.transaction', compact('package', 'uniqueCode'));
    }

    public function paymentStore()
    {
        $transaction = \App\Transaction::create([
            'IDClient' => auth()->user()->id,
            'IDPackage' => request()->package,
            'AddedTime' => time(),
            'AddedByIP' => '127.0.0.1',
            'StartDateTime' => time(),
            'EndDateTime' => time(),
            'BookingCode' => md5(uniqid(rand(), true)),
            'TotalPax' => 1,
        ]);

        if ($transaction) {
            $package = \App\Package::find(request()->package);
            $transaction->ContractPricePerAdult = $package->ContractPricePerAdult;
            $transaction->ContractPricePerChild = $package->ContractPricePerChild;
            $transaction->PublishPrice = $package->PublishPrice;
            $transaction->PublishPricePerAdult = $package->PublishPricePerAdult;
            $transaction->PublishPricePerChild = $package->PublishPricePerChild;
            $transaction->PercentageDiscountContractPrice = $package->PercentageDiscountContractPrice;
            $transaction->DiscountContractPrice = $package->DiscountContractPrice;
            $transaction->DiscountContractPricePerAdult = $package->DiscountContractPricePerAdult;
            $transaction->DiscountContractPricePerChild = $package->DiscountContractPricePerChild;
            $transaction->PercentageDiscountPublishPrice = $package->PercentageDiscountPublishPrice;
            $transaction->DiscountPublishPrice = $package->DiscountPublishPrice;
            $transaction->DiscountPublishPricePerAdult = $package->DiscountPublishPricePerAdult;
            $transaction->DiscountPublishPricePerChild = $package->DiscountPublishPricePerChild;
            $transaction->PercentageCommission = $package->PercentageCommission;
            $transaction->PercentageCommissionPerAdult = $package->PercentageCommissionPerAdult;
            $transaction->PercentageCommissionPerChild = $package->PercentageCommissionPerChild;
            $transaction->PercentageTaxFromCommission = $package->PercentageTaxFromCommission;
            $transaction->PercentageServiceCharge = $package->PercentageServiceCharge;
            $transaction->ServiceCharge = $package->ServiceCharge;
            $transaction->ServiceChargePerAdult = $package->ServiceChargePerAdult;
            $transaction->ServiceChargePerChild = $package->ServiceChargePerChild;
            $transaction->PercentageTax = $package->PercentageTax;
            $transaction->Tax = $package->Tax;
            $transaction->TaxPerAdult = $package->TaxPerAdult;
            $transaction->TaxPerChild = $package->TaxPerChild;
            $transaction->ContractPriceFinal = $package->ContractPriceFinal;
            $transaction->ContractPricePerAdultFinal = $package->ContractPricePerAdultFinal;
            $transaction->ContractPricePerChildFinal = $package->ContractPricePerChildFinal;
            $transaction->PublishPriceFinal = $package->PublishPriceFinal;
            $transaction->PublishPricePerAdultFinal = $package->PublishPricePerAdultFinal;
            $transaction->PublishPricePerChildFinal = $package->PublishPricePerChildFinal;
            $transaction->PercentageCommissionDesktopPrice = $package->PercentageCommissionDesktopPrice;
            $transaction->CommissionDesktopPrice = $package->CommissionDesktopPrice;
            $transaction->CommissionDesktopPricePerAdult = $package->CommissionDesktopPricePerAdult;
            $transaction->CommissionDesktopPricePerChild = $package->CommissionDesktopPricePerChild;
            $transaction->PointValueDesktopPrice = $package->PointValueDesktopPrice;
            $transaction->PointValueDesktopPricePerAdult = $package->PointValueDesktopPricePerAdult;
            $transaction->PointValueDesktopPricePerChild = $package->PointValueDesktopPricePerChild;
            $transaction->TaxFromCommissionDesktopPrice = $package->TaxFromCommissionDesktopPrice;
            $transaction->TaxFromCommissionDesktopPricePerAdult = $package->TaxFromCommissionDesktopPricePerAdult;
            $transaction->TaxFromCommissionDesktopPricePerChild = $package->TaxFromCommissionDesktopPricePerChild;
            $transaction->DesktopPrice = $package->DesktopPrice;
            $transaction->DesktopPricePerAdult = $package->DesktopPricePerAdult;
            $transaction->DesktopPricePerChild = $package->DesktopPricePerChild;
            $transaction->DesktopPriceFinal = $package->DesktopPriceFinal;
            $transaction->DesktopPricePerAdultFinal = $package->DesktopPricePerAdultFinal;
            $transaction->DesktopPricePerChildFinal = $package->DesktopPricePerChildFinal;
            $transaction->PercentageCommissionMobilePrice = $package->PercentageCommissionMobilePrice;
            $transaction->CommissionMobilePrice = $package->CommissionMobilePrice;
            $transaction->CommissionMobilePricePerAdult = $package->CommissionMobilePricePerAdult;
            $transaction->CommissionMobilePricePerChild = $package->CommissionMobilePricePerChild;
            $transaction->PointValueMobilePrice = $package->PointValueMobilePrice;
            $transaction->PointValueMobilePricePerAdult = $package->PointValueMobilePricePerAdult;
            $transaction->PointValueMobilePricePerChild = $package->PointValueMobilePricePerChild;
            $transaction->TaxFromCommissionMobilePrice = $package->TaxFromCommissionMobilePrice;
            $transaction->TaxFromCommissionMobilePricePerAdult = $package->TaxFromCommissionMobilePricePerAdult;
            $transaction->TaxFromCommissionMobilePricePerChild = $package->TaxFromCommissionMobilePricePerChild;
            $transaction->MobilePrice = $package->MobilePrice;
            $transaction->MobilePricePerAdult = $package->MobilePricePerAdult;
            $transaction->MobilePricePerChild = $package->MobilePricePerChild;
            $transaction->MobilePriceFinal = $package->MobilePriceFinal;
            $transaction->MobilePricePerAdultFinal = $package->MobilePricePerAdultFinal;
            $transaction->MobilePricePerChildFinal = $package->MobilePricePerChildFinal;
            $transaction->CommissionMobileAppPrice = $package->CommissionMobileAppPrice;
            $transaction->CommissionMobileAppPricePerAdult = $package->CommissionMobileAppPricePerAdult;
            $transaction->CommissionMobileAppPricePerChild = $package->CommissionMobileAppPricePerChild;
            $transaction->PointValueMobileAppPrice = $package->PointValueMobileAppPrice;
            $transaction->PointValueMobileAppPricePerAdult = $package->PointValueMobileAppPricePerAdult;
            $transaction->PointValueMobileAppPricePerChild = $package->PointValueMobileAppPricePerChild;
            $transaction->TaxFromCommissionMobileAppPrice = $package->TaxFromCommissionMobileAppPrice;
            $transaction->TaxFromCommissionMobileAppPricePerAdult = $package->TaxFromCommissionMobileAppPricePerAdult;
            $transaction->TaxFromCommissionMobileAppPricePerChild = $package->TaxFromCommissionMobileAppPricePerChild;
            $transaction->MobileAppPrice = $package->MobileAppPrice;
            $transaction->MobileAppPricePerAdult = $package->MobileAppPricePerAdult;
            $transaction->MobileAppPricePerChild = $package->MobileAppPricePerChild;
            $transaction->MobileAppPriceFinal = $package->MobileAppPriceFinal;
            $transaction->MobileAppPricePerAdultFinal = $package->MobileAppPricePerAdultFinal;
            $transaction->MobileAppPricePerChildFinal = $package->MobileAppPricePerChildFinal;
            $transaction->PercentageDiscountBookingValue = $package->PercentageDiscountBookingValue;
            $transaction->DiscountBookingValueCondition = $package->DiscountBookingValueCondition;
            $transaction->BuyX = $package->BuyX;
            $transaction->GetYFree = $package->GetYFree;
            // $transaction->FreeGiftBookingValue = $package->FreeGiftBookingValue;
            // $transaction->FreeGiftBookingValueCondition = $package->FreeGiftBookingValueCondition;
            $transaction->Currency = 'IDR';
            // $transaction->CurrencySign = $package->CurrencySign;
            $transaction->CurrencySign = 'Rp';
            $transaction->CashbackPointValue = $package->CashbackPointValue;
            $transaction->CashbackPointValueCondition = $package->CashbackPointValueCondition;
            $transaction->Status = 'Waiting for payment';
            $transaction->save();

            $payment = \App\TransactionPayment::create([
                'IDTransaction' => $transaction->ID,
                'PaymentType' => 'bank_transfer',
                'Payment' => $transaction->DesktopPriceFinal,
                'Currency' => 'IDR',
                'CurrencySign' => 'Rp',
                'PaymentTo' => request()->bank,
                'AddedTime' => time(),
                'AddedByIP' => '127.0.0.1',
                'Status' => 'Pending'
            ]);

            if ($payment) {

                $user = \App\User::where('role_id', 10)->first();
                $user->notify(new \App\Notifications\TransactionSuperAdmin($user->id, auth()->user(), $payment));

                $status = 'OK';
                $message = "Berhasil ditambahkan";
                request()->session()->flash( 'status', $status );
                request()->session()->flash( 'message', $message );
                return redirect()->route('subscribe.transaction_detail', $payment->ID);
            }
        }
    }

    public function detail(\App\TransactionPayment $payment)
    {
        return view('transaction.detail', compact('payment'));
    }

    public function memberExpired()
    {
        return view('subscribe.member_expired');
    }
}
