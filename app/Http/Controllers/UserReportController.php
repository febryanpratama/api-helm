<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attendance;
use App\TaskUser;
use App\Todo;
use App\TodoActivity;
use App\TaskAttachment;
use App\Task;
use App\Reward;
use App\UserReward;
use App\UserRewardReview;

class UserReportController extends Controller
{
    public function index()
    {
        // Initialize
        $userId = auth()->user()->id;

        // Check Search Report
        if (request('from_date') || request('till_date')) {
            $attendances = auth()->user()->searchAttendances(request('from_date'), request('till_date'), $userId);
        } else {
            // Initialize
            $latestDate = date('t');
            $startDate  = date('Y-m').'-01';
            $endDate    = date('Y-m').'-'.$latestDate;

            $attendances = Attendance::where('user_id', $userId)->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate)->latest()->get();
        }

        $tasks = $this->_assignedTask(request()->get('search'));

        return view('report.index', compact('attendances','tasks'));
    }

    private function _assignedTask($search = null) {
        // Initialize
        $tasks = TaskUser::where('user_id', auth()->user()->id)->with('task')->get();

        if ($search) {
            $tasks = auth()->user()->searchTasks($search);
        }

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
            $row['assignedBy']          = $task->task->assignedBy->name;
            $row['users']               = $task->task->users;
            $row['todos']               = $task->task->todos;

            // Check Status
            if ($task->task->status) {
                $row['status'] = $task->task->status;
            } else {
                $row['status'] = 0;
            }

            // Todos (Doing)
            $toDoId       = Todo::where('task_id', $task->task_id)->pluck('id');
            $toDoDoing    = TodoActivity::whereIn('todo_id', $toDoId)->where('status', 'doing')->latest()->get();
            $row['todos_doing'] = $toDoDoing;

            $val[] = $row;
        }

        // Sort Array
        $data = collect($val)->sortBy('status')->toArray();

        return $data;
    }

    public function showReportUser()
    {
        // Initialize
        $attachments = TaskAttachment::where(['task_id' => request('taskId'),'user_id' => auth()->user()->id])->latest()->get();
        $data        = [];

        foreach ($attachments as $val) {
            // Initialize
            $reward = Reward::where('task_id', $val->task_id)->first();

            if ($reward) {
                $userReward   = UserReward::where(['user_id' => $val->user_id, 'reward_id' => $reward->id])->first();
                $statusReward = '<span class="badge badge-info text-white">Moderasi</span>';

                if ($userReward && $userReward->give_reward == '0') {
                    $statusReward = '<span class="badge badge-danger text-white">Ditolak</span>';
                } elseif ($userReward && $userReward->give_reward == '1') {
                    $statusReward = '<span class="badge badge-success text-white">Diterima</span>';
                }

                if ($reward->reward_type == '1') {
                    $rewardTypeDesc = 'Skor';
                } elseif ($reward->reward_type == '2') {
                    $rewardTypeDesc = 'Hadiah';
                } else {
                    $rewardTypeDesc = 'Uang';
                }

                $row['is_reward']           = true;
                $row['status_reward']       = $statusReward;
                $row['give_reward']         = ($userReward) ? $userReward->give_reward : '';
                $row['reward_value']        = ($userReward) ? $userReward->reward_value : '';
                $row['reward_description']  = ($userReward) ? $userReward->reward_description : '';
                $row['reward_type']         = $reward->reward_type;
                $row['reward_type_desc']    = $rewardTypeDesc;
                $row['user_reward']         = ($userReward) ? true : false;
                $row['user_reward_id']      = ($userReward) ? $userReward->id : '';
                $row['user_reward_path']    = ($userReward) ? $userReward->evidence_of_transfer : '';

                if ($userReward) {
                    // Get Review From This Account
                    $userRewardR = UserRewardReview::where(['user_reward_id' => $userReward->id, 'user_id' => auth()->user()->id])->first();

                    if ($userRewardR) {
                        $row['is_review_this_account']  = true;
                        $row['review_this_account']     = $userRewardR->review;
                    } else {
                        $row['is_review_this_account']  = false;
                    }
                }
            } else {
                $row['user_reward'] = false;
                $row['is_reward']   = false;
            }

            $row['username']   = auth()->user()->name;
            $row['user_id']     = auth()->user()->id;
            $row['created_at'] = $val->created_at->format('d F Y');
            $row['path']       = $val->path;
            $row['task_name']  = $val->task->name;

            $data[] = $row;
         } 

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }

    public function showReportUserAssigned(Task $task)
    {
        // Initialize
        $usersId = [];

        foreach ($task->users as $val) {
            if ($task->assignedBy->id != $val->id) {
                array_push($usersId, $val->id);
            }
        }

        // Initialize
        $attachments = TaskAttachment::where(['task_id' => $task->id])->whereIn('user_id', $usersId)->latest()->get();
        $data        = [];

        foreach ($attachments as $val) {
            // Initialize
            $reward = Reward::where('task_id', $val->task_id)->first();

            $row['task_id']         = $val->task_id;
            $row['created_at']      = $val->created_at->format('d F Y');
            $row['path']            = $val->path;
            $row['username']        = $val->user->name;
            $row['user_id']         = $val->user->id;
            $row['reward_id']       = ($reward) ? $reward->id : '';
            $row['reward_value']    = ($reward) ? $reward->reward_value : '';

            if ($reward) {
                // Initialize - Check User Is Reward Exist
                $userReward = UserReward::where(['reward_id' => $reward->id, 'user_id' => $val->user->id])->first();

                if ($reward->reward_type == '1') {
                    $rewardTypeDesc = 'Skor';
                } elseif ($reward->reward_type == '2') {
                    $rewardTypeDesc = 'Hadiah';
                } else {
                    $rewardTypeDesc = 'Uang';
                }

                if ($userReward) {
                    $row['give_reward']         = $userReward->give_reward;
                    $row['reward_value']        = $userReward->reward_value;
                    $row['reward_description']  = $userReward->reward_description;
                    $row['reward_type']         = $reward->reward_type;
                    $row['reward_type_desc']    = $rewardTypeDesc;

                    // Check Reward Review
                    $userRewardR = UserRewardReview::where(['user_reward_id' => $userReward->id, 'user_id' => $val->user->id])->first();

                    if ($userRewardR) {
                        $row['user_reward'] = true;
                        $row['user_review'] = $userRewardR->review;
                    } else {
                        $row['user_reward'] = false;
                        $row['user_review'] = '';
                    }
                } else {
                    $row['give_reward']      = '2';
                    $row['user_reward']      = false;
                    $row['reward_type']      = $reward->reward_type;
                    $row['reward_type_desc'] = $rewardTypeDesc;
                }

                $statusReward = '<span class="badge badge-info text-white">Moderasi</span>';
                
                if ($userReward && $userReward->give_reward == '0') {
                    $statusReward = '<span class="badge badge-danger text-white">Ditolak</span>';
                } elseif ($userReward && $userReward->give_reward == '1') {
                    $statusReward = '<span class="badge badge-success text-white">Diterima</span>';
                }

                $row['status_reward'] = $statusReward;
            }

            $data[] = $row;
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $data
        ]);
    }
}
