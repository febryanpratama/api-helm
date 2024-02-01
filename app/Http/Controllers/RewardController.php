<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserReward;
use App\UserRewardReview;

class RewardController extends Controller
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
        // Check Reward Exists
        $rewardExists = UserReward::where(['reward_id' => request('reward_id'), 'user_id' => request('user_id')])->first();

        if ($rewardExists) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data sudah diinput sebelumnya'
            ]);

            die;
        }

        // Check Upload File
        if (request('evidence-of-transfer')) {
            // Initialize
            $file             = request()->file('evidence-of-transfer');
            $md5_name         = uniqid().md5_file($file->getRealPath());
            $ext              = $file->getClientOriginalExtension();
            $destination_path = public_path('storage/uploads/img/reward/');

            $file->move($destination_path,"$md5_name.$ext");
        }

        UserReward::create([
            'reward_id'             => request('reward_id'),
            'user_id'               => request('user_id'),
            'give_reward'           => request('give_reward'),
            'reward_description'    => request('reward_description'),
            'reward_value'          => request('reward_value'),
            'evidence_of_transfer'  => (request('evidence-of-transfer')) ? env('SITE_URL') .'/storage/uploads/img/reward/'.$md5_name.'.'.$ext : null,
        ]);

        return response()->json([
            'status'    => true,
            'message'   => 'Data berhasil disimpan'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function storeUserReward()
    {
        UserRewardReview::create([
            'user_reward_id' => request('user_reward_id'),
            'user_id'        => request('user_id'),
            'review'         => request('review')
        ]);

        return response()->json([
            'status'    => true,
            'data'      => 'Ulasan berhasil dikirim'
        ]);
    }
}
