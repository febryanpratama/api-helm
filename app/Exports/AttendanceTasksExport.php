<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceTasksExport implements FromView
{
    public $start_date, $end_date, $user_id, $membersId;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($start_date, $end_date, $user_id, $membersId)
    {
        $this->start_date = $start_date;
        $this->end_date   = $end_date;
        $this->user_id    = $user_id;
        $this->membersId  = $membersId;
    }

    public function view(): View
    {

        $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->get();

        if ($this->user_id) {
            $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->where('id', $this->user_id)->get();
        }

        if ($this->membersId) {
            $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->whereIn('id', $this->membersId)->get();
        }

        return view('excel.task_attendance', [
            'user' => $user,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'user_id' => $this->user_id,
        ]);
    }
}
