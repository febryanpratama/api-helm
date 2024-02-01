<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App;

class LangController extends Controller
{
    public function switchLang($lang, Request $request)
    {
        // Change Lang
        Session::put('applocale', $lang);
     
        return redirect()->back();
    }
}
