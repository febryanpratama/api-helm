<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\AttendanceTasksExport;
use App\Exports\TasksExport;
use Illuminate\Http\Request;
use Excel;
use App\TodoActivity;
use App\HintWidget;
use App\UserDivision;

class ReportsController extends Controller
{

    public function index()
    {
        return view('admin.report');
    }

    public function attendance()
    {
        // dd(request()->all());
        $attendance = \App\Attendance::whereHas('user', function($q){
            $q->where('company_id', auth()->user()->company_id);
        })->orderBy('id', 'desc')->whereNotNull('check_out_datetime')->paginate(5);
        if (request()->has('search') && request()->search == '1') {
            if (request()->start_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->whereDate('check_in_datetime', '>=', request()->start_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->end_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->start_date != '' && request()->end_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->whereDate('check_in_datetime', '>=', request()->start_date)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->user_id) {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->where('user_id', request()->user_id)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->start_date != '' && request()->user_id) {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->where('user_id', request()->user_id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->end_date != '' && request()->user_id) {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->where('user_id', request()->user_id)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->start_date != '' && request()->end_date != '' && request()->user_id) {
                $attendance = \App\Attendance::orderBy('id', 'desc')->whereHas('user', function($q){
                    $q->where('company_id', auth()->user()->company_id);
                })
                ->where('user_id', request()->user_id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }
        }

        $user = \App\User::whereIn('role_id', [6,8])->where('company_id', auth()->user()->company_id)->get();

        // dd($attendance);
        return view('admin.report_attendance', compact('attendance', 'user'));
    }

    public function userAttendance(\App\User $user)
    {
        // dd(request()->all());
        $attendance = \App\Attendance::orderBy('id', 'desc')->where('user_id', $user->id)->whereNotNull('check_out_datetime')->paginate(5);
        if (request()->has('search') && request()->search == '1') {
            if (request()->start_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->where('user_id', $user->id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->end_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->where('user_id', $user->id)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }

            if (request()->start_date != '' && request()->end_date != '') {
                $attendance = \App\Attendance::orderBy('id', 'desc')->where('user_id', $user->id)->whereDate('check_in_datetime', '>=', request()->start_date)->whereDate('check_out_datetime', '<=', request()->end_date)->whereNotNull('check_out_datetime')->paginate(5);
            }
        }

        // dd($attendance);
        return view('admin.report_attendance_user', compact('attendance', 'user'));
    }

    public function downloadExcel()
    {
        $param_start = null;
        $param_end = null;
        $param_user_id = null;
        if (request()->start_date != '') {
            $param_start = request()->start_date;
        }

        if (request()->end_date != '') {
            $param_end = request()->end_date;
        }

        if (request()->user_id != '') {
            $param_user_id = request()->user_id;
        }

        return Excel::download(new AttendanceExport($param_start, $param_end, $param_user_id), 'attendance.xlsx');
    }

    public function task()
    {
        $tasks = \App\Task::whereHas('assignedBy', function($q){
                $q->where('company_id', auth()->user()->company_id);
            })->paginate(5);
        if (request()->has('user_id')) {
            $user = \App\User::find(request()->get('user_id'));
            $tasks = $user->tasks()->paginate(5);
        }
        $user = \App\User::whereIn('role_id', [6,8])->where('company_id', auth()->user()->company_id)->get();
        return view('admin.report_task', compact('tasks', 'user'));
    }

    public function downloadExcelTask()
    {
        $param_start = null;
        $param_end = null;
        $param_user_id = null;
        // if (request()->start_date != '') {
        //     $param_start = request()->start_date;
        // }

        // if (request()->end_date != '') {
        //     $param_end = request()->end_date;
        // }

        if (request()->user_id != '') {
            $param_user_id = request()->user_id;
        }

        return Excel::download(new TasksExport($param_start, $param_end, $param_user_id), 'tasks.xlsx');
        
    }

    public function reportTaskAttendance()
    {
        // Initialize
        $user           = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->get();
        $memberActivity = TodoActivity::latest()->get();
        $startDate      = request('start_date_activity');
        $endDate        = request('end_date_activity');
        $userId         = request('user_id_activity');
        $memberAcTotal  = count(TodoActivity::whereDate('created_at', '>=', date('Y-m-d 00:00:00'))->latest()->get());

        // Filter Division
        if (request('division')) {
            // Initialize
            $divisionId = UserDivision::where('division_id', request('division'))->pluck('user_id');
            $user       = \App\User::whereIn('role_id', [6,8,1,2])
                        ->whereIn('id', $divisionId)
                        ->where('company_id', auth()->user()->company_id)
                        ->get();
        }

        if (request('member-activity')) {
            // Initialize
            $divisionId     = UserDivision::where('division_id', request('member-activity'))->pluck('user_id');
            $memberActivity = TodoActivity::whereIn('user_id', $divisionId)->latest()->get();
        }

        if (request()->has('search') && request()->search == '1') {
            if (request()->user_id) {
                $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->where('id', request()->user_id)->get();
            }

            if ($startDate && $endDate && $userId) {
                $memberActivity = TodoActivity::where('user_id', $userId)
                                ->whereDate('created_at', '>=', $startDate)
                                ->whereDate('created_at', '<=', $endDate)
                                ->latest()
                                ->get();
            } elseif ($startDate && $endDate) {
                $memberActivity = TodoActivity::whereDate('created_at', '>=', $startDate)
                                ->whereDate('created_at', '<=', $endDate)
                                ->latest()
                                ->get();
            } elseif ($startDate && $userId) {
                $memberActivity = TodoActivity::where('user_id', $userId)
                                ->whereDate('created_at', '>=', $startDate)
                                ->latest()
                                ->get();
            } elseif ($endDate && $userId) {
                $memberActivity = TodoActivity::where('user_id', $userId)
                                ->whereDate('created_at', '<=', $endDate)
                                ->latest()
                                ->get();
            } elseif ($startDate) {
                $memberActivity = TodoActivity::whereDate('created_at', '>=', $startDate)->latest()->get();
            } elseif ($endDate) {
                $memberActivity = TodoActivity::whereDate('created_at', '<=', $endDate)->latest()->get();
            } elseif ($userId) {
                $memberActivity = TodoActivity::where('user_id', $userId)->latest()->get();
            }
        }

        // Hint Widget
        $hintWidgets = HintWidget::where(['user_id' => auth()->user()->id, 'widget' => 'reports_page'])->count();

        // return view('admin.report_task_attendance', compact('user'));
        return view('admin.reports.index', compact('user', 'memberActivity', 'hintWidgets', 'memberAcTotal'));
    }

    public function downloadExcelAttendanceTask()
    {
        // Initialize
        $param_start    = null;
        $param_end      = null;
        $param_user_id  = null;
        $membersId      = '';

        // Check Type
        if (request('type') == 'aNumberOf') {
            $membersId = request('membersId');
        }

        if (request()->start_date != '') {
            $param_start = request()->start_date;
        }

        if (request()->end_date != '') {
            $param_end = request()->end_date;
        }

        if (request()->user_id != '') {
            $param_user_id = request()->user_id;
        }

        return Excel::download(new AttendanceTasksExport($param_start, $param_end, $param_user_id, $membersId), 'report.xlsx');
    }

    public function detialReportTaskAttendance(\App\User $user)
    {

        if (request()->has('search') && request()->search == '1') {

            if (request()->user_id) {
                $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->where('id', request()->user_id)->get();
            }
        }

        // dd($attendance);
        return view('admin.detail_report_task_attendance', compact('user'));
    }

    public function taskListByUser(\App\User $user)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $user->TasksToDay($user->id)
        ]);
    }

    public function attendanceCountDay(\App\User $user)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $user->attendanceCount($user->id)
        ]);
    }

    public function attendanceByUser(\App\User $user)
    {
        // Check Search
        if (request('startDate') || request('endDate')) {
            $attendances = $user->searchAttendances(request('startDate'), request('endDate'), request('userId'));
        } else {
            $attendances = \App\Attendance::where('user_id', $user->id)->latest()->get();
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $attendances
        ]);
    }

    public function hintWidget()
    {
        // Validate Hint Widget
        $widget = HintWidget::where(['user_id' => auth()->user()->id, 'step' => request('step')])->first();

        if ($widget) {
            return response()->json([
                'status'    => false,
                'message'   => 'data exists'
            ]);

            die;
        }

        HintWidget::create([
            'user_id'   => auth()->user()->id,
            'step'      => request('step'),
            'widget'    => request('widget')
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'success'
        ]);
    }
}
