<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Subject;

class EmbedToAppsController extends Controller
{
    public function index($subjectId)
    {
        // Initialize
        $subject = Subject::where('ID', $subjectId)->first();

        return view('embed-to-apps.index', compact('subject'));
    }
}
