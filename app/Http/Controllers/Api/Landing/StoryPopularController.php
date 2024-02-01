<?php

namespace App\Http\Controllers\Api\Landing;

use App\Http\Controllers\Controller;
use App\LandingStoryPopular;
use App\Story;
use Illuminate\Http\Request;
use Validator;

class StoryPopularController extends Controller
{
    public function makePopular(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'story_id' => 'required|exists:stories,ID'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }


        $story = LandingStoryPopular::where([
            'story_id' => $request->story_id,
        ])->first();

        if (!$story) {
            $story = LandingStoryPopular::create([
                'story_id' => $request->story_id,
            ]);
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'story popular web berhasil disimpan',
            'result' => $story
        ], 200);
    }

    public function removePopular(Request $request)
    {
        //set validation
        $validator = Validator::make(request()->all(), [
            'story_id' => 'required|exists:stories,ID'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $data_story = Story::find($request->story_id);

        $story = LandingStoryPopular::where([
            'story_id' => $request->story_id,
        ])->first();


        if ($story) {
            $story->delete();
        }

        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => 'Berhasil merubah story popular',
        ], 200);
    }
}
