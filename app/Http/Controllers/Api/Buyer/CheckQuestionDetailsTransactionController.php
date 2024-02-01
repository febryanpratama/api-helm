<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\TransactionAutocomplete;

class CheckQuestionDetailsTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $data            = [];
        $selectQuestions = $this->_query(1);

        foreach ($selectQuestions as $val) {
            // Initialize
            $row['id']              = $val->id;
            $row['category_id']     = $val->category_id;
            $row['value']           = $val->value;
            $row['metric_length']   = $val->metric_length;
            $row['data_type']       = $val->data_type;

            // Roots Question
            $roots = TransactionAutocomplete::where('parent', $val->id)->get();
            
            $rootsData = [];
            foreach($roots as $root) {
                // Initialize
                $rowRoot['value']         = $root->value;
                $rowRoot['data_type']     = $root->data_type;
                $rowRoot['metric_length'] = $root->metric_length;

                $rootsData[] = $rowRoot;
            }

            $row['root'] = $rootsData;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // Initialize
        $data            = [];
        $selectQuestions = $this->_query(0);

        foreach ($selectQuestions as $val) {
            // Initialize
            $row['id']              = $val->id;
            $row['category_id']     = $val->category_id;
            $row['value']           = $val->value;
            $row['metric_length']   = $val->metric_length;
            $row['data_type']       = $val->data_type;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    private function _query($option)
    {
        if (request('value')) {
            // Initialize
            $selectQuestions = DB::table('category_transaction_autocomplete')
                            ->join('list_transaction_autocomplete',
                                    'list_transaction_autocomplete.id',
                                    '=',
                                    'category_transaction_autocomplete.list_transaction_autocomplete_id')
                            ->select('*')
                            ->where('category_transaction_autocomplete.category_id', request('category_id'))
                            ->where('list_transaction_autocomplete.fixed_item', $option)
                            ->where('list_transaction_autocomplete.value', 'LIKE', '%'.request('value').'%')
                            ->get();
        } else {        
            // Initialize
            $selectQuestions = DB::table('category_transaction_autocomplete')
                            ->join('list_transaction_autocomplete',
                                    'list_transaction_autocomplete.id',
                                    '=',
                                    'category_transaction_autocomplete.list_transaction_autocomplete_id')
                            ->select('*')
                            ->where('category_transaction_autocomplete.category_id', request('category_id'))
                            ->where('list_transaction_autocomplete.fixed_item', $option)
                            ->get();
        }

        return $selectQuestions;
    }
}