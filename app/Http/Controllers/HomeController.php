<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Str;

class HomeController extends Controller
{
    public function index()
    {
        // Check Role
        if (auth()->user()->role_id == '1') {
            if (auth()->user()->company && !auth()->user()->company->facebook) {
                $redirect = Str::slug(auth()->user()->company->Name).'/company/edit?company='.auth()->user()->company->ID;

                return redirect($redirect);
            }
        }

        return view('home.index');
    }
}
