<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\TaskUser;
use App\Todo;
use App\Division;
use App\UserDivision;
use App\Majors;
use App\UserMajors;
use DB;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Chat;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // init search task
        $search = null;
        if (request()->has('search') && request()->get('search') != '') {
            $search = request()->get('search');
        }
        if (request()->has('search_task') && request()->get('search_task') != '') {
            $search = request()->get('search_task');
        }
        if (request()->has('search_project') && request()->get('search_project') != '') {
            $search = request()->get('search_project');
        }
        if (request()->has('search_knowledge') && request()->get('search_knowledge') != '') {
            $search = request()->get('search_knowledge');
        }
        if (request()->has('search_memo') && request()->get('search_memo') != '') {
            $search = request()->get('search_memo');
        }

        $check_company_expired = new \App\Company;

        // config send email next couple week
        if (count($check_company_expired->coupleWeeksToExpired()) > 0) {
            foreach ($check_company_expired->coupleWeeksToExpired() as $key => $value) {
                $check_data = \DB::table('email_expired')->where('company_id', $value->ID)->where('created_at', 'like', '%'.date('Y-m-d').'%')->first();

                if (!$check_data) {
                    $email_expired = \App\EmailExpired::create([
                        'company_id' => $value->ID
                    ]);

                    foreach ($value->admin as $admin) {
                        \Mail::to($admin->email)->send(new \App\Mail\SubscribeExpired($value));
                    }
                }
            }
        }
        // config send email next week
        if (count($check_company_expired->nextWeekToExpired()) > 0) {
            foreach ($check_company_expired->nextWeekToExpired() as $key => $value) {
                $check_data = \DB::table('email_expired')->where('company_id', $value->ID)->where('created_at', 'like', '%'.date('Y-m-d').'%')->first();

                if (!$check_data) {
                    $email_expired = \App\EmailExpired::create([
                        'company_id' => $value->ID
                    ]);
                    foreach ($value->admin as $admin) {
                        \Mail::to($admin->email)->send(new \App\Mail\SubscribeExpired($value));
                    }
                }
            }
        }

        // config send email expired
        if (count($check_company_expired->expired()) > 0) {
            foreach ($check_company_expired->expired() as $key => $value) {
                $check_data = \DB::table('email_expired')->where('company_id', $value->ID)->where('created_at', 'like', '%'.date('Y-m-d').'%')->first();

                if (!$check_data) {
                    $email_expired = \App\EmailExpired::create([
                        'company_id' => $value->ID
                    ]);

                    foreach ($value->admin as $admin) {
                        \Mail::to($admin->email)->send(new \App\Mail\SubscriptionExpired($value));
                    }
                }
            }
        }

        // assigned_by / menugaskan
        $task = \App\Task::where('assigned_by', auth()->user()->id)->get();

        // assigned / ditugaskan (task without project)
        $assigned_task = $this->_assignedTask();
        if (request()->has('search') || request()->has('search_task')) {
            $assigned_task = $this->_assignedTask($search);
        }
        // $assigned_task = auth()->user()->tasks;

        if (request()->has('search_task')) { // search project
            $assigned_task = auth()->user()->searchTasks(request()->get('search_task'));
        }

        if (request()->has('subject') && request()->get('subject') != '') {
            $assigned_task =  $this->_assignedTask($search, request()->get('subject'));
        }

        // dd(request()->get('start_date'));
        if (request()->has('start_date') && request()->get('start_date') != '' || request()->has('end_date') && request()->get('end_date')) {
            // dd(request()->get('end_date'));
            // $assigned_task =  $this->_assignedTask($search, request()->get('subject'));
            $assigned_task = $this->_assignedTask($search, null, request()->get('start_date'), request()->get('end_date'));
        }

        $assigned_task         = $this->paginate($assigned_task, 20,null, ['path' => request()->fullUrl(), 'pageName' => 'task_page'], 'task_page');

        // task priority
        $tasks_priority = auth()->user()->taskDeadline()->take(5)->get();

        if (count($tasks_priority) == 0) {
            $tasks_priority = auth()->user()->taskNew()->take(5)->get();
        }

        if (request()->has('search') && request()->get('search') != '') {
            $tasks_priority = auth()->user()->taskDeadline(request()->get('search'))->take(5)->get();
        }

        // To Do
        $todos = \App\TodoActivity::where(['user_id' => auth()->user()->id, 'status' => 'doing'])->latest()->get();

        $summary_task       = $this->workingOnProgress();
        $message            = \App\Message::all();
        $list_conversation  = auth()->user()->listUserConversation();

        // get all data subject user login
        $subject_id = auth()->user()->getDivisionMajorsSubject();
        
        // get data knowledge
        $category_knowledge = \App\Knowledge::select('category')->groupBy('category')->get();
        $knowledge_public   = \App\Knowledge::where('is_private', 'n')->where('company_id', auth()->user()->company_id)->orderBy('id', 'desc')->get();
        $my_knowledge       = \App\Knowledge::where('is_private', 'y')->where('company_id', auth()->user()->company_id)->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();
        $knowledges         = \App\Knowledge::doesntHave('subjects')->where('company_id', auth()->user()->company_id)->where('is_private', 'n')->orderBy('id', 'desc')->get();

        $my_knowledge_subject         = \App\Knowledge::whereHas('subjects', function($q) use($subject_id) {
            $q->whereIn('subjects.ID', $subject_id);
        })->with('subjects')->where('is_private', 'y')->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->where('company_id', auth()->user()->company_id)->get();

        $knowledge_subject         = \App\Knowledge::whereHas('subjects', function($q) use($subject_id) {
            $q->whereIn('subjects.ID', $subject_id);
        })->with('subjects')->where('is_private', 'n')->orderBy('id', 'desc')->where('company_id', auth()->user()->company_id)->get();


        // Merge Data
        $knowledges         = $knowledges->merge($my_knowledge);
        $knowledges         = $knowledges->merge($my_knowledge_subject);
        $knowledges         = $knowledges->merge($knowledge_subject);
        $knowledges         = $this->paginate($knowledges, 20,null, ['path' => request()->fullUrl(), 'pageName' => 'knowledge_page'], 'knowledge_page');

        if (request()->has('search') || request()->has('search_knowledge')) {
            $knowledges = \App\Knowledge::where('content', 'like', '%'.$search.'%')->where('is_private', 'n')->orderBy('id', 'desc')->paginate(20, ['*'], 'knowledge_page');
        }

        if (request()->has('subject') && request()->get('subject') != '') {
            $subject_id = request()->get('subject');
            $knowledges = \App\Knowledge::whereHas('subjects', function($q) use($subject_id) {
                $q->whereIn('subjects.ID', $subject_id);
            })->where('title', 'like', '%'.$search.'%')->where('company_id', auth()->user()->company_id)->where('is_private', 'n')->orderBy('id', 'desc')->paginate(20, ['*'], 'knowledge_page');
        }

        if (request()->has('category') && request()->get('category') != '') {
            $knowledges = \App\Knowledge::where('company_id', auth()->user()->company_id)->where('category', 'like', '%'.request()->get('category').'%')->paginate(20, ['*'], 'knowledge_page');
        }

        // mergeing user and user supervisor
        $user = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor = \App\User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)->get();
        $assign_user = $user->merge($supervisor);

        // data project filter by company
        $data_project = new \App\Project;
        $projects = $data_project->checkCompany();
        if (request()->has('search') || request()->has('search_project')) { // search project
            $projects = $data_project->checkCompany($search);
        }

        // add user client
        $client_user = \App\User::where('role_id', 9)->where('company_id', auth()->user()->company_id)->get();

        // get data client role project
        if (auth()->user()->role_id == 9) {
            $projects = auth()->user()->projects;
        }

        // memo
        $created_memo = \App\Memo::where('created_by', auth()->user()->id)->get();
        $memo = $created_memo->merge(auth()->user()->memoPeriods);
        if (request()->has('search') && request()->get('search') != '' || request()->has('search_memo') && request()->get('search_memo')) {
            // Initialize
            $memos  = \App\Memo::where('name', 'LIKE', '%'.$search.'%')->pluck('id');
            $memoId = [];

            foreach ($memos as $val) {
                $memoUser = DB::table('memos_users')->where(['memo_id' => $val, 'user_id' => auth()->user()->id])->first();

                if ($memoUser) {
                    array_push($memoId, $memoUser->memo_id);
                }
            }

            $memo = \App\Memo::whereIn('id', $memoId)->get();
        }

        // get all subject data users
        $subjects_users = auth()->user()->getDivisionMajorsSubjectData();
        $get_task = new \App\Task;

        // dd($task->doing);

        return view('home.index', compact('get_task','task', 'tasks_priority', 'message', 'assigned_task', 'list_conversation', 'user', 'category_knowledge', 'knowledge_public', 'my_knowledge', 'assign_user', 'client_user', 'projects', 'knowledges', 'summary_task', 'memo','todos', 'subjects_users'));
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function paginate($items, $perPage = 5, $page = null, $options = [], $pageName = 'page')
    {
        $page = $page ?: (Paginator::resolveCurrentPage($pageName) ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    private function _assignedTask($search = null, $filter_subject = null, $start_date = null, $end_date = null) {
        // Initialize
        // $tasks = auth()->user()->tasks;
        
        $tasks = TaskUser::where('user_id', auth()->user()->id)->with('task')->get();

        if ($search) {
            $tasks = auth()->user()->searchTasks($search);
        }

        if ($filter_subject) {
            $subject_id = $filter_subject;
            $task_subject   = \App\Task::whereHas('subjects', function($q) use($subject_id) {
                $q->whereIn('subjects.ID', [$subject_id]);
            })->where('name', 'like', '%'.$search.'%')->with('subjects')->get();
            $tasks           = $task_subject;
            
        }

        if ($start_date || $end_date) {
            // dd($start_date);
            $tasks = auth()->user()->searchTasksDate($search, $start_date, $end_date);
        }

        if ($search || $filter_subject || $start_date || $end_date && count($tasks) > 0) {
            $val = [];
            foreach($tasks as $task) {
                // Initialize
                $row = [];
                $row['id']              = $task->id;
                $row['name']            = $task->name;
                $row['project_id']      = $task->project_id;

                if ($task->project) {
                    $row['project_by']          = $task->project->title;
                    $row['background_color']    = $task->project->background_color;
                } else {
                    $row['project_by']          = '-';
                    $row['background_color']    = '#36a8d9';
                }

                $row['assigned_by'] = $task->assigned_by;
                $usersTask          = '';

                // Get Users Assigned To In Task
                foreach ($task->users as $user) {
                    $usersTask .= '<button class="btn btn-sm btn-info text-white mb-2 mr-2">'.$user->name.'</button>';
                }

                $row['assigned_to']     = $usersTask;
                $row['start_date']      = date('d M y H:i', $task->start_date);
                $row['end_date']        = date('d M y H:i', $task->end_date);
                $row['start_date_num']  = $task->start_date;
                $row['end_date_num']    = $task->end_date;
                $row['report_path']     = $task->report_path;
                $row['detail']          = $task->detail;
                $row['created_at']      = $task->created_at->format('d M y H:i');
                $row['updated_at']      = $task->updated_at;
                $row['pivot_user_id']   = $task->pivot_user_id;
                $row['pivot_task_id']   = $task->pivot_task_id;

                // Check Progress %
                if (count($task->todos) > 0) {
                    $percentage = (count($task->isDone())/count($task->todos)) * 100;
                } else {
                    $percentage = '0';
                }

                // Check Task Attachment
                if ($task->taskAttachment) {
                    $attachment     = $task->taskAttachment->type;
                    $pathAttachment = $task->taskAttachment->path;
                } else {
                    $attachment     = null;
                    $pathAttachment = null;
                }

                $row['percentage']          = ceil($percentage);
                $row['attachment']          = $attachment;
                $row['pathAttachment']      = $pathAttachment;
                $row['assignedBy']          = ($task->assignedBy) ? $task->assignedBy->name : '-';
                $row['users']               = $task->users;
                $row['todos']               = $task->todos;

                // Check Status
                if ($task->status) {
                    $row['status'] = $task->status;
                } else {
                    $row['status'] = 0;
                }

                $val[] = $row;
            }
        } else {
            $val = [];
            foreach($tasks as $task) {
                // Initialize
                $row = [];
                $row['id']              = $task->task->id;
                $row['name']            = $task->task->name;
                $row['project_id']      = $task->task->project_id;
    
                if ($task->task->project) {
                    $row['project_by']          = $task->task->project->title;
                    $row['background_color']    = $task->task->project->background_color;
                } else {
                    $row['project_by']          = '-';
                    $row['background_color']    = '#36a8d9';
                }
    
                $row['assigned_by'] = $task->task->assigned_by;
                $usersTask          = '';
    
                // Get Users Assigned To In Task
                foreach ($task->task->users as $user) {
                    $usersTask .= '<button class="btn btn-sm btn-info text-white mb-2 mr-2">'.$user->name.'</button>';
                }
                
                $row['assigned_to']     = $usersTask;
                $row['start_date']      = date('d M y H:i', $task->task->start_date);
                $row['end_date']        = date('d M y H:i', $task->task->end_date);
                $row['start_date_num']  = $task->task->start_date;
                $row['end_date_num']    = $task->task->end_date;
                $row['report_path']     = $task->task->report_path;
                $row['detail']          = $task->task->detail;
                $row['created_at']      = $task->task->created_at->format('d M y H:i');
                $row['updated_at']      = $task->task->updated_at;
                $row['pivot_user_id']   = $task->task->pivot_user_id;
                $row['pivot_task_id']   = $task->task->pivot_task_id;
    
                // Check Progress %
                if (count($task->task->todos) > 0) {
                    $percentage = (count($task->task->isDone())/count($task->task->todos)) * 100;
                } else {
                    $percentage = '0';
                }
    
                // Check Task Attachment
                if ($task->task->taskAttachment) {
                    $attachment     = $task->task->taskAttachment->type;
                    $pathAttachment = $task->task->taskAttachment->path;
                } else {
                    $attachment     = null;
                    $pathAttachment = null;
                }
    
                $row['percentage']          = ceil($percentage);
                $row['attachment']          = $attachment;
                $row['pathAttachment']      = $pathAttachment;
                $row['assignedBy']          = ($task->task->assignedBy) ? $task->task->assignedBy->name : '-';
                $row['users']               = $task->task->users;
                $row['todos']               = $task->task->todos;
    
                // Check Status
                if ($task->task->status) {
                    $row['status'] = $task->task->status;
                } else {
                    $row['status'] = 0;
                }
    
                $val[] = $row;
            }   
        }

        // Sort Array
        // $data = collect($val)->sortBy('percentage')->toArray(); // <- By Percentage
        $data = collect($val)->sortBy('status')->toArray();

        return $data;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dataUser()
    {
        // $user = \App\User::whereIn('role_id', [6,8])->with('people')->paginate(10);

        // checking filter by company
        $user = \App\User::where('company_id', auth()->user()->company_id)->whereIn('role_id', [6,8])->paginate(16);
        // $user = \App\User::where('company_id', auth()->user()->company_id)->whereIn('role_id', [6,8])->latest()->get();

        $user_propose = \App\User::where('company_id', auth()->user()->company_id)->where('is_active', 'n')->where('is_propose', 'y')->get();

        $role = \App\Roles::all();
        $supervisor = \App\User::where('role_id', 8)->where('company_id', auth()->user()->company_id)->get();
        
        return view('admin.index', compact('user', 'role', 'supervisor', 'user_propose'));
    }

    // admin form user
    public function userForm()
    {
        // $user = \App\User::whereIn('role_id', [6,8])->with('people')->paginate(10);
        $data_user = null;
        if (request()->has('id')) {
            $data_user = \App\User::find(request()->get('id'));
        }

        // checking filter by company
        
        // Initialize
        $user       = \App\User::where('company_id', auth()->user()->company_id)->whereIn('role_id', [6,8])->paginate(10);
        $role       = \App\Roles::all();
        $supervisor = \App\User::where('role_id', 8)->where('company_id', auth()->user()->company_id)->get();
        $divisions  = Division::where('IDCompany', auth()->user()->company->ID)->orderBy('Name', 'ASC')->get();
        $majors     = '';

        if (auth()->user()->company->Type == 'school' || auth()->user()->company->Type == 'college') {
            $majors = Majors::where('IDCompany', auth()->user()->company_id)->orderBy('Name', 'ASC')->get();
        }

        return view('admin.form_user', compact('user', 'role', 'supervisor', 'data_user', 'divisions', 'majors'));
    }

    // admin approve propose user
    public function proposeApprove(\App\User $user)
    {
        // $user = \App\User::whereIn('role_id', [6,8])->with('people')->paginate(10);
        if ($user) {
            $user->is_active = 'y';
            $user->is_propose = 'n';
            $user->role_id = 6;
            $user->save();
            $status = 'OK';
            $message = "Berhasil ditambahkan";
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => true,
                'message'   => 'Pengajuan Pengguna berhasil di Approve'
            ]);

            die;
        }

        request()->session()->flash( 'status', $status );
        request()->session()->flash( 'message', $message );

        return redirect()->route('user.data');
    }

    public function editUser(\App\User $user)
    {
        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => [
                'data'      => $user,
                'division'  => $user->division,
                'majors'    => $user->majors
            ]
        ]);
    }

    // for admin store user
    public function store()
    {
        $password = $this->generateRandomString(6);
        $user = \App\User::updateOrCreate([
            'id' => request()->id,
        ],
        [
            'email' => request()->email,
            'nip' => request()->nip ? request()->nip : '-',
            'name' => request()->name,
            'phone' => request()->phone,
            'role_id' => request()->role,
            'supervised_by' => request()->supervised_by,
            'company_id' => auth()->user()->company_id,
            'is_active' => 'y',
            'password' => bcrypt($password),
            'is_demo' => (auth()->user()->is_demo == 1) ? 1 : 0
        ]);

        $status = 'FAILED';
        $message = "Gagal ditambahkan";
        
        if ($user) {
            $status = 'OK';
            $message = "Berhasil diubah";

            // Check User Division
            $userDivision = UserDivision::where('user_id', $user->id)->first();

            if ($userDivision) {
                UserDivision::where('user_id', $user->id)->update([
                    'division_id' => request('divisionId')
                ]);
            } else {
                UserDivision::where('user_id', $user->id)->create([
                    'user_id'       => $user->id,
                    'division_id'   => request('divisionId')
                ]);
            }

            // Check Company Type
            if (auth()->user()->company->Type == 'school' || auth()->user()->company->Type == 'college') {
                // Check User Majors
                $userMajors = UserMajors::where('user_id', $user->id)->first();

                if ($userMajors) {
                    UserMajors::where('user_id', $user->id)->update([
                        'major_id' => request('majorsId')
                    ]);
                } else {
                    UserMajors::where('user_id', $user->id)->create([
                        'user_id'  => $user->id,
                        'major_id' => request('majorsId')
                    ]);
                }
            }

            if (!request()->id) {
                $user->password = $password;
                \Mail::to($user->email)->send(new \App\Mail\RegisterAbsensi($user));
                $status = 'OK';
                $message = "Berhasil ditambahkan";
            }

            // Check ajax request
            if(request()->ajax()){
                // Check Create or Not
                if ($user->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Member berhasil disimpan'
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Member berhasil diperbaharui'
                ]);

                die;
            }
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Not
            if ($user->wasRecentlyCreated) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Member gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Member gagal diperbaharui'
            ]);

            die;
        }

        request()->session()->flash( 'status', $status );
        request()->session()->flash( 'message', $message );

        return redirect()->route('user.data');
    }

    public function delete(\App\User $user)
    {
        $status = 'FAILED';
        $message = "Gagal dihapus";

        if ($user->delete()) {
            $status = 'OK';
            $message = "Berhasil dihapus";

            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Member berhasil dihapus'
                ]);

                die;
            }
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Member gagal dihapus'
            ]);

            die;
        }

        request()->session()->flash( 'status', $status );
        request()->session()->flash( 'message', $message );

        return redirect()->back();
    }

    function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function admin()
    {
        return view('admin.home');
    }

    public function switchLang($lang)
    {
        \Session::put('applocale', $lang);
        return redirect()->back();
    }

    public function workingOnProgress()
    {
        $get_activities = \App\TodoActivity::get();

        $data = array();

        if (count($get_activities) > 0) {
            foreach ($get_activities as $key => $value) {
                // dd($value->todo->task->users->toArray());
    
                $status = false;
                foreach ($value->todo->task->users as $user) {
                    if (auth()->user()->id == $user->id) {
                        $status = true;
                    }
                }
    
                if ($status) {
                    $value->todo;
                    $value->task;
                    $data[] = $value;
                }           
            }
        }

        return $data;
    }
}
