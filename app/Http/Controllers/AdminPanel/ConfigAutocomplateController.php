<?php

namespace App\Http\Controllers\AdminPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ListTitleAutocomplete;
use App\Category;
use App\CategoryTitleAutocomplete;
use App\TransactionAutocomplete;
use App\CategoryTransactionAutocomplete;

class ConfigAutocomplateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Initialize
        $category = Category::orderBy('name', 'ASC')->get();

        return view('admin-panel.config-autocomplate.index', compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listAutocomplete()
    {
        // Initialize
        $autocomplates = ListTitleAutocomplete::orderBy('prefix', 'ASC')->get();
        $selected      = CategoryTitleAutocomplete::where('category_id', request('categoryId'))->pluck('list_title_autocomplete_id');

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia.',
            'data'      => [
                'autocomplates' => $autocomplates,
                'selected'      => $selected
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
        // Initialize
        $categoryId     = request('categoryId');
        $autocomplates  = request('autocompleteId');

        // Delete all data where categoryId
        CategoryTitleAutocomplete::whereIn('category_id', $categoryId)->delete();

        foreach($categoryId as $category) {
            foreach($autocomplates as $val) {
                CategoryTitleAutocomplete::create([
                    'category_id'                   => $category,
                    'list_title_autocomplete_id'    => $val
                ]);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menambahkan data.',
            'data'      => [
                'category_id' => $categoryId
            ]
        ]);
    }

    public function indexTransaction()
    {
        // Initialize
        $category = Category::orderBy('name', 'ASC')->get();

        return view('admin-panel.config-autocomplate.index-transaction', compact('category'));
    }

    public function listAutocompleteTransaction()
    {
        // Initialize
        $autocomplates = TransactionAutocomplete::orderBy('value', 'ASC')->where('side', 0)->get();
        $selected      = CategoryTransactionAutocomplete::where('category_id', request('categoryId'))->pluck('list_transaction_autocomplete_id');

        foreach($autocomplates as $val) {
            // Initialize
            $row['id']              = $val->id;
            $row['value']           = $val->value;
            $row['metric_length']   = $val->metric_length;
            $row['side']            = $val->side;
            $row['data_type']       = $val->data_type;

            // Get Root
            $roots = TransactionAutocomplete::where('parent', $val->id)->get();

            $rootsData = [];
            foreach($roots as $root) {
                // Initialize
                $rowRoot['value']         = $root->value;
                $rowRoot['data_type']     = $root->data_type;
                $rowRoot['metric_length'] = $root->metric_length;

                $rootsData[] = $rowRoot;
            }

            $row['root']            = $rootsData;
            $row['data_type']       = $val->data_type;

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'autocomplates' => $data,
                'selected'      => $selected
            ]
        ]);
    }

    public function storeTransaction()
    {
        // Initialize
        $categoryId     = request('categoryId');
        $autocomplates  = request('autocompleteId');

        // Delete all data where categoryId
        CategoryTransactionAutocomplete::whereIn('category_id', $categoryId)->delete();

        foreach($categoryId as $category) {
            foreach($autocomplates as $val) {
                CategoryTransactionAutocomplete::create([
                    'category_id'                      => $category,
                    'list_transaction_autocomplete_id' => $val
                ]);
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menambahkan data.',
            'data'      => [
                'category_id' => $categoryId
            ]
        ]);
    }
}
