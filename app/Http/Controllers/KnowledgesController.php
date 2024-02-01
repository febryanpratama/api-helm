<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Knowledge;

class KnowledgesController extends Controller
{
    public function index(Knowledge $knowledge)
    {
        // Initialize
        $my_knowledge       = Knowledge::whereNotIn('id', [$knowledge->id])->where('is_private', 'y')->where('user_id', auth()->user()->id)->orderBy('id', 'desc')->get();
        // $getKnowledges      = Knowledge::whereNotIn('id', [$knowledge->id])->where('is_private', 'n')->orderBy('id', 'desc')->get();
        $getKnowledgesAll   = Knowledge::whereNotIn('id', [$knowledge->id])->where('is_private', 'n')->orderBy('id', 'desc')->paginate('15');
        $getKnowledges      = $getKnowledgesAll->merge($my_knowledge);
        $bgColor            = $knowledge->background_color;
        $knowledge          = Knowledge::where('id', $knowledge->id)->get();
        $knowledges         = $knowledge->merge($getKnowledges);
        $category_knowledge = Knowledge::select('category')->groupBy('category')->get();

        return view('member.note.index', compact('knowledges','bgColor','category_knowledge','getKnowledgesAll'));
    }

    public function store()
    {
        if (request()->id) {
            $knowledge = \App\Knowledge::updateOrCreate([
                'id' => request()->id
            ],
            [
                'title'             => request()->title,
                'category'          => request()->category,
                'hastag'            => request()->hastag,
                'content'           => request()->content,
                'is_private'        => request()->is_private != null ? request()->is_private : 'n',
                'edited_by'         => auth()->user()->id,
                'company_id'        => auth()->user()->company_id,
                'background_color'  => request()->background_color
            ]);
        } else {
            $knowledge = \App\Knowledge::create(
            [
                'title'             => request()->title,
                'category'          => request()->category,
                'hastag'            => request()->hastag,
                'content'           => request()->content,
                'is_private'        => request()->is_private != null ? request()->is_private : 'n',
                'user_id'           => auth()->user()->id,
                'company_id'        => auth()->user()->company_id,
                'background_color'  => request()->background_color
            ]);
        }

        if ($knowledge) {
            if (request()->id) {
                $knowledge->subjects()->detach(request()->subject);    
            }

            $knowledge->subjects()->attach(request()->subject);

            if (request()->hasFile('file_knowledge')) {
                foreach (request()->file('file_knowledge') as $key => $value) {
                    // Initialize
                    $fileSize = $value->getSize();

                    // Check Account
                    if (auth()->user()->is_demo == 1) {
                        if ($fileSize <= 100000) { // 1 MB
                            $path = $value->store('uploads/knowledge', 'public');
                            
                            $knowledge_media = \App\KnowledgeMedia::create([
                                'knowledge_id' => $knowledge->id,
                                'location' =>  env('SITE_URL') . '/storage/' . $path,
                            ]);
                        }
                    } else {
                        if ($fileSize <= 300000) { // 3 MB
                            $path = $value->store('uploads/knowledge', 'public');
                            
                            $knowledge_media = \App\KnowledgeMedia::create([
                                'knowledge_id' => $knowledge->id,
                                'location' =>  env('SITE_URL') . '/storage/' . $path,
                            ]);
                        }
                    }
                }
            }

            // Check ajax request
            if(request()->ajax()){
                // Check Create or Not
                if ($knowledge->wasRecentlyCreated) {
                    return response()->json([
                        'status'    => true,
                        'message'   => 'Catatan berhasil disimpan'
                    ]);

                    die;
                }

                return response()->json([
                    'status'    => true,
                    'message'   => 'Catatan berhasil diperbaharui'
                ]);

                die;
            }

            $notif = [
                'status' => 'success',
                'message' => 'Create Knowledge successfully'
            ];

            return redirect()->back()->with($notif);
        }

        // Check ajax request
        if(request()->ajax()){
            // Check Create or Not
            if ($knowledge->wasRecentlyCreated) {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Catatan gagal disimpan'
                ]);

                die;
            }

            return response()->json([
                'status'    => false,
                'message'   => 'Catatan gagal diperbaharui'
            ]);

            die;
        }

        $notif = [
            'status' => 'failed',
            'message' => 'Create Knowledge fail'
        ];

        return redirect()->back()->with($notif);
    }

    public function destroy(\App\Knowledge $knowledge)
    {
        if ($knowledge->delete()) {
            $notif = [
                'status' => 'success',
                'message' => 'Knowledge delete successfully'
            ];

            // Check ajax request
            if(request()->ajax()){
                return response()->json([
                    'status'    => true,
                    'message'   => 'Catatan berhasil dihapus'
                ]);

                die;
            }
        }

        // Check ajax request
        if(request()->ajax()){
            return response()->json([
                'status'    => false,
                'message'   => 'Catatan gagal dihapus'
            ]);

            die;
        }

        return redirect()->back()->with($notif);
    }
}
