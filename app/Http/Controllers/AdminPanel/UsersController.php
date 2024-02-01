<?php

namespace App\Http\Controllers\AdminPanel;

use App\Address;
use App\Course;
use App\Http\Controllers\Controller;
use App\Company;
use App\Invoice;
use App\InvoiceAddress;
use App\OfficePhoto;
use App\Portfolio;
use App\TeamPhoto;
use Illuminate\Http\Request;
use App\User;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $users = User::whereNotNull('otp')->latest()->paginate(20);

        return view('admin-panel.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function sellers()
    {
        // Initialize
        $users = User::with('company')->where('role_id', '1')->latest()->paginate(20);

        return view('admin-panel.users.sellers', compact('users'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sellersDelete($id)
    {
        /*
            1. Delete Invoice
            2. Delete Address
            3. Delete Portfolio
            4. Delete Team Photo
            5. Delete Office Photo
            6. Delete Users
        */

        // Initialize
        $user    = User::where('id', $id)->first();
        $invoice = Invoice::where('user_id', $id)->get();

        foreach ($invoice as $val) {
            foreach ($val->transaction as $t) {
                foreach ($t->transactionDetails as $td) {
                    $td->delete();
                }

                $t->delete();
            }

            // Invoice Address
            InvoiceAddress::where('invoice_id', $val->id)->delete();

            $val->delete();
        }

        $address        = Address::where('user_id', $id)->delete();
        $portfolio      = Portfolio::where('company_id', $user->company_id)->delete();
        $teamPhoto      = TeamPhoto::where('company_id', $user->company_id)->delete();
        $officePhoto    = OfficePhoto::where('company_id', $user->company_id)->delete();
        $company        = Company::where('ID', $user->company_id)->delete();
        
        foreach ($user->course as $val) {
            // Unlink File
            $explodePath = explode('/', $val->thumbnail);
            
            if (count($explodePath) >= 7) {
                @unlink('storage/uploads/course/'.auth()->user()->company->Name.'-'.auth()->user()->id.'/'.$explodePath[7]);
            }

            $val->delete();
        }

        $user->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mengahpus data.'
        ]);
    }
}
