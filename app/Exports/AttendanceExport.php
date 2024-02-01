<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AttendanceExport implements FromView
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return \App\Attendance::all();
    // }

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
        $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->whereNotNull('check_out_datetime')->get();
        if ($this->start_date) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->whereDate('check_in_datetime', '>=', $this->start_date)->whereNotNull('check_out_datetime')->get();
        }

        if ($this->end_date) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->whereDate('check_out_datetime', '<=', $this->end_date)->whereNotNull('check_out_datetime')->get();
        }

        if ($this->start_date && $this->end_date) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->whereDate('check_in_datetime', '>=', $this->start_date)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->get();
        }

        if ($this->user_id) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->where('user_id', $this->user_id)->whereNotNull('check_out_datetime')->paginate(5);
        }

        if (request()->start_date != '' && $this->user_id) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->where('user_id', $this->user_id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereNotNull('check_out_datetime')->paginate(5);
        }

        if (request()->end_date != '' && $this->user_id) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->where('user_id', $this->user_id)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
        }

        if (request()->start_date != '' && request()->end_date != '' && $this->user_id) {
            $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })
            ->where('user_id', $this->user_id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
        }

        return view('excel.attendance', [
            'attendance' => $attendance
        ]);
    }
}
