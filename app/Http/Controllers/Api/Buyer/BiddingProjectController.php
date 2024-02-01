<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BiddingProject;
use App\Project;
use App\Invoice;
use App\InvoiceAddress;
use App\Address;
use App\MasterLocation;
use App\Transaction;
use App\TransactionDetails;
use App\CourseTerminSchedule;
use Validator;

// Paginate
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BiddingProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $projectId = request('project_id');

        // Check Project
        $project = Project::where('id', $projectId)->first();

        if (!$project) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Projek dengan ID ('.$projectId.') tidak ditemukan.'
            ]);
        }

        if ($project->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk projek ini.'
            ]);
        }

        $biddingProjects = BiddingProject::where(['project_id' => request('project_id')])->where('status', '!=', 3)->latest()->get();
        $listData        = $this->paginate($biddingProjects, 20, null, ['path' => $request->fullUrl()]);
        $data            = [];

        foreach($listData as $val) {
            // Initialize
            $row['id']              = $val->id;
            $row['price']           = $val->price;
            $row['price_rupiah']    = rupiah($val->price);
            $row['service_date']    = $val->service_date;
            $row['status']          = biddingStatus($val->status);
            $row['status_code']     = $val->status;
            $row['termin']          = $val->termin;
            $row['project']         = $val->project_json;
            $row['user']            = $val->user;

            // Check Address
            $address = Address::where(['user_id' => $val->user_id, 'main_address' => 1])->first();

            if (!$address) {
                $address = Address::where(['user_id' => $val->user_id])->first();
            }
            
            $row['address_id']      = ($address) ? $address->id : null;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $listData->currentPage(),
                'from'              => 1,
                'last_page'         => $listData->lastPage(),
                'next_page_url'     => $listData->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $listData->perPage(),
                'prev_page_url'     => $listData->previousPageUrl(),
                'total'             => $listData->total()
            ]
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
            'bidding_project_id' => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // Check Bidding Project
        $biddingProject = BiddingProject::where('id', request('bidding_project_id'))->first();

        if (!$biddingProject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Penawaran Projek dengan ID ('.request('bidding_project_id').') tidak ditemukan.'
            ]);
        }

        // Check Owner Project
        if ($biddingProject->project->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        // Check Status Bidding
        if ($biddingProject->status != 0) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Hanya data Bidding yang berstatus (Menunggu Persetujuan) yang bisa di Approve.'
            ]);
        }

        // Check Json Data
        $biddingProjectJson = $biddingProject->project_json;

        // Validation for Product
        if ($biddingProjectJson['is_service'] == 0) {
            // Validation
            $validator = Validator::make(request()->all(), [
                'expedition'                        => 'required',
                'expedition_service'                => 'required',
                'expedition_service_description'    => 'required',
                'expedition_shipping_cost'          => 'required',
                'expedition_etd'                    => 'required'
            ]);

            if ($validator->fails()) {
                $data = [
                    'status'    => 'error',
                    'message'   => $validator->errors()->first(),
                    'code'      => 400
                ];

                return response()->json($data, 400);
            }
        }

        // Insert To Database
        $requestData    = request()->all();
        $checkAddress   = $this->_checkAddress($biddingProjectJson['address_id']);

        if (!$checkAddress) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat dengan ID ('.$biddingProjectJson['address_id'].') tidak ditemukan.'
            ]);
        }

        $configPayment  = $this->_configPayment($biddingProject, $requestData, $biddingProjectJson);
        $invoice        = $this->_invoice($configPayment);
        
        if ($invoice) {
            $invoiceAddress = $this->_invoiceAddress($invoice, $checkAddress);

            // Create To Transaction Table
            $transaction = Transaction::create([
                'store_id'              => $biddingProject->user->company_id,
                'invoice_id'            => $invoice->id,
                'total_payment'         => ($configPayment['total_pay'] - $configPayment['unique_code']),
                'status'                => 1,
                'expedition'            => (isset($requestData['expedition'])) ? $requestData['expedition'] : null,
                'service'               => (isset($requestData['expedition'])) ? $requestData['expedition_service'] : null,
                'service_description'   => (isset($requestData['expedition'])) ? $requestData['expedition_service_description'] : null,
                'shipping_cost'         => (isset($requestData['expedition'])) ? $requestData['expedition_shipping_cost'] : null,
                'etd'                   => (isset($requestData['expedition'])) ? $requestData['expedition_etd'] : null,
                'service_date'          => $biddingProject->service_date,
                'project_id'            => $biddingProject->project->id
            ]);

            if ($transaction) {
                // Create To Transaction Details Table
                $transactionDetails = $this->insertTransactionDetails($transaction, 1, $biddingProject, $checkCart = null, $biddingProject->service_date, $biddingProjectJson);

                if ($transactionDetails) {
                    // Create To Termin Schedule Table
                    $terminFormula = terminBidding($biddingProject->id);

                    foreach ($terminFormula as $index => $ft) {
                        // Check Payment Method
                        $isVerifiedTermin = 0;

                        $courseTerminS = CourseTerminSchedule::create([
                            'user_id'                       => auth()->user()->id,
                            'course_transaction_detail_id'  => $transactionDetails->id,
                            'description'                   => $ft['description'],
                            'value'                         => $ft['value_num'],
                            'interest'                      => $ft['interest'],
                            // 'due_date'                      => date('Y-m-d', strtotime($ft['due_date'])),
                            'due_date'                      => null, // sementara settingan yg terbaru jadwal nya null
                            'termin_percentage'             => $ft['termin_percentage'],
                            'completion_percentage'         => $ft['completion_percentage'],
                            'completion_percentage_detail'  => $ft['completion_percentage_detail'],
                            'due_date_description'          => $ft['due_date_description'],
                            'duedate_number'                => $ft['duedate_number'],
                            'duedate_name'                  => $ft['duedate_name'],
                            'is_verified'                   => ($index == 0) ? $isVerifiedTermin : 0, 
                            'is_percentage'                 => $ft['is_percentage'],
                        ]);
                    }

                    // Update Bidding Status
                    BiddingProject::where('project_id', $biddingProject->project_id)->update([
                        'status' => 2
                    ]);

                    $biddingProject->update(['status' => 1]);
                }

                // Update Status Project
                Project::where('id', $biddingProject->project_id)->update(['status' => 1]);
                
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Berhasil menambahkan data.',
                    'data'      => $invoice
                ]);
            }
        }

        return response()->json([
            'status'    => 'error',
            'message'   => 'Gagal menambahkan data.'
        ]);
    }

    private function _configPayment($biddingProject, $requestData, $biddingProjectJson) {
        // Initialize
        $totals = $biddingProject->price;

        if ($biddingProject->termin) {
            if ($biddingProject->termin->is_percentage == 1) { // with percenteage
                $totals = ($biddingProject->termin->down_payment/100) * $biddingProject->termin->installment_amount;
                $totals = $totals * 1;
            } else { // with nominal
                $totals = $biddingProject->termin->down_payment;
                $totals = $totals * 1;
            }
        }

        // Initialize - Payment
        $bank               = $biddingProjectJson['bank_name'];
        $noRek              = $biddingProjectJson['no_rek'];
        $uniqueCode         = $this->checkUniqueCode();
        $status             = 0;
        $shippingCost       = (isset($requestData['expedition_shipping_cost'])) ? $requestData['expedition_shipping_cost'] : 0;
        $totalPay           = ($totals + $shippingCost + $uniqueCode);
        $totalPayOriginal   = ($totals + $shippingCost);
        $paymentType        = $biddingProjectJson['payment_type'];
        $transactionFees    = 0;
        $totalShipping      = $shippingCost;
        
        return [
            'bank'               => $bank,
            'no_rek'             => $noRek,
            'total_pay'          => $totalPay,
            'total_pay_original' => $totalPayOriginal,
            'payment_type'       => $paymentType,
            'transaction_fees'   => $transactionFees,
            'total_shipping'     => $totalShipping,
            'unique_code'        => $uniqueCode,
            'status'             => $status,
            'is_service'         => $biddingProjectJson['is_service']
        ];
    }

    private function checkUniqueCode()
    {
        // Initialize
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');

        // Check Exists Unique Code
        $uniqueCodeExists = Invoice::where([
                            'unique_code' => $uniqueCode,
                            'status'      => 0
                        ])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode       = rand(100, 1000);
                $uniqueCodeExists = Invoice::where([
                                        'unique_code' => $uniqueCode,
                                        'status'      => 0
                                    ])
                                    ->whereDate('expired_transaction', '>=', $nowDate)
                                    ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        return $uniqueCode;
    }

    private function _checkAddress($addressId)
    {
        $address = Address::where('id', $addressId)->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat pengiriman tidak ditemukan.'
            ]);
        }

        // Check Master Location
        $masterLocation = MasterLocation::where('id', $address->district_id)->first();

        return [
            'address'         => $address,
            'master_location' => $masterLocation
        ];
    }

    private function _invoice($configPayment)
    {
        // Initialize
        $invoice = Invoice::create([
            'user_id'                => auth()->user()->id,
            'total_payment'          => $configPayment['total_pay'],
            'total_payment_original' => $configPayment['total_pay_original'],
            'payment_type'           => $configPayment['payment_type'],
            'total_shipping_cost'    => $configPayment['total_shipping'],
            'transaction_fees'       => $configPayment['transaction_fees'],
            'bank_name'              => $configPayment['bank'],
            'no_rek'                 => $configPayment['no_rek'],
            'unique_code'            => $configPayment['unique_code'],
            'status'                 => $configPayment['status'],
            'is_bidding'             => 1,
            'is_service'             => $configPayment['is_service'],
            'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse'))
        ]);

        return $invoice;
    }

    private function _invoiceAddress($invoice, $checkAddress)
    {
        // Initialize
        $invoiceAddress = InvoiceAddress::create([
            'invoice_id'        => $invoice['id'],
            'address_id'        => $checkAddress['address']['id'],
            'province'          => $checkAddress['master_location']['provinsi'],
            'city'              => $checkAddress['master_location']['kota'],
            'district'          => $checkAddress['master_location']['kecamatan'],
            'address_type'      => $checkAddress['master_location']['type'],
            'details_address'   => $checkAddress['address']['details_address']
        ]);

        return $invoiceAddress;
    }

    private function insertTransactionDetails($transaction, $qty, $biddingProject, $checkCart = '', $serviceDate = null, $biddingProjectJson)
    {
        // Initialize
        $transactionDetails = TransactionDetails::create([
            'transaction_id'              => $transaction->id,
            'course_id'                   => null,
            'course_name'                 => $biddingProjectJson['name'],
            'course_detail'               => $biddingProjectJson['description'],
            'thumbnail'                   => null,
            'thumbnail_path'              => null,
            'price_course'                => $biddingProject->price,
            'discount'                    => null,
            'slug'                        => null,
            'course_package_category'     => $biddingProjectJson['is_service'],
            'period_day'                  => null,
            // 'start_time_min'              => $biddingProjectJson['start_date_time'],
            // 'end_time_min'                => $biddingProjectJson['end_date_time'],
            'back_payment_status'         => 0,
            'is_immovable_object'         => 0,
            'course_category'             => $biddingProjectJson['category_id'],
            'price_course_after_discount' => 0,
            'qty'                         => $qty,
            'weight'                      => $biddingProjectJson['weight'],
            'category_detail_inputs'      => ($checkCart) ? $checkCart->category_detail_inputs : null,
            'service_date'                => $serviceDate
        ]);

        return $transactionDetails;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Initialize
        $biddingProject = BiddingProject::with('project','termin','user')->where('id', $id)->first();

        if (!$biddingProject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Penawaran Projek dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        // Check Address
        $address = Address::where(['user_id' => $biddingProject->user_id, 'main_address' => 1])->first();

        if (!$address) {
            $address = Address::where(['user_id' => $biddingProject->user_id])->first();
        }

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Penawar ('.$biddingProject->user->name.') belum memiliki alamat.'
            ]);
        }

        // Initialize
        $row['id']              = $biddingProject->id;
        $row['user_id']         = $biddingProject->user_id;
        $row['project_id']      = $biddingProject->project_id;
        $row['price']           = $biddingProject->price;
        $row['price_rupiah']    = rupiah($biddingProject->price);
        $row['service_date']    = $biddingProject->service_date;
        $row['status']          = $biddingProject->status;
        $row['created_at']      = $biddingProject->created_at;
        $row['updated_at']      = $biddingProject->updated_at;
        $row['user']            = $biddingProject->user;
        $row['address_id']      = $address->id;
        $row['termin']          = $biddingProject->termin;
        $row['project']         = $biddingProject->project_json;

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
        // Check Bidding Project
        $biddingProject = BiddingProject::where('id', $id)->first();

        if (!$biddingProject) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Penawaran Projek dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        if ($biddingProject->project->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses untuk mengubah penawaran ini.'
            ]);
        }

        $biddingProject->update(['status' => 2]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $biddingProject
        ]);
    }

    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
