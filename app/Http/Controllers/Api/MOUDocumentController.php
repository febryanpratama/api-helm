<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MOUDocument;
use App\TransactionDetails;
use Validator;

class MOUDocumentController extends Controller
{
    // MOU Document
    public function index()
    {
        // Initialize
        $data = MOUDocument::where('transaction_details_id', request('transaction_details_id'))->first();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function store()
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'transaction_details_id' => 'required|integer',
            'file'                   => 'required|mimes:pdf'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check Transaction Details Id
        $transactionDetails = TransactionDetails::where('id', request('transaction_details_id'))->first();

        if (!$transactionDetails) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi Details Dengan ID ('.request('transaction_details_id').') tidak ditemukan.'
            ]);
        }

        // Check MOU Exists
        $existsData = MOUDocument::where('transaction_details_id', request('transaction_details_id'))->first();

        if ($existsData) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda Sudah mengunggah Dokumen MOU sebelumnya.'
            ]);
        }

        // Upload File
        $file = request()->file('file');
        $path = $file->store('uploads/mou', 'public');

        $data = MOUDocument::create([
            'transaction_details_id' => request('transaction_details_id'),
            'document_from_seller'    => env('SITE_URL').'/storage/'.$path
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $data
        ]);
    }

    public function update()
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'transaction_details_id' => 'required|integer',
            'file'                   => 'required|mimes:pdf'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check Transaction Details Id
        $transactionDetails = TransactionDetails::where('id', request('transaction_details_id'))->first();

        if (!$transactionDetails) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Transaksi Details Dengan ID ('.request('transaction_details_id').') tidak ditemukan.'
            ]);
        }

        // Check MOU Exists
        $mouDocumentExists = MOUDocument::where('transaction_details_id', request('transaction_details_id'))->first();

        if (!$mouDocumentExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Seller belum melakukan Unggah Dokumen MOU.'
            ]);
        }

        if ($mouDocumentExists->document_from_buyer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda Sudah mengunggah Dokumen MOU sebelumnya.'
            ]);
        }

        // Upload File
        $file = request()->file('file');
        $path = $file->store('uploads/mou', 'public');

        $mouDocumentExists->update([
            'document_from_buyer' => env('SITE_URL').'/storage/'.$path
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $mouDocumentExists
        ]);
    }

    public function destroy($id)
    {
        // Check Data
        $data = MOUDocument::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($data->document_from_buyer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Dokumen tidak bisa di hapus, buyer sudah mengunggah dokumen.'
            ]);
        }

        // Initialize
        $explodePath = explode('/', $data->document_from_seller);
        
        @unlink('storage/uploads/mou/'.$explodePath[6]);

        $data->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.'
        ]);
    }

    public function destroyFromBuyer($id)
    {
        // Check Data
        $data = MOUDocument::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if (!$data->document_from_buyer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Dokumen tidak ditemukan.'
            ]);
        }

        // Initialize
        $explodePath = explode('/', $data->document_from_buyer);
        
        @unlink('storage/uploads/mou/'.$explodePath[6]);

        $data->update([
            'document_from_buyer' => null
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.'
        ]);
    }
}
