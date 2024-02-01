<?php

namespace App\Http\Controllers\Api\Seller\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\InventoryPurchasesPaymentHistory;
use App\InventoryPurchases;
use App\Account;
use App\Journal;
use App\BeginBalance;
use App\Course;
use Validator;

class PaymentHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $data      = [];
        $inventory = InventoryPurchasesPaymentHistory::where('inventory_purchases_id', request('inventory_purchases_id'))->latest()->paginate(10);
            
        foreach ($inventory as $val) {
            $row['id']                      = $val->id;
            $row['inventory_purchases_id']  = $val->inventory_purchases_id;
            $row['description']             = $val->description;
            $row['value']                   = $val->value;
            $row['value_rupiah']            = rupiah($val->value);
            $row['inventory_purchases']     = $val->inventoryPurchases;
            $row['created_at']              = $val->created_at;
            $row['updated_at']              = $val->updated_at;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $inventory->currentPage(),
                'from'              => 1,
                'last_page'         => $inventory->lastPage(),
                'next_page_url'     => $inventory->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $inventory->perPage(),
                'prev_page_url'     => $inventory->previousPageUrl(),
                'total'             => $inventory->total()
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
        // Check Inventory Purchases
        $inventoryPurchases = InventoryPurchases::where('id', request('inventory_purchases_id'))->first();

        if (!$inventoryPurchases) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Pembelian Tidak Langsung dengan ID ('.request('inventory_purchases_id').')'
            ]);
        }

        // Validation
        $validator = Validator::make(request()->all(), [
            'inventory_purchases_id'    => 'required|integer',
            'value'                     => 'required|integer'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data);
        }

        // Check Begin Balance
        $beginBalance = BeginBalance::where('StartPeriod', '<=', date('Y-m-d'))->where('EndPeriod', '>=', date('Y-m-d'))->first();

        if (!$beginBalance) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Begin Balance tidak tersedia untuk tanggal '.date('d F Y').' Silahkan buat data di fitur Begin Balance.'
            ]);
        }
        
        try {
            $data = InventoryPurchasesPaymentHistory::create([
                'inventory_purchases_id' => request('inventory_purchases_id'),
                'description'            => request('description'),
                'value'                  => request('value')
            ]);

            if ($data) {
                // Insert to Journals
                $this->_manageDataJournal($beginBalance, $inventoryPurchases, $data, $request);
            }

            $response = [
                'status'    => 'success',
                'message'   => 'Pembayaran berhasil disimpan.',
                'data'      => $data
            ];
        } catch (\Throwable $e) {
            $response = [
                'status'    => 'error',
                'message'   => $e->getMessage().' At Line '.$e->getLine()
            ];
        }

        return response()->json($response);
    }

    private function _manageDataJournal($beginBalance, $inventoryPurchases, $data, $request)
    {
        // Initialize
        if ($beginBalance && $beginBalance->Method == 0) { // Perpetual
            $this->indirectPerpetual($beginBalance, $inventoryPurchases, $data, $request);
        } elseif ($beginBalance && $beginBalance->Method == 1) { // Periodik
            $this->indirectPeriodik($beginBalance, $inventoryPurchases, $data, $request);
        }
    }

    private function indirectPerpetual($beginBalance, $inventoryPurchases, $data, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Cash in Hand')->first();

        // Remaining Payment
        $remainingPayment = InventoryPurchasesPaymentHistory::where('inventory_purchases_id', $inventoryPurchases->id)->sum('value');

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $debit1         = array();
            $debit_json     = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $credit1         = array();
            $credit_json     = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($inventoryPurchases->total_payment - $inventoryPurchases->first_payment) - $remainingPayment; // Remaining Payment
            $debit1['account']      = $res_acc_debit1;
            $debit_json[]           = $debit1;

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = ($inventoryPurchases->total_payment - $inventoryPurchases->first_payment) - $remainingPayment; // Remaining Payment
            $credit1['account'] = $res_acc_credit1;
            $credit_json[]      = $credit1;

            // Docs
            $doc['no']   = $inventoryPurchases->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembayaran Kas Tidak Langsung|'.$inventoryPurchases->id.'|Pembayaran',
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }

        return true;
    }

    private function indirectPeriodik($beginBalance, $inventoryPurchases, $data, $request)
    {
        // Check Account
        $accountDebit1  = Account::where('CurrType', 'Account Payable')->first();
        $accountCredit1 = Account::where('CurrType', 'Cash in Hand')->first();

        // Remaining Payment
        $remainingPayment = InventoryPurchasesPaymentHistory::where('inventory_purchases_id', $inventoryPurchases->id)->sum('value');

        if ($accountDebit1 && $accountCredit1) {
            // Initialize
            // --- DEBIT
            $res_acc_debit1 = array();
            $debit1         = array();
            $debit_json     = array();
            // --- DEBIT

            // --- CREDIT
            $res_acc_credit1 = array();
            $credit1         = array();
            $credit_json     = array();
            // --- CREDIT

            $res_doc = array();

            // Debit 1
            $acc_debit1['id']       = $accountDebit1->ID;
            $acc_debit1['name']     = $accountDebit1->Name;
            $acc_debit1['code']     = $accountDebit1->Code;
            $acc_debit1['group']    = $accountDebit1->group;
            $acc_debit1['type']     = $accountDebit1->CurrType;
            $res_acc_debit1[]       = $acc_debit1;

            $debit1['id']           = $accountDebit1->ID;
            $debit1['value']        = ($inventoryPurchases->total_payment - $inventoryPurchases->first_payment) - $remainingPayment; // Remaining Payment
            $debit1['account']      = $res_acc_debit1;
            $debit_json[]           = $debit1;

            // Credit 1
            $acc_credit1['id']      = $accountCredit1->ID;
            $acc_credit1['name']    = $accountCredit1->Name;
            $acc_credit1['code']    = $accountCredit1->Code;
            $acc_credit1['group']   = $accountCredit1->group;
            $acc_credit1['type']    = $accountCredit1->CurrType;

            $res_acc_credit1[] = $acc_credit1;

            $credit1['id']      = $accountCredit1->ID;
            $credit1['value']   = ($inventoryPurchases->total_payment - $inventoryPurchases->first_payment) - $remainingPayment; // Remaining Payment
            $credit1['account'] = $res_acc_credit1;
            $credit_json[]      = $credit1;

            // Docs
            $doc['no']   = $inventoryPurchases->id;
            $doc['file'] = null;
            $res_doc[]   = $doc;

            $journalData = [
                'IDCompany'     => auth()->user()->company_id,
                'IDCurrency'    => 0,
                'Rate'          => 1,
                'JournalType'   => 'general',
                'JournalDate'   => date('Y-m-d'),
                'JournalName'   => 'Pembayaran Kas Tidak Langsung|'.$inventoryPurchases->id.'|Pembayaran',
                'JournalDocNo'  => $res_doc,
                'json_debit'    => $debit_json,
                'json_credit'   => $credit_json,
                'AddedTime'     => time(),
                'AddedBy'       => auth()->user()->id,
                'AddedByIP'     => $request->ip()
            ];

            Journal::create($journalData);
        }

        return true;
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
        $data = InventoryPurchasesPaymentHistory::with('InventoryPurchases')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

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
        $data = InventoryPurchasesPaymentHistory::with('InventoryPurchases')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        $data->update([
            'description' => request('description'),
            'value'       => request('value')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $data
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
        $data = InventoryPurchasesPaymentHistory::with('InventoryPurchases')->where('id', $id)->first();

        if (!$data) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Data dengan ID ('.$id.') tidak ditemukan.'
            ]);
        }

        $data->delete();

        return response()->json([
            'status'    => 'error',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $id
            ]
        ]);
    }
}
