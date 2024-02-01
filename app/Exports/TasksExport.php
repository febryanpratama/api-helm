<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TasksExport implements FromView
{
    public $start_date, $end_date, $user_id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($start_date, $end_date, $user_id)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->user_id = $user_id;
    }

    public function view(): View
    {
        // dd($this->start_date);
        $tasks = \App\Task::whereHas('assignedBy', function($q){
            $q->where('company_id', auth()->user()->company_id);
        })->get();
        if ($this->user_id) {
            $user = \App\User::find(request()->get('user_id'));
            $tasks = $user->tasks()->get();
        }

        return view('excel.task', [
            'tasks' => $tasks
        ]);
    }
}
