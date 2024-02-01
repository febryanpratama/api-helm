<?php

namespace App\Http\Controllers\Api\Seller;

use App\Account;
use App\Http\Controllers\Controller;
use App\Journal;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class JournalController extends Controller
{
    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function index(Request $request)
    {
        // Initialize
        $journal = Journal::where('IDCompany', auth()->user()->company_id)->orderBy('ID', 'DESC')->get();


        // Custom Paginate
        $journal = $this->paginate($journal, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($journal as $val) {
            // Initialize
            $row['id'] = $val->ID;
            $row['company_id'] = $val->IDCompany;
            $row['currency_id'] = $val->IDCurrency;
            $row['rate'] = $val->Rate;
            $row['journal_date'] = $val->JournalDate;
            $row['journal_type'] = $val->JournalType;
            $row['journal_name'] = $val->JournalName;
            $row['journal_doc'] = $val->JournalDocNo;
            $row['debit']       = $val->json_debit;
            $row['credit']       = $val->json_credit;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Journal.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $journal->currentPage(),
                'from'              => 1,
                'last_page'         => $journal->lastPage(),
                'next_page_url'     => $journal->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $journal->perPage(),
                'prev_page_url'     => $journal->previousPageUrl(),
                'total'             => $journal->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'journal_date'          => 'required',
            'journal_type'          => 'required',
            'journal_name'          => 'required',
            'journal_doc_no'        => 'nullable|array',
            'journal_doc_no*'       => 'nullable',
            'journal_doc_file'      => 'nullable|array',
            'journal_doc_file*'     => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'currency_id'           => 'required|integer',
            'rate'                  => 'required|numeric',

            'debit_account_id'          => 'nullable|array',
            'debit_account_id.*'        => 'nullable|integer|exists:accounts,ID',
            'debit_value'               => 'required|array',
            'debit_value.*'             => 'required|numeric',

            'credit_account_id'          => 'nullable|array',
            'credit_account_id.*'        => 'nullable|integer|exists:accounts,ID',
            'credit_value'               => 'required|array',
            'credit_value.*'             => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }


        // CHECK TOTAL DEBIT CREDIT
        $total_debit = array_sum($request->debit_value);
        $total_credit = array_sum($request->credit_value);
        if ($total_debit != $total_credit) {
            $data = [
                'status'    => 'error',
                'message'   => 'Jumlah Total Debit Credit harus sama total Debit yg anda input ' . rupiah($total_debit) . ' & total Credit yang anda input ' . rupiah($total_credit),
                'code'      => 400
            ];
    
            return response()->json($data, 400);
        }

        for ($i=0; $i < count($request->debit_value); $i++) {
            // CHECK SALAH SATU ACCOUNT DEBIT/CREDIT
            if (!$request->debit_account_id[$i] && !$request->credit_account_id[$i]) {
                $data = [
                    'status'    => 'error',
                    'message'   => 'Account Debit Credit harus terisi salah satu',
                    'code'      => 400
                ];
        
                return response()->json($data, 400);
            }

            $debit_account = Account::find($request->debit_account_id[$i]);

            $res_acc = null;
            if ($debit_account) {
                $acc['id'] = $debit_account->ID;
                $acc['name'] = $debit_account->Name;
                $acc['code'] = $debit_account->Code;
                $acc['group'] = $debit_account->group;
                $acc['type'] = $debit_account->CurrType;

                $res_acc[] = $acc;
            }
            $debit['id'] = $request->debit_account_id[$i];
            $debit['value'] = $request->debit_value[$i];
            $debit['account'] = $res_acc;
            $debit_json[] = $debit;

            $credit_account = Account::find($request->credit_account_id[$i]);
            $res_acc_cre = null;
            if ($credit_account) {
                $acc['id'] = $credit_account->ID;
                $acc['name'] = $credit_account->Name;
                $acc['code'] = $credit_account->Code;
                $acc['group'] = $credit_account->group;
                $acc['type'] = $credit_account->CurrType;

                $res_acc_cre[] = $acc;
            }

            $credit['id'] = $request->credit_account_id[$i];
            $credit['value'] = $request->credit_value[$i];
            $credit['account'] = $res_acc_cre;
            $credit_json[] = $credit;
        }

        $res_doc = array();
        for ($i=0; $i < count($request->journal_doc_no); $i++) {
            $doc['no'] = null;
            if (isset($request->journal_doc_no[$i]) && $request->journal_doc_no[$i] != '') {
                $doc['no'] = $request->journal_doc_no[$i];
            }


            $acc_photo = null;
            if (isset($request->journal_doc_file[$i]) && $request->journal_doc_file[$i] != '') {
                $acc_photo = $request->journal_doc_file[$i]->store('uploads/'.auth()->user()->company->Name.'/accounts/photo', 'public');
                $acc_photo = env('SITE_URL').'/storage/'.$acc_photo;
                
            }
            $doc['file'] = $acc_photo;

            $res_doc[] = $doc;
        }


        $journal = Journal::create([
            'IDCompany'             => auth()->user()->company_id,
            'IDCurrency'            => $request->currency_id,
            'Rate'                  => $request->rate,
            'JournalType'           => $request->journal_type,
            'JournalDate'           => $request->journal_date,
            'JournalName'           => $request->journal_name,
            'JournalDocNo'          => $res_doc,
            'json_debit'            => $debit_json,
            'json_credit'           => $credit_json,
            'AddedTime'             => time(),
            'AddedBy'               => auth()->user()->id,
            'AddedByIP'             => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 201,
            'message'   => 'Berhasil disimpan',
            'data'    => $journal,
        ], 201);
    }

    public function show(Journal $journal)
    {
        
        $res['id'] = $journal->ID;
        $res['company_id'] = $journal->IDCompany;
        $res['currency_id'] = $journal->IDCurrency;
        $res['rate'] = $journal->Rate;
        $res['journal_date'] = $journal->JournalDate;
        $res['journal_type'] = $journal->JournalType;
        $res['journal_name'] = $journal->JournalName;
        $res['journal_doc'] = $journal->JournalDocNo;
        $res['debit']       = $journal->json_debit;
        $res['credit']       = $journal->json_credit;

        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'detail journal',
            'data'    => $res
        ];

        return response()->json($data, 200);
    }

    public function update(Journal $journal, Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'journal_date'          => 'required',
            'journal_type'          => 'required',
            'journal_name'          => 'required',
            'journal_doc_no'        => 'nullable|array',
            'journal_doc_no*'       => 'nullable',
            'journal_doc_file'      => 'nullable|array',
            'journal_doc_file*'     => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'currency_id'           => 'required|integer',
            'rate'                  => 'required|numeric',

            'debit_account_id'          => 'nullable|array',
            'debit_account_id.*'        => 'nullable|integer|exists:accounts,ID',
            'debit_value'               => 'required|array',
            'debit_value.*'             => 'required|numeric',

            'credit_account_id'          => 'nullable|array',
            'credit_account_id.*'        => 'nullable|integer|exists:accounts,ID',
            'credit_value'               => 'required|array',
            'credit_value.*'             => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        // CHECK TOTAL DEBIT CREDIT
        $total_debit = array_sum($request->debit_value);
        $total_credit = array_sum($request->credit_value);
        if ($total_debit != $total_credit) {
            $data = [
                'status'    => 'error',
                'message'   => 'Jumlah Total Debit Credit harus sama total Debit yg anda input ' . rupiah($total_debit) . ' & total Credit yang anda input ' . rupiah($total_credit),
                'code'      => 400
            ];
    
            return response()->json($data, 400);
        }

        for ($i=0; $i < count($request->debit_value); $i++) {
            // CHECK SALAH SATU ACCOUNT DEBIT/CREDIT
            if (!$request->debit_account_id[$i] && !$request->credit_account_id[$i]) {
                $data = [
                    'status'    => 'error',
                    'message'   => 'Account Debit Credit harus terisi salah satu',
                    'code'      => 400
                ];
        
                return response()->json($data, 400);
            }

            $debit_account = Account::find($request->debit_account_id[$i]);

            $res_acc = null;
            if ($debit_account) {
                $acc['id'] = $debit_account->ID;
                $acc['name'] = $debit_account->Name;
                $acc['code'] = $debit_account->Code;
                $acc['group'] = $debit_account->group;
                $acc['type'] = $debit_account->CurrType;

                $res_acc[] = $acc;
            }
            $debit['id'] = $request->debit_account_id[$i];
            $debit['value'] = $request->debit_value[$i];
            $debit['account'] = $res_acc;
            $debit_json[] = $debit;

            $credit_account = Account::find($request->credit_account_id[$i]);
            $res_acc_cre = null;
            if ($credit_account) {
                $acc['id'] = $credit_account->ID;
                $acc['name'] = $credit_account->Name;
                $acc['code'] = $credit_account->Code;
                $acc['group'] = $credit_account->group;
                $acc['type'] = $credit_account->CurrType;

                $res_acc_cre[] = $acc;
            }

            $credit['id'] = $request->credit_account_id[$i];
            $credit['value'] = $request->credit_value[$i];
            $credit['account'] = $res_acc_cre;
            $credit_json[] = $credit;
        }

        $res_doc = array();
        for ($i=0; $i < count($request->journal_doc_no); $i++) {
            $doc['no'] = null;
            if (isset($request->journal_doc_no[$i]) && $request->journal_doc_no[$i] != '') {
                $doc['no'] = $request->journal_doc_no[$i];
            }


            $acc_photo = isset($journal->JournalDocNo[$i]) ? $journal->JournalDocNo[$i]['file'] : null;
            if (isset($request->journal_doc_file[$i]) && $request->hasFile($request->journal_doc_file[$i])) {
                $acc_photo = $request->journal_doc_file[$i]->store('uploads/'.auth()->user()->company->Name.'/accounts/photo', 'public');
                $acc_photo = env('SITE_URL').'/storage/'.$acc_photo;
                
            }
            $doc['file'] = $acc_photo;

            $res_doc[] = $doc;
        }

        $journal->update([
            'IDCurrency'            => $request->currency_id,
            'Rate'                  => $request->rate,
            'JournalType'           => $request->journal_type,
            'JournalDate'           => $request->journal_date,
            'JournalName'           => $request->journal_name,
            'JournalDocNo'          => $res_doc,
            'json_debit'            => $debit_json,
            'json_credit'           => $credit_json,
            'EditedTime'            => time(),
            'EditedBy'              => auth()->user()->id,
            'EditedByIP'            => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dirubah',
            'data'    => $journal,
        ], 200);
    }

    public function destroy(Journal $journal)
    {
        $journal->delete();

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dihapus',
        ], 200);
    }
}
