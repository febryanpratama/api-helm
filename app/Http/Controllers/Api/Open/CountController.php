<?php

namespace App\Http\Controllers\Api\Open;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Invoice;
use App\Project;
use App\Company;
use App\Portfolio;

class CountController extends Controller
{
    public function index()
    {
        // Initialize
        $buyer          = User::where(['company_id' => null, 'role_id' => 6])->count();
        $seller         = User::where(['role_id' => 1])->where('company_id', '!=', null)->count();
        $project        = Project::count();
        $projectDone    = Project::where('status', 1)->count();
        $projectOnGoing = Project::where('status', 0)->count();
        $company        = Company::count();
        $portfolio      = Portfolio::count();
        $invoice        = Invoice::where('status', 1)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total_buyer'               => $buyer,
                'total_seller'              => $seller,
                'total_projek'              => $project,
                'total_projek_selesai'      => $projectDone,
                'total_projek_on_going'     => $projectOnGoing,
                'total_store'               => $company,
                'total_portfolio'           => $portfolio,
                'total_transaksi_selesai'   => $invoice
            ]
        ]);
    }

    public function countBuyers()
    {
        // Initialize
        $data = User::where(['company_id' => null, 'role_id' => 6])->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Buyer'
            ]
        ]);
    }

    public function countSeller()
    {
        // Initialize
        $data = User::where(['role_id' => 1])->where('company_id', '!=', null)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Seller'
            ]
        ]);
    }

    public function countProject()
    {
        // Initialize
        $data = Project::count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Proyek'
            ]
        ]);
    }

    public function countProjectDone()
    {
        // Initialize
        $data = Project::where('status', 1)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Proyek'
            ]
        ]);
    }

    public function countProjectOnGoing()
    {
        // Initialize
        $data = Project::where('status', 0)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Proyek'
            ]
        ]);
    }

    public function countCompany()
    {
        // Initialize
        $data = Company::count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Toko'
            ]
        ]);
    }

    public function countPortfolio()
    {
        // Initialize
        $data = Portfolio::count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Portofolio'
            ]
        ]);
    }

    public function countDoneTransaction()
    {
        // Initialize
        $data = Invoice::where('status', 1)->count();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'total'     => $data,
                'details'   => $data.' Transaksi'
            ]
        ]);
    }
}
