<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KonverterController extends Controller
{
    public function insertUser(Request $request){
        $response = array(
            'status'=>false,
            'code'=>2,
            'result'=>null
        );

        $email = '';
        $name = '';
        $phone_no = '';
        $nip = '';

        if($request->has('email') && $request->email !='' && $request->email !=null){
            $email = $request->email;
        }
        if($request->has('name') && $request->name !='' && $request->name !=null){
            $name = $request->name;
        }
        if($request->has('phone_no') && $request->phone_no !='' && $request->phone_no !=null){
            $phone_no = $request->phone_no;
        }
        if($request->has('nip') && $request->nip !='' && $request->nip !=null){
            $nip = $request->nip;
        }

        if(!empty($email)){
            $check_people = \App\User::where('email',$email)->first();
        }
        if(empty($email)){
            return $response;
        }
        
        if(empty($check_people)){
            $password = $this->generateRandomString(6);
            $input_people = array(
                'email' => $email,
                'password'=> bcrypt($password),
                'name'=>$name,
                'nip'=>$nip,
                'phone' => $phone_no,
                'role_id'=>'6',
            );
            
            $insert_people = \App\User::create($input_people);

            if ($insert_people) {
                $insert_people->password = $password;
                \Mail::to($insert_people->email)->send(new \App\Mail\RegisterAbsensi($insert_people));
            }
            $people_data = $insert_people;
            $people_id = $insert_people->id;
        }else{
            $people_id = $check_people->id;
            $people_data = $check_people;
        }

        if($people_id > 0){
            $response = array(
                'status'=>true,
                'code'=>1,
                'result'=>array(
                    'user' => $people_data,
                )
            );
        }

        return $response;
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
}
