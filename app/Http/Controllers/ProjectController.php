<?php

namespace App\Http\Controllers;

use App\Project;
use App\User;
use Illuminate\Http\Request;
use App\Task;
use DB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project = \App\Project::create([
            'title'             => $request->title,
            'description'       => $request->description,
            'user_id'           => auth()->user()->id,
            'background_color'  => request()->background_color
        ]);

        if ($project) {
            if ($request->user_client) {
                $project->users()->attach($request->user_client);
            }

            if (request()->file('upload_file')) {
                // Initialize
                $file       = request()->file('upload_file');
                $md5_name   = uniqid().md5_file($file->getRealPath());
                $ext        = $file->getClientOriginalExtension();
                $fileSize   = $file->getSize();

                // Check Account
                if (auth()->user()->is_demo == 1) {
                    if ($fileSize <= 100000) { // 1 MB
                        // Check Extension
                        if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                            $destination_path = public_path('storage/uploads/img/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'image',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/img/project/$md5_name.$ext",
                            ]);
                        } else if ($ext == 'mp4' || $ext == 'avi') {
                            $destination_path = public_path('storage/uploads/video/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'video',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/video/project/$md5_name.$ext",
                            ]);
                        } else if ($ext == 'pdf') {
                            $destination_path = public_path('storage/uploads/file/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'file',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/file/project/$md5_name.$ext",
                            ]);
                        }                
                    }
                } else {
                    if ($fileSize <= 300000) { // 3 MB
                        // Check Extension
                        if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg') {
                            $destination_path = public_path('storage/uploads/img/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'image',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/img/project/$md5_name.$ext",
                            ]);
                        } else if ($ext == 'mp4' || $ext == 'avi') {
                            $destination_path = public_path('storage/uploads/video/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'video',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/video/project/$md5_name.$ext",
                            ]);
                        } else if ($ext == 'pdf') {
                            $destination_path = public_path('storage/uploads/file/project/');
                            $file->move($destination_path,"$md5_name.$ext");
                        
                            $attachment = \App\ProjectAttachment::create([
                                'project_id' => $project->id,
                                'type' => 'file',
                                'path' => env('SITE_URL') . '/storage/' . "uploads/file/project/$md5_name.$ext",
                            ]);
                        }
                    }
                }
            }

            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Proyek berhasil disimpan',
                    'data'      => $project
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Project created successfully'
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Proyek gagal disimpan'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Project created failed'
        ];

        return redirect()->back()->with($notif);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        // Initialize
        $data_project       = new \App\Project;
        $projects           = $data_project->checkCompany();
        $user               = User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor         = User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)                  ->get();
        $assign_user        = $user->merge($supervisor);
        $client_user        = User::where('role_id', 9)->where('company_id', auth()->user()->company_id)->get();

        $array_user_project = [];
        foreach ($project->users as $project_user) {
            $array_user_project[] = $project_user->id;
        }

        $get_user_project   = User::where('role_id', 9)->whereNotIn('id', $array_user_project)->where('company_id', auth()->user()->company_id)->get();
        $tasks              = Task::where('project_id', $project->id)->paginate('24');

        return view('project.show', compact('project', 'projects', 'assign_user', 'client_user', 'get_user_project', 'array_user_project', 'tasks'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        // Initialize
        $project->title             = $request->title;
        $project->description       = $request->description;
        $project->background_color  = $request->background_color;

        if ($project->save()) {
            // Delete All Client
            // $project->users()->detach($request->user_client);
            DB::table('projects_users')->where('project_id', $project->id)->delete();

            if ($request->user_client) {
                $project->users()->attach($request->user_client);
            }

            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Proyek berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Project update successfully'
            ];
        }
        
        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Proyek gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Project update failed'
        ];

        return redirect()->back()->with($notif);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        if ($project->delete()) {
            
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Proyek berhasil dihapus'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Project delete successfully'
            ];
        } else {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => false,
                    'message'   => 'Proyek gagal dihapus'
                ]);

                die;
            }
        }

        return redirect()->back()->with($notif);
    }

    public function showUsers(\App\Project $project)
    {
        // Initialize
        $user        = User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor  = User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)           ->get();
        $assign_user = $user->merge($supervisor);
        $tasks       = Task::where('id', request('task-id'))->first();

        // Get User By Task
        $users = [];
        foreach ($tasks->users as $val) {
            array_push($users, $val->id);
        }

        $data = '';
        foreach ($assign_user as $val) {
            // Initialize
            $selected = '';
            
            if (in_array($val->id, $users)) {
                $selected = 'selected';
            }

            $data .= '<option value="'.$val->id.'" '.$selected.'>'.$val->name.'</option>';
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function listProject()
    {
        // Initialize
        $data_project   = new \App\Project;
        $projects       = $data_project->checkCompany();
        $projectId      = request('projectId');

        $html = '';
        foreach ($projects as $val) {
            // Initialize
            $selected = '';

            if ($val->id == $projectId) {
                $selected = 'selected';
            }

            $html .= '<option value="'.$val->id.'" '.$selected.'>'.$val->title.'</option>';
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $html
        ]);
    }

    public function listActivity(Project $project)
    {
        // Initialize
        $tasks      = Task::where('project_id', $project->id)->pluck('id');
        $todos      = \App\Todo::whereIn('task_id', $tasks)->pluck('id');
        $activities = \App\TodoActivity::whereIn('todo_id', $todos)->latest('updated_at')->get();

        $data = [];
        foreach ($activities as $val) {
            $row = [];
            $row['todo']        = ucfirst($val->todo->todo);
            $row['status']      = ucfirst($val->status);
            $row['user_name']   = ucfirst($val->user->name);
            $row['created_at']  = $val->created_at->format('d M y H:i');
            $row['updated_at']  = $val->updated_at->format('d M y H:i');
            $row['avatar']      = $val->user->avatar;
            $row['task']        = ucfirst($val->todo->task->name);

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }
}
