<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Unit;

class UnitController extends Controller
{
    public function index()
    {
        // Initialize
        $units = Unit::orderBy('name', 'ASC')->get();
        
        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data Unit.',
            'data'      => $units
        ]);
    }
}
