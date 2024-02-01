<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\TransactionDetails;
use App\WorkReportTransactionDetails;

class WorkReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data = WorkReportTransactionDetails::where('transaction_details_id', request('transaction_details_id'))->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'transaction_details_id' => 'required',
            'file'                   => 'required|array',
            'file.*'                 => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $id                 = request('transaction_details_id');
        $transactionDetails = TransactionDetails::where('id', $id)->first();

        if (!$transactionDetails) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Detail Transaksi dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Check Invoice
        if ($transactionDetails->transaction->invoice->status == 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Detail Transaksi dengan ID ('.$id.') belum melakukan pembayaran.'
            ]);
        }

        // Initialize
        $files = request('file');

        // Initialize
        $data = $this->_oneFunction($files, 'validate');

        if ($data['status'] == 'error') {
            return $data;
        } else {
            // Initialize
            $this->_oneFunction($files, 'store');
            
            // Check Data
            $data = WorkReportTransactionDetails::where('transaction_details_id', $id)->get();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Data berhasil ditambahkan.',
                'data'      => $data
            ]);
        }
    }

    private function _oneFunction($files, $config)
    {
        // Validation
        foreach($files as $key => $val) {
            // Initialize
            $detalisFile  = request()->file('file')[$key];
            $extFT        = $detalisFile->getClientOriginalExtension();
            $originalName = $detalisFile->getClientOriginalName();
            $maxSize      = $detalisFile->getSize();

            if ($config == 'validate') {
                if ($maxSize > 5000000) {
                    return [
                        'status'  => 'error',
                        'message' => 'Ukuran file ('.$originalName.') melebihi batas yang ditentukan. Maksimal ukuran file 5 MB'
                    ];

                    break;
                }
            } else {
                // Upload File
                $path = $detalisFile->store('uploads/work-report', 'public');

                if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
                    $fileType = 1;
                } else if ($extFT == 'mp4' || $extFT == 'mkv') {
                    $fileType = 2;
                } else {
                    $fileType = 3;
                }

                WorkReportTransactionDetails::create([
                    'transaction_details_id' => request('transaction_details_id'),
                    'path'                   => env('SITE_URL').'/storage/'.$path,
                    'file_type'              => $fileType
                ]);
            }
        }

        return [
            'status' => 'success'
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Initialize
        $workReport = WorkReportTransactionDetails::where('id', $id)->first();

        if (!$workReport) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Media dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Explode
        $explodePath = explode('/', $workReport->path);
        @unlink('storage/uploads/work-report/'.$explodePath[6]);

        $workReport->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }
}
