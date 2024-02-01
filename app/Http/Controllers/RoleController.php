<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Roles;

class RoleController extends Controller
{
    public function show()
    {
        // Initialize
        $roles = Roles::whereIn('id', ['6','8','9'])->get();
        
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $roles
        ]);
    }
}
