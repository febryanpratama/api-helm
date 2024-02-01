<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\HintWidget;

class HintWidgetController extends Controller
{
    public function store()
    {
        HintWidget::create([
            'user_id' => auth()->user()->id,
            'page'    => (request('page')) ? request('page') : 'create-course-package'
        ]);

        return response()->json([
            'status' => true
        ]);
    }
}
