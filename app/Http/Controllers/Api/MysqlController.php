<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class MysqlController extends Controller
{
    public function index()
    {
        // Initialize
        $data = DB::table('users')
                ->leftJoin('company','company.ID','=','users.company_id')
                ->leftJoin('course','course.user_id','=','users.id')
                ->select(
                    'users.name as seller',
                    'users.company_id',
                    'company.Name as store',
                    DB::raw('count(course.id) as total_product, product')
                )
                ->where('product', 0)
                ->groupBy('users.company_id')
                ->get();

        dd($data);
    }
}
