<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CourseTerminSchedule;
use App\TerminScheduleBast;
use App\MediaTerminScheduleBast;
use App\User;
use App\Company;
use App\Transaction;
use App\TransactionDetails;
use Validator;
use Illuminate\Support\Carbon;
use App\Notifications\GlobalNotification;
use Notification;

class TerminScheduleBastController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Initialize
        $terminSchedule = false;
        $transaction    = false;

        // Check use two param & Exists Data
        if (request('termin_schedule_id') && request('transaction_details_id')) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Isi salah satu (termin_schedule_id / transaction_details_id)'
            ]);
        }

        if (!request('termin_schedule_id') && !request('transaction_details_id')) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Isi salah satu (termin_schedule_id / transaction_details_id)'
            ]);
        }

        if (request('termin_schedule_id')) {
            // Initialize
            $terminSchedule = CourseTerminSchedule::where('id', request('termin_schedule_id'))->first();

            if (!$terminSchedule) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Data dengan id ('.request('termin_schedule_id').') tidak ditemukan.'
                ]);
            }
        } else {
            // Initialize
            $transactionDetails = TransactionDetails::where('id', request('transaction_details_id'))->first();

            if (!$transactionDetails) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Data dengan id ('.request('transaction_details_id').') tidak ditemukan.'
                ]);
            }
        }

        // Initialize
        $files = request('file');

        // Initialize
        $existsData = (new TerminScheduleBast)->newQuery();

        if (request('termin_schedule_id')) {
            $existsData->where('course_termin_schedule_id', $terminSchedule->id);
        } else {
            $existsData->where('transaction_details_id', $transactionDetails->id);
        }

        $bastData    = $existsData->first();
        $developIsOn = false;

        if ($bastData && !$developIsOn) {
            return response()->json([
                'status'    => 'success',
                'message'   => 'Anda sudah mengupload BAST sebelumnya.'
            ]);
        }

        // Check Has the buyer paid for the order
        if ($terminSchedule) {
            if ($terminSchedule->transactionDetails && !$developIsOn) {
                if ($terminSchedule->transactionDetails->transaction->invoice->status != 1) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Buyer belum melakukan pembayaran.'
                    ]);
                }
            }
        } else {
            if ($transactionDetails->transaction->invoice && !$developIsOn && $transactionDetails->transaction->invoice->status != 1) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Buyer belum melakukan pembayaran.'
                ]);
            }
        }

        if ($files) {
            // Validation
            foreach($files as $key => $val) {
                // Initialize
                $detalisFile  = request()->file('file')[$key];
                $extFT        = $detalisFile->getClientOriginalExtension();
                $originalName = $detalisFile->getClientOriginalName();
                $maxSize      = $detalisFile->getSize();

                if ($maxSize > 5000000) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Ukuran file ('.$originalName.') melebihi batas yang ditentukan. Maksimal ukuran file 5 MB'
                    ]);

                    break;
                }
            }

            // Create Data
            $terminScheduleBast = TerminScheduleBast::create([
                'course_termin_schedule_id' => (request('termin_schedule_id')) ? request('termin_schedule_id') : null,
                'transaction_details_id'    => (request('transaction_details_id')) ? request('transaction_details_id') : null
            ]);

            if ($terminScheduleBast) {
                foreach($files as $key => $val) {
                    // Initialize
                    $detalisFile = request()->file('file')[$key];
                    $extFT       = $detalisFile->getClientOriginalExtension();
                    $maxSize     = $detalisFile->getSize();

                    // Upload File
                    $path = $detalisFile->store('uploads/termin-schedule/bast', 'public');

                    if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
                        $fileType = 1;
                    } else if ($extFT == 'mp4' || $extFT == 'mkv') {
                        $fileType = 2;
                    } else {
                        $fileType = 3;
                    }

                    // Create Data
                    MediaTerminScheduleBast::create([
                        'termin_schedule_bast_id' => $terminScheduleBast->id,
                        'path'                    => env('SITE_URL').'/storage/'.$path,
                        'description'             => request('description')[$key],
                        'file_type'               => $fileType,
                        'file_extension'          => $extFT
                    ]);
                }
            }

            // Notification for admin apps, buyer and platinume member by buyer area
            if (request('termin_schedule_id')) {
                $param = $terminSchedule;
                
                $this->_notificationsTermin($param);
            } else {
                $param = $transactionDetails;

                $this->_notificationsTtrans($param);
            }

            return response()->json([
                'status'    => 'success',
                'message'   => 'Berhasil menambahkan data.',
                'data'      => $terminScheduleBast
            ]);
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Gagal menambahkan data. (File harus diisi)',
        ]);
    }

    private function _notificationsTermin($terminSchedule)
    {
        // For Admin Apps
        $message    = 'Seller dari toko '.auth()->user()->company->Name.' telah melakukan Report Progress untuk ('.$terminSchedule->description.') di Invoice (#INV-'.$terminSchedule->transactionDetails->transaction->invoice_id.')';
        $receiverId = '0';
        $title      = 'Report Progress';
        $code       = '90';
        $data       = [
                        'transaction_id' => $terminSchedule->transactionDetails->transaction_id
                      ];
        $icon       = '';

        // Get Admin Apps
        $users = User::where('is_admin_access', 1)->get();

        foreach($users as $val) {
            // Initialize
            $recipient = $val;
            
            Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
        }

        // For Buyer
        $recipient = User::where('id', $terminSchedule->user_id)->first();
        
        Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));

        // For Platinum Member
        $checkPlatinumMember = Company::where(['status' => 1, 'city_id' => $terminSchedule->transactionDetails->transaction->company->city_id])->first();

        if ($checkPlatinumMember) {
            if ($checkPlatinumMember->ID != auth()->user()->company_id) {
                // Initialize
                $recipient = User::where('id', $checkPlatinumMember->user->id)->first();

                if ($recipient) {
                    Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
                }
            }
        }
    }

    private function _notificationsTtrans($transactionDetails)
    {
        // For Admin Apps
        $message    = 'Seller dari toko '.auth()->user()->company->Name.' telah melakukan Report Progress untuk Invoice (#INV-'.$transactionDetails->transaction->invoice_id.')';
        $receiverId = '0';
        $title      = 'Report Progress';
        $code       = '90';
        $data       = [
                        'transaction_id' => $transactionDetails->transaction->id
                      ];
        $icon       = '';

        // Get Admin Apps
        $users = User::where('is_admin_access', 1)->get();

        foreach($users as $val) {
            // Initialize
            $recipient = $val;
            
            Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
        }

        // For Buyer
        $recipient = User::where('id', $transactionDetails->transaction->invoice->user_id)->first();
        
        Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));

        // For Platinum Member
        $checkPlatinumMember = Company::where(['status' => 1, 'city_id' => $transactionDetails->transaction->company->city_id])->first();

        if ($checkPlatinumMember) {
            if ($checkPlatinumMember->ID != auth()->user()->company_id) {
                // Initialize
                $recipient = User::where('id', $checkPlatinumMember->user->id)->first();

                if ($recipient) {
                    Notification::send($recipient, new GlobalNotification($receiverId, $recipient, $title, $code, $message, $data, $icon));
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Check Exists Data
        $data = TerminScheduleBast::with('mediaTerminScheduleBast')->where('course_termin_schedule_id', $id)->first();

        if (!$data) {
            // Check By Transaction Id
            $data = TerminScheduleBast::with('mediaTerminScheduleBast')->where('transaction_details_id', $id)->first();

            if (!$data) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda belum melakukan Upload Bast untuk ID ('.$id.')'
                ]);
            }
        }

        // Initialize
        $row['id']                          = $data->id;
        $row['course_termin_schedule_id']   = $data->course_termin_schedule_id;
        $row['transaction_details_id']      = $data->transaction_details_id;
        $row['status']                      = statusBast($data->status);
        $row['status_code']                 = $data->status;
        $row['reason_to_complain']          = $data->reason_to_complain;
        $row['media_termin_schedule_bast']  = $data->mediaTerminScheduleBast;

        $data = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Check Data
        $data = MediaTerminScheduleBast::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'description' => 'required'
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
        $path = $data->path;

        if (request('file')) {
            // Destroy File
            $explodePath = explode('/', $data->path);
            
            @unlink('storage/uploads/termin-schedule/bast/'.$explodePath[7]);

            // Initialize
            $detalisFile = request()->file('file');
            $extFT       = $detalisFile->getClientOriginalExtension();
            $maxSize     = $detalisFile->getSize();

            // Upload File
            $path = $detalisFile->store('uploads/termin-schedule/bast', 'public');

            if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
                $fileType = 1;
            } else if ($extFT == 'mp4' || $extFT == 'mkv') {
                $fileType = 2;
            } else {
                $fileType = 3;
            }

            $path = env('SITE_URL').'/storage/'.$path;
        }

        $data->update([
            'path'          => $path,
            'description'   => request('description')
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengubah data.',
            'data'    => $data
        ]);
    }

    public function storeFile($id)
    {
        // Check Data
        $data = TerminScheduleBast::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'file'          => 'required',
            'description'   => 'required'
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
        $detalisFile = request()->file('file');
        $extFT       = $detalisFile->getClientOriginalExtension();
        $maxSize     = $detalisFile->getSize();

        // Upload File
        $path = $detalisFile->store('uploads/termin-schedule/bast', 'public');

        if ($extFT == 'jpg' || $extFT == 'jpeg' || $extFT == 'png') {
            $fileType = 1;
        } else if ($extFT == 'mp4' || $extFT == 'mkv') {
            $fileType = 2;
        } else {
            $fileType = 3;
        }

        // Create Data
        $mtsb = MediaTerminScheduleBast::create([
            'termin_schedule_bast_id' => $data->id,
            'path'                    => env('SITE_URL').'/storage/'.$path,
            'description'             => request('description'),
            'file_type'               => $fileType,
            'file_extension'          => $extFT
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $mtsb
        ]);
    }

    public function destroyFile($id)
    {
        // Check Data
        $data = MediaTerminScheduleBast::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Destroy File
        $explodePath = explode('/', $data->path);
        
        @unlink('storage/uploads/termin-schedule/bast/'.$explodePath[7]);

        $data->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Check Data
        $data = TerminScheduleBast::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        foreach($data->mediaTerminScheduleBast as $val) {
            // Destroy File
            $explodePath = explode('/', $val->path);
            
            @unlink('storage/uploads/termin-schedule/bast/'.$explodePath[7]);

            // Delete Data
            $val->delete();
        }

        $data->delete();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id' => $id
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        // Check Data
        $data = TerminScheduleBast::where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'status' => 'required|integer|in:1,2'
        ]);

        if (request('status') == 2) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'reason_to_complain' => 'required'
            ]);
        }

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Initialize
        $data->update([
            'status'                => request('status'),
            'reason_to_complain'    => (request('reason_to_complain')) ? request('reason_to_complain') : null
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil diperbarui.',
            'data'      => $data
        ]);
    }

    public function automaticApprove()
    {
        // Initialize
        $date  = date('Y-m-d', strtotime(date('Y-m-d').'+3 day'));
        $datas = TerminScheduleBast::where(['status' => 0])->get();

        foreach($datas as $val) {
            $date = new Carbon($val->created_at);

            if ($date->diffIndays() > 3) {
                $val->update([
                    'status'             => 1,
                    'reason_to_complain' => null
                ]);
            }
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil update data'
        ]);
    }
}
