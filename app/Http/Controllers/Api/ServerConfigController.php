<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServerConfigController extends Controller
{
    public function date()
    {
        // Initialize
        $dateTimeFormat12 = date('Y-m-d H:i:s');
        $dateTimeFormat24 = date('Y-m-d h:i:s');
        $numberFormat     = strtotime($dateTimeFormat24);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => [
                'format_12_jam' => $dateTimeFormat12,
                'format_24_jam' => $dateTimeFormat24,
                'number_format' => $numberFormat
            ]
        ]);
    }
}
