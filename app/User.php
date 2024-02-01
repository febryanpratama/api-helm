<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Musonza\Chat\Traits\Messageable;
use Chat;
use Laravel\Passport\HasApiTokens;
use Str;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use Messageable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     'name', 'email', 'password', 'role_id', 'nip', 'phone', 'supervised_by', 'is_active', 'company_id', 'is_propose', 'is_demo',
    // ];
    
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'otp',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['role_name'];

    public function tasks($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id');
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%');
        }
        return $result;
    }

    public function projectTasks($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users');
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->where('name', 'like', '%'.$search.'%');
        }
        return $result;
    }

    public function taskDeadline($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users')->whereBetween(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), [date('Y-m-d'), date('Y-m-d', strtotime('+3 days', strtotime(date('Y-m-d'))))]);
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->where('name', 'like', '%'.$search.'%')->whereBetween(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), [date('Y-m-d'), date('Y-m-d', strtotime('+3 days', strtotime(date('Y-m-d'))))]);
        }
        return $result;
    }

    public function taskNew($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users')->orderBy('created_at', 'desc');
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->where('name', 'like', '%'.$search.'%')->orderBy('created_at', 'desc');
        }
        return $result;
    }

    public function taskOld($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users')->orderBy('created_at', 'asc');
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->where('name', 'like', '%'.$search.'%')->orderBy('created_at', 'asc');
        }
        return $result;
    }

    public function taskDone($search = null)
    {
        $result = $this->belongsToMany(Task::class, 'tasks_users');
        if ($search) {
            $result = $this->belongsToMany(Task::class, 'tasks_users')->where('name', 'like', '%'.$search.'%');
        }
        return $result;
    }

    public function TasksToDay($userId)
    {
        // Initialize
        $todos        = Todo::where('assigned_to', $userId)->pluck('id');
        $todoActivity = TodoActivity::whereIn('todo_id', $todos)->get();
        $data         = [];

        foreach($todoActivity as $todo) {
            // Initialize
            $endDateToDo = date('Y-m-d', strtotime($todo->updated_at));
            $todayDate   = date('Y-m-d');
            $row         = [];

            // Check Selesai To Do
            if ($endDateToDo == $todayDate) {
                // Check Status To Do
                if ($todo->status == 'done') {
                    $row['id']      = $todo->id;
                    $row['todo']    = $todo->todo->todo;

                    if ($todo->todo->task) {
                        // Initialize
                        $taskDetail  = $todo->todo->task;
                        $percentage  = 0;

                        if (!empty($taskDetail->isDone()) && count($taskDetail->todos) > 0) {
                            $percentage = (count($taskDetail->isDone())/count($taskDetail->todos)) * 100;
                        }

                        $row['task']        = $taskDetail->name;
                        $row['percentage']  = ceil($percentage);
                    }

                    $row['is_done_time']    = date('d F Y H:i', $todo->is_done_time);

                    // Initialize
                    $taskId    = $todo->todo->task_id;
                    $doneTodos = Todo::where(['task_id' => $taskId, 'is_done' => 'y'])->latest()->paginate(5);
                    $html      = '';

                    foreach($doneTodos as $doneTodo) {
                        // Check Background Color
                        $bgC = 'rgb(54, 168, 217)';

                        if (!empty($doneTodo->task->project)) {
                            $bgC = $doneTodo->task->project->background_color;
                        }

                        $html .= '<div class="col-md-6">
                                        <div class="card text-white mb-2 mt-2">
                                            <div id="'.$doneTodo->id.'" class="card-body cursor-area" style="background-color: '.$bgC.'">
                                                <h6>'.Str::limit($doneTodo->todo, 13).'</h6>
                                            </div>
                                        </div>          
                                    </div>';
                    }

                    $row['todos'] = $html;

                    $data[] = $row;
                }
            }
        }

        // Sort Array
        $desc = rsort($data);

        return $data;
    }

    public function allTasks()
    {
        return $this->belongsToMany(Task::class, 'tasks_users');
    }

    public function searchTasks($search = null)
    {
        return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->get();
    }

    public function searchTasksDate($search = null, $start_date = null, $end_date = null)
    {
        if ($start_date && $end_date) {
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`start_date`))'), '<=', $start_date)->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), '>=', $end_date)->get();
        }
        if ($start_date) {
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`start_date`))'), '>=', $start_date)->get();
        }
        if ($end_date) {
            // dd($end_date);
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), '<=', $end_date)->get();
        }
    }

    public function searchTasksDateApi($search = null, $start_date = null, $end_date = null)
    {
        if ($start_date && $end_date) {
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`start_date`))'), '<=', $start_date)->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), '>=', $end_date);
        }
        if ($start_date) {
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`start_date`))'), '>=', $start_date);
        }
        if ($end_date) {
            // dd($end_date);
            return $this->belongsToMany(Task::class, 'tasks_users')->whereNull('project_id')->where('name', 'like', '%'.$search.'%')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`end_date`))'), '<=', $end_date);
        }
    }

    public function summaryTasks()
    {
        return $this->belongsToMany(Task::class, 'tasks_users')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`start_date`))'), '>=', \Carbon\Carbon::now()->toDateString());
    }

    public function memos()
    {
        return $this->belongsToMany(Memo::class, 'memos_users');
    }

    public function memoPeriods()
    {
        return $this->belongsToMany(Memo::class, 'memos_users')->where('start_period', '<=', date('Y-m-d'))->where('end_period', '>=', date('Y-m-d'));
    }

    public function checked()
    {
        return $this->hasMany(UserCheck::class, 'user_id')->orderBy('id', 'desc')->first();
    }

    public function totalCheckToday()
    {
        return $this->hasMany(UserCheck::class, 'user_id')->where('created_at', '>=', \Carbon\Carbon::now()->toDateString())->orderBy('id', 'desc');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'projects_users', 'user_client_id', 'project_id');
    }

    public function listUserConversation()
    {
        $list_conversation = Chat::conversations()->setPaginationParams(['sorting' => 'desc'])
        ->setParticipant($this)
        ->get();

        return $list_conversation;
    }

    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'IDClient');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendance($userId)
    {
        // Initialize
        $attendance = Attendance::where('user_id', $userId)->latest()->first();
        $todayDate  = date('Y-m-d');

        if ($attendance) {
            // Initiailze
            $checkIn = date('Y-m-d', strtotime($attendance->check_in_datetime));

            // Check Date
            if ($checkIn == $todayDate) {
                return $attendance;
            }

            return false;
        }

        return false;
    }

    public function attendanceCount($userId)
    {
        // Initialize
        $date         = date('Y-m-d');
        $todayDate    = date('d');
        $latestDate   = date('t', strtotime($date));
        $startDate    = date('Y-m-01 00:00:00');
        $endDate      = date('Y-m-'.$latestDate.' 00:00:00');

        $attendance   = Attendance::where('user_id', $userId)->whereDate('check_in_datetime', '>=', $startDate)
                        ->whereDate('check_in_datetime', '<=', $endDate)->latest()->get();
        $present      = count($attendance);
        $notPresent   = 0;

        // Notes :
        // Get Total Tidak Hadir
        if ($todayDate == $latestDate) {
            $notPresent = ($latestDate - 10) - $present;
        }

        // Initialize
        $data['present']     = $present;
        $data['not_present'] = $notPresent;

        return $data;
    }

    public function searchAttendances($start = null, $end = null, $user_id = null)
    {
        $attendance = $this->attendances()->orderBy('id', 'desc')->get();
        if ($start) {
            $attendance = $this->attendances()->orderBy('id', 'desc')->whereDate('check_in_datetime', '>=', $start)->get();
        }

        if ($end) {
            $attendance = $this->attendances()->orderBy('id', 'desc')->whereDate('check_out_datetime', '<=', $end)->get();
        }

        if ($start && $end) {
            $attendance = $this->attendances()->orderBy('id', 'desc')->whereDate('check_in_datetime', '>=', $start)->whereDate('check_out_datetime', '<=', $end)->get();
        }

        if ($user_id) {
            $attendance = $this->attendances()->orderBy('id', 'desc')
            ->where('user_id', $user_id)->get();
        }

        if ($start && $user_id) {
            $attendance = $this->attendances()->orderBy('id', 'desc')
            ->where('user_id', $user_id)->whereDate('check_in_datetime', '>=', $start)->get();
        }

        if ($end && $user_id) {
            $attendance = $this->attendances()->orderBy('id', 'desc')
            ->where('user_id', $user_id)->whereDate('check_out_datetime', '<=', $end)->get();
        }

        if ($start && $end && $user_id) {
            $attendance = $this->attendances()->orderBy('id', 'desc')
            ->where('user_id', $user_id)->whereDate('check_in_datetime', '>=', $start)->whereDate('check_out_datetime', '<=', $end)->get();
        }

        return $attendance;
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }

    public function hintWidget()
    {
        return $this->hasMany(HintWidget::class, 'user_id');
    }

    public function checkDivision()
    {
        // Initialize
        $status   = false;
        $division = Division::where('IDCompany', auth()->user()->company->ID)->first();

        if ($division) {
            $status = true;
        }

        return $status;
    }
    
    public function division()
    {
        return $this->belongsToMany(Division::class, 'user_division', 'user_id');
    }

    public function majors()
    {
        return $this->belongsToMany(Majors::class, 'user_majors', 'user_id', 'major_id');
    }

    public function userDvision()
    {
        return $this->belongsToMany(Division::class, 'user_division', 'user_id', 'division_id')->where('users.id', auth()->user()->id);
    }

    public function userMajor()
    {
        return $this->belongsToMany(Majors::class, 'user_majors', 'user_id', 'major_id')->where('user_id', auth()->user()->id);
    }

    public function getDivisionMajorsSubject()
    {
        $division = $this->whereHas('userDvision')->with('division')->get();

        $majors = $this->whereHas('userMajor')->get();

        if ($division) {
            foreach ($division as $key => $value) {
                foreach ($value->division as $k => $v) {
                    $subject[] = $v->subject;
                }
            }
    
            if (isset($subject[0])) {
                // Initialize
                $data = [];
                
                foreach ($subject[0] as $key => $value) {
                    $row[] = $value->ID;

                    // $data[] = $row;
                    array_push($data, $value->ID);
                }

                return $data;
            }

        }

        if ($majors) {
            foreach ($majors as $key => $value) {
                foreach ($value->majors as $k => $v) {
                    # code...
                    $subject[] = $v->subject;
                }
            }
    
            if (isset($subject[0])) {
                // dd($subject[0]);
                $data = [];
                foreach ($subject[0] as $key => $value) {
                    $data[] = $value->ID;
                    array_push($data, $value->ID);
                }
                return $data;
            }
        }

        return array();
    }

    public function getDivisionMajorsSubjectData()
    {
        $division = $this->whereHas('userDvision')->with('division')->get();

        $majors = $this->whereHas('userMajor')->get();

        if ($division) {
            foreach ($division as $key => $value) {
                foreach ($value->division as $k => $v) {
                    # code...
                    $subject[] = $v->subject;
                }
            }
    
            if (isset($subject[0])) {
                // dd($subject[0]);

                foreach ($subject[0] as $key => $value) {
                    $data[] = $value;
                }
                return $data;
            }

        }

        if ($majors) {
            foreach ($majors as $key => $value) {
                foreach ($value->majors as $k => $v) {
                    # code...
                    $subject[] = $v->subject;
                }
            }
    
            if (isset($subject[0])) {
                // dd($subject[0]);
                foreach ($subject[0] as $key => $value) {
                    $data[] = $value;
                }
                return $data;
            }
        }

        return array();

    }

    public function getRoleNameAttribute()
    {
        $role = \App\Roles::find($this->role_id);
        return $role->Name;
    }

    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function userCourse()
    {
        return $this->hasMany(UserCourse::class);
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function address()
    {
        return $this->hasMany(Address::class);
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }

    public function course()
    {
        return $this->hasMany(Course::class);
    }
}
