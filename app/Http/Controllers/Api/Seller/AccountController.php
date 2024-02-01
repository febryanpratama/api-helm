<?php

namespace App\Http\Controllers\Api\Seller;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Validator;

class AccountController extends Controller
{
    private function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        // Initialize
        $page  = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function listGroup(Request $request)
    {
        $arr = [
            array(
                'name' => 'Assets'
            ),
            array(
                'name' => 'Assets >> Current Assets'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Down Payment'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Cash and Cash Equivalents'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Account Receivable'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Inventory'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Investment'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Prepaid Tax'
            ),
            array(
                'name' => 'Assets >> Current Assets >> Prepaid Expenses'
            ),
            array(
                'name' => 'Assets >> Fixed Assets'
            ),
            array(
                'name' => 'Assets >> Intangible Assets'
            ),
            array(
                'name' => 'Assets >> Other Assets'
            ),
            array(
                'name' => 'Liabilities'
            ),
            array(
                'name' => 'Liabilities >> Current Liabilities'
            ),
            array(
                'name' => 'Liabilities >> Other Current Liabilities'
            ),
            array(
                'name' => 'Liabilities >> Long Term Liabilities'
            ),
            array(
                'name' => 'Liabilities >> Other Liabilities'
            ),
            array(
                'name' => 'Equities'
            ),
            array(
                'name' => 'Income'
            ),
            array(
                'name' => 'Cost of Good Sold'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process >> Raw Material Cost'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process >> Semi Material Cost'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process >> WIP Cost'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process >> Direct Labour Cost'
            ),
            array(
                'name' => 'Cost of Good Sold >> Work In Process >> Factory Overhead Cost'
            ),
            array(
                'name' => 'Operation Expenses'
            ),
            array(
                'name' => 'Other Income'
            ),
            array(
                'name' => 'Extraordinary Income'
            ),
            array(
                'name' => 'Other Expenses'
            ),
            array(
                'name' => 'Extraordinary Expenses'
            ),
        ];

        $res = $arr;

        $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'list group',
            'data' => $res
        ];

        return response()->json($data, 200);
    }

    public function listType(Request $request)
    {
        $array="ASSETS;Current Assets;Down Payment;Cash and Cash Equivalents;Cash in Hand;Cash in Bank;Deposit;Marketable Securities;Account Receivable;Allowance for Bad Debts;Inventory;Inventory SM;Inventory RM;Inventory WIP;Inventory CG;Inventory FG;Inventory Waste;Investment;Prepaid Tax;VAT In;Income Tax Art 22;Income Tax Art 23;Income Tax Art 25;Income Tax Art 4 Ay. 2;Prepaid Expenses;Prepaid Insurance;Prepaid Advertised;Fixed Assets;Land;Building;Accumulated Depreciation Building;Vehicles;Accumulated Depreciation Vehicles;Office Equipment;Accumulated Depreciation Office Equipment;Intangible Assets;Amortization;Patent;Other Assets;Pra Operation Expenses;LIABILITIES;Current Liabilities;Account Payable;Foreign Account Payable;Tax Payable;Expenses Payable;Short Term Bank Payable;Other Current Liabilities;Non Effort Curent Liabilities;Long Term Liabilities;Bank Loan;Other Liabilities;EQUITIES;Common Stock;Retained Earning;Retained Earning Current Year;Deviden;Difference in Paid Up Capital;Income Summary;INCOME;Sales Inventory;Sales Discount;Sales Return;COST OF GOOD SOLD;COGS Inventory;Work In Process;Raw Material Cost;Direct Labour Cost;Semi Material Cost;Factory Overhead Cost;Work In Process - Raw Material Cost;Work In Process - WIP Cost;Work In Process - Direct Labour Cost;Work In Process - Semi Material Cost;Work In Process - Overhead Cost;Indirect Labour Cost;Factory Supplies;Electricity;Depreciation;Insurance;Cleanliness and Factory Maintenance;Sparepart;Water;Purchase Discount;Purchase Return;Purchase Freight;Raw Material Used;Raw Material Begin;Raw Material Purchase;Raw Material End;Overview of Raw Material Used;Direct Labor;Direct Factory Labor;Factory Employee Severance;Overhead Factory;Cost of Supporting Material;Factory Supplies;Factory Rent;Electricity;BBM,Parking,Tol Expenses;Depreciation Expenses;Insurance;Import Expenses;Cleanliness and Factory Maintenance;PNBP and Bea Cukai;Catering;Sparepart;Factory Uniform;Industrial Zones and Water;Packing;Medicine and Medical;Repair and Maintenance;Vehicles;Subcon;Gasoline and Chemical;Rent;Other Factory Expenses;Work In Process;Work In Process Begin;Work In Process End;Overview Work In Process;Finished Good;Finished Good Begin;Finished Good Purchase;Finished Good End;Overview Finished Good;OPERATION EXPENSES;Operation and General;Depreciation Office Supplies;Professional Service;Administration;Holiday Parcel;Sparepart;Entertainment;Jamsostek;Office and General Needs;Eating and Drinking Employees;Sales;Rent;Photocopy;Depreciation Vehicles Expenses;Document and Permits;Calibration;Other Office Expenses;Salaries Expenses;Advertised Expenses;Freight Expenses;Honorarium & Comission Expenses;Travelling Expenses;BBM;Supplies Expenses;Electricity & Telephon;Repair & Maintenance Vehicle Expenses;Depreciation Expenses;Insurance Expenses;Advertised Expenses;OTHER INCOME;Interest Revenue;Rent Revenue;Foreign Exchange Differences;Interest Income;Profit of Assets Sales;Profit for Release of Debt;Non Effort Other Income;EXTRAORDINARY INCOME;Extraordinary Income;OTHER EXPENSES;Bank Service Expenses;Tax Expenses;Interest Expenses;Loss of Assets Sales;Non Effort Other Expenses;EXTRAORDINARY EXPENSES;Extraordinary Expenses;Insurance Claim Income;Loss of Natural Disasters;Loss of Riot;Prizes/Donations/Lottery";
        $currtype=explode(";",$array);	
        foreach ($currtype as $key => $value) {
            $type['name'] = $value;
            $res[] = $type;
        }

        $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'list type',
            'data' => $res
        ];

        return response()->json($data, 200);
    }

    public function index(Request $request)
    {
        // Initialize
        $account = Account::orderBy('ID', 'desc')->where('is_delete', 0)->get();

        if ($request->search != '') {
            $account = Account::orderBy('ID', 'desc')->where('Name', 'like', '%'. $request->search . '%')->where('is_delete', 0)->get();
        }

        // Custom Paginate
        $account = $this->paginate($account, 20, null, ['path' => $request->fullUrl()]);
        $data       = [];

        foreach ($account as $val) {
            // Initialize
            $row['id'] = $val->ID;
            $row['name'] = $val->Name;
            $row['other_name'] = $val->OtherName;
            $row['code'] = $val->Code;
            $row['group'] = $val->group;
            $row['type'] = $val->CurrType;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data Account.',
            'data'      => $data,
            'meta'      => [
                'current_page'      => $account->currentPage(),
                'from'              => 1,
                'last_page'         => $account->lastPage(),
                'next_page_url'     => $account->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $account->perPage(),
                'prev_page_url'     => $account->previousPageUrl(),
                'total'             => $account->total()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'other_name' => 'required|string',
            'code' => 'required|numeric',
            'group' => 'nullable|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $type = strtolower($request->type);
        $type = ucwords($type);
        if ($type != 'Assets' && $type != 'Liabilities' && $type != 'Equities' && $type != 'Income' && $type != 'Cost Of Good Sold' && $type != 'Operation Expenses' && $type != 'Other Income' && $type != 'Other Expenses') {
            // Validation
            $validator = Validator::make(request()->all(), [
                'group' => 'required|string',
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

        // INIT
        $group1 = null;
        $group2 = null;
        $group3 = null;
        $group4 = null;

        $level1 = null;
        $level2 = null;
        $level3 = null;
        $level4 = null;

        $group_exp = explode(' >> ', $request->group);

        if (isset($group_exp[0])) {
            $group1 = $group_exp[0];
        }
        if (isset($group_exp[1])) {
            $group2 = $group_exp[1];
        }
        if (isset($group_exp[2])) {
            $group3 = $group_exp[2];
        }
        if (isset($group_exp[3])) {
            $group4 = $group_exp[3];
        }

        if ($group1) {
            $level1 =  Account::where('Name', 'LIKE', $group1)->first();
            if ($level1) {
                $level1 = $level1->ID;
            }
        }
        if ($group2) {
            $level2 =  Account::where('Name', 'LIKE', $group2)->first();
            if ($level2) {
                $level2 = $level2->ID;
            }
        }
        if ($group3) {
            $level3 =  Account::where('Name', 'LIKE', $group3)->first();
            if ($level3) {
                $level3 = $level3->ID;
            }
        }
        if ($group4) {
            $level4 =  Account::where('Name', 'LIKE', $group4)->first();
            if ($level4) {
                $level4 = $level4->ID;
            }
        }

        $account = Account::create([
            'Name' => $request->name,
            'OtherName' => $request->other_name,
            'Code' => $request->code,
            'Type1' => $group1,
            'Type2' => $group2,
            'Type3' => $group3,
            'Type4' => $group4,
            'Level1' => $level1,
            'Level2' => $level2,
            'Level3' => $level3,
            'Level4' => $level4,
            'CurrType' => $request->type,
            'is_delete' => 0,
            'AddedTime'     => time(),
            'AddedBy'       => auth()->user()->id,
            'AddedByIP'     => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 201,
            'message'   => 'Berhasil disimpan',
            'data'    => $account,
        ], 201);
    }

    public function show(Account $account)
    {
        $row['id'] = $account->ID;
        $row['name'] = $account->Name;
        $row['code'] = $account->Code;
        $row['group'] = $account->group;
        $row['type'] = $account->CurrType;

        $res[] = $row;

        $data = [
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'detail account',
            'data'    => $res
        ];

        return response()->json($data, 200);
    }

    public function update(Request $request, Account $account)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'other_name' => 'required|string',
            'code' => 'required|numeric',
            'group' => 'nullable|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }

        $type = strtolower($request->type);
        $type = ucwords($type);
        if ($type != 'Assets' && $type != 'Liabilities' && $type != 'Equities' && $type != 'Income' && $type != 'Cost Of Good Sold' && $type != 'Operation Expenses' && $type != 'Other Income' && $type != 'Other Expenses') {
            // Validation
            $validator = Validator::make(request()->all(), [
                'group' => 'required|string',
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

        // INIT
        $group1 = null;
        $group2 = null;
        $group3 = null;
        $group4 = null;

        $level1 = null;
        $level2 = null;
        $level3 = null;
        $level4 = null;

        $group_exp = explode(' >> ', $request->group);

        if (isset($group_exp[0])) {
            $group1 = $group_exp[0];
        }
        if (isset($group_exp[1])) {
            $group2 = $group_exp[1];
        }
        if (isset($group_exp[2])) {
            $group3 = $group_exp[2];
        }
        if (isset($group_exp[3])) {
            $group4 = $group_exp[3];
        }

        if ($group1) {
            $level1 =  Account::where('Name', 'LIKE', $group1)->first();
            if ($level1) {
                $level1 = $level1->ID;
            }
        }
        if ($group2) {
            $level2 =  Account::where('Name', 'LIKE', $group2)->first();
            if ($level2) {
                $level2 = $level2->ID;
            }
        }
        if ($group3) {
            $level3 =  Account::where('Name', 'LIKE', $group3)->first();
            if ($level3) {
                $level3 = $level3->ID;
            }
        }
        if ($group4) {
            $level4 =  Account::where('Name', 'LIKE', $group4)->first();
            if ($level4) {
                $level4 = $level4->ID;
            }
        }

        $account->update([
            'Name' => $request->name,
            'OtherName' => $request->other_name,
            'Code' => $request->code,
            'Type1' => $group1,
            'Type2' => $group2,
            'Type3' => $group3,
            'Type4' => $group4,
            'Level1' => $level1,
            'Level2' => $level2,
            'Level3' => $level3,
            'Level4' => $level4,
            'CurrType' => $request->type,
            'EditedTime'    => time(),
            'EditedBy'      => auth()->user()->id,
            'EditedByIP'    => $request->ip()
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dirubah',
            'data'    => $account,
        ], 200);
    }

    public function destroy(Account $account)
    {
        $account->update([
            'is_delete' => 1
        ]);

        return response()->json([
            'status'    => 'success',
            'code'      => 200,
            'message'   => 'Berhasil dihapus',
        ], 200);
    }
}
