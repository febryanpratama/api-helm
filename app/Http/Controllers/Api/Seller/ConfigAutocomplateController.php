<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

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
        $autocomplates = DB::table('list_title_autocomplete')
                        ->leftJoin('category_title_autocomplete', 'list_title_autocomplete.id', '=', 'category_title_autocomplete.list_title_autocomplete_id')
                        ->where('category_title_autocomplete.category_id', request('category_id'))
                        ->where('list_title_autocomplete.name','LIKE','%'.request('search').'%')
                        ->get();

        // Initialize
        $data = [];

        foreach($autocomplates as $val) {
            $row['id']          = $val->id;
            $row['category_id'] = $val->category_id;
            $row['prefix']      = $val->prefix;
            $row['name']        = $val->name;
            $row['prefix_name'] = ($val->prefix) ? $val->prefix.' - '.$val->name : $val->name;

            $data[] = $row;
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }
}
