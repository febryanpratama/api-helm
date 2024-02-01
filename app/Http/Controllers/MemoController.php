<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use App\MemoMedia;
use App\Memo;
use DB;

class MemoController extends Controller
{
    public function index(Memo $memo)
    {
        // Initialize
        $memoUser   = DB::table('memos_users')->where('user_id', auth()->user()->id)->whereNotIn('memo_id', [$memo->id])->pluck('memo_id');
        $getMemo    = Memo::whereIn('id', $memoUser)->paginate('20');
        $bgColor    = $memo->background_color;
        $memo       = Memo::where('id', $memo->id)->get();
        $memos      = $memo->merge($getMemo);

        // mergeing user and user supervisor
        $user       = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor = \App\User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)->get();
        $assign_user = $user->merge($supervisor);

        return view('member.memo.index', compact('memos','assign_user','bgColor','getMemo'));
    }

    public function store()
    {
        // dd(request('memo_user'));
        $memo = \App\Memo::updateOrCreate(
            [
                'id' => request()->id
            ], [
            'name'              => request()->title,
            'created_by'        => auth()->user()->id,
            'description'       => request()->description,
            'background_color'  => request()->background_color,
            'start_period'      => request()->start_period,
            'end_period'        => request()->end_period,
        ]);
    
        if ($memo) {
            // Initialize
            $memoUser = request()->memo_user;

            // Check Create or Note
            if (!$memo->wasRecentlyCreated) {
                // Memo Untuk fitur home
                // $memoUser = json_decode(request()->memo_user, TRUE);
            }

            if (request()->id) {
                $memo->users()->detach($memoUser);
            }

            $memo->users()->attach($memoUser);
    
            if (!request()->id) {
                if ($memoUser) {
                    for ($i=0; $i < count($memoUser) ; $i++) { 
                        $user = \App\User::find($memoUser[$i]);
    
                        if ($user->id != auth()->user()->id) {
    
                            // OneSignal::sendNotificationUsingTags(
                            //     "ada memo baru nih memo-$memo->name",
                            //     array(
                            //         ["field" => "tag", "key" => "user_id", "relation" => "=", "value" => $user->id],
                            //     ),
                            //     $url = route('home'),
                            //     $data = null,
                            //     $buttons = null,
                            //     $schedule = null
                            // );
                        }
                    }
                }
            }

            // Check File Upload
            if (request()->hasFile('upload_file')) {
                // Check is there previous media
                if ($memo->memoMedia) {
                    $explodePath = explode('/', $memo->memoMedia->location);

                    @unlink('storage/uploads/memo/'.$explodePath[6]);

                    // Delete Memo Media
                    $memo->memoMedia->delete();
                }

                // Intialize
                $fileSize = request()->hasFile('upload_file')->getSize();

                // Check Account
                if (auth()->user()->is_demo == 1) {
                    if ($fileSize <= 100000) { // 1 MB
                        $path = request('upload_file')->store('uploads/memo', 'public');

                        $memoMedia = MemoMedia::create([
                            'memo_id'   => $memo->id,
                            'location'  => env('SITE_URL') . '/storage/' . $path,
                            'type'      => request('upload_file')->getClientMimeType()
                        ]);
                    }
                } else {
                    if ($fileSize <= 300000) { // 3 MB
                        $path = request('upload_file')->store('uploads/memo', 'public');

                        $memoMedia = MemoMedia::create([
                            'memo_id'   => $memo->id,
                            'location'  => env('SITE_URL') . '/storage/' . $path,
                            'type'      => request('upload_file')->getClientMimeType()
                        ]);
                    }
                }
            }

            // Check ajax request
            if(request()->ajax()){
                // Check Create or Note
                if ($memo->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Memo berhasil disimpan'
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Memo berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Memo created successfully',
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Note
            if ($memo->wasRecentlyCreated) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Memo gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Memo gagal diperbaharui'
            ]);

            die;
        }
    
        $notif = [
            'status' => 'failed',
            'message' => 'Task created fail'
        ];
    
        return redirect()->back()->with($notif);
    }

    public function delete(\App\Memo $memo)
    {
        // Check Memo Media
        if ($memo->memoMedia) {
            $explodePath = explode('/', $memo->memoMedia->location);

            @unlink('storage/uploads/memo/'.$explodePath[6]);

            // Delete Memo Media
            $memo->memoMedia->delete();
        }

        if ($memo->delete()) {
            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Memo berhasil dihapus'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Deleted successfully',
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Memo gagal dihapus'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Deleted Fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function memoUser() {
        // Initiailze
        $user       = \App\User::whereIn('role_id', [6,8,1,2])->where('company_id', auth()->user()->company_id)->orWhere('id', auth()->user()->id)->get();
        $supervisor = \App\User::whereNotNull('supervised_by')->where('company_id', auth()->user()->company_id)->where('supervised_by', auth()->user()->id)->get();
        $assign_user = $user->merge($supervisor);
        $memo        = Memo::where('id', request('memoId'))->first();
        $html        = '';

        foreach($assign_user as $val) {
            foreach($memo->users as $user) {
                if ($val->id == $user->id) {
                    $html .= '<option value="'.$val->id.'" selected>'.$val->name.'</option>';
                } else {
                    $html .= '<option value="'.$val->id.'">'.$val->name.'</option>';
                }
            }
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Data tersedia',
            'data'      => $html
        ]);
    }
}
