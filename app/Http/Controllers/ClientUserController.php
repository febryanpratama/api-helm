<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientUserController extends Controller
{
    public function index()
    {
        $project = auth()->user()->projects;
        return view('client.index', compact('project'));
    }

    public function detailProject(\App\Project $project)
    {
        // dd($project->tasks);
        return view('client.detail_project', compact('project'));
    }
}
