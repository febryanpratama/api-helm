<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class AuthController extends Controller
{
    /*== Signin process ==*/
	public function signinProcess( Request $request, \App\People $people ) 
	{
        // dd($request->all());
		$field = request()->email;

        if (filter_var($field, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = FALSE;
        }

		if ($field) {
			// check user
			$member = $people->signin( $request->email, $request->password, $field);
			// endif
			if ( !$member ) {
				$status = 'FAILED';
				$message = "Email atau password salah, atau akun Anda belum aktif, silakan coba lagi";
			} else {
				if ($member->Foto != null) {
					$foto = 'assets/img/profile/'.$member->Foto;
				} else {
					$foto = 'assets/img/pp/avatar.jpg';
				}
				
				$status = 'OK';
				$message = "Signin berhasil";
				$role  = \App\Roles::where("ID",$member->IDRole)->first();
				// set data user for session
				$data_user[ 'user_id' ] = $member->ID;
				$data_user[ 'email' ] = $member->Email;
				$data_user[ 'name' ] = $member->Name;
				$data_user[ 'phone' ] = $member->PhoneNo;
				$data_user[ 'address' ] = $member->Address;
				$data_user[ 'id_subdistrict' ] = $member->IDSubdistrict;
				$data_user[ 'zipcode' ] = $member->ZipCode;
				$data_user[ 'card_no' ] = $member->IDCardNo;
				$data_user[ 'role_id' ] = $member->IDRole;
				$data_user[ 'role_name' ] = $role->Name;
				$data_user[ 'own_venue' ] = $member->IsOwnVenue;
				$data_user[ 'pp' ] = $foto;
				// set session
				
				$request->session()->put( $data_user );
				
			}
	
			$request->session()->flash( 'status', $status );
			$request->session()->flash( 'message', $message );
			
			// redirect
			if ( $status == 'OK' ){
					return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name)); 
			} else {
				return redirect()->back();
			}
		} else {
			$status = 'FAILED';
			$message = "Harap Input Email atau No Hp";

			$request->session()->flash( 'status', $status );
			$request->session()->flash( 'message', $message );

			return redirect()->back();
		}

	}

	public function validation()
    {
		$status = 'FAILED';
		$message = "Email atau password salah, atau akun Anda belum aktif, silakan coba lagi";
		if (Auth::attempt(request()->only('nip', 'password'))){

			if (auth()->user()->role_id == 6 || auth()->user()->role_id == 8) {
				$path = null;
				if (request()->foto) {
					// $imagePath = request('foto')->store('uploads/img/photo', 'public');
					$path = env('SITE_URL') . '/storage/' . request()->foto;
				}
				
				$location = request()->location;
				$member = auth()->user();
				date_default_timezone_set('Asia/Jakarta');
				$member->time = date('H:i');

				$check_attendance = \App\Attendance::where('user_id', $member->id)->whereNull('check_out_datetime')->orderBy('id', 'desc')->first();

				if ($check_attendance) {
					return redirect()->route('auth.logout')->with(['status' => 'true', 'message' => 'Anda belum melakukan checkout untuk checkin sebelumnya']);
				}

				// save attendance
				$attendance = \App\Attendance::create([
					'user_id' => $member->id,
					'check_in_datetime' => date('Y-m-d H:i:s'),
					'check_in_place' => $location,
					'check_in_photo' => $path
				]);

				// send To mail management
				\Mail::to(env('EMAIL_RECEIVER'))->send(new \App\Mail\CheckIn($member, $path, $location));

				\Mail::to($member->email)->send(new \App\Mail\CheckIn($member, $path, $location));
			}
			return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
        }

		request()->session()->flash( 'status', $status );
		request()->session()->flash( 'message', $message );
		
        return redirect('/login');
    }

	public function validationOtp()
    {
		$status = 'FAILED';
		$message = "OTP salah, silakan coba lagi";
		// dd(request()->all());
		if (Auth::attempt(request()->only('email', 'password'))){

			if (auth()->user()->role_id == 10) {
				return redirect()->route('superadmin.index');
			}

			// cek expired admin
			if (auth()->user()->role_id == 1 && auth()->user()->company && auth()->user()->company->ExpiredDate != null && date('Y-m-d') > auth()->user()->company->ExpiredDate) {
				return redirect()->route('subscribe.package');
			}

			// cek expired user
			if (auth()->user()->company && auth()->user()->company->ExpiredDate != null && date('Y-m-d') > auth()->user()->company->ExpiredDate) {
				return redirect()->route('subscribe.member_expired');
			}

			// cek admin user register complete profile
			if (auth()->user()->name == '-' && auth()->user()->phone == null) { // asalnya pake ||
				// return redirect()->route('profile.edit', auth()->user()->id);
				return redirect()->route('transaction.register');
			}

			// cek admin user rigster not active and done input company
			if (auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0 && auth()->user()->company && auth()->user()->company->IsConfirmed == 'n') {
				return redirect()->route('transaction.package');
			}

			// cek admin user rigster not active
			if (auth()->user()->is_active == 'n' && count(auth()->user()->transaction) == 0) {
				return redirect()->route('transaction.agency');
			}
			// cek admin user rigster not active && complete transaction	
			if (auth()->user()->is_active == 'n' && count(auth()->user()->transaction) > 0) {
				return redirect()->route('transaction.detail', auth()->user()->transaction[0]->payment->ID);
			}

			// cek admin active && company not complete
			if (auth()->user()->is_active == 'y' && auth()->user()->company && auth()->user()->company->IsConfirmed =='y' && auth()->user()->company->Address == '-') {
				return redirect()->route('profile.company_edit', ['company' => auth()->user()->company_id]);
			}

			if (auth()->user()->role_id == 6 || auth()->user()->role_id == 8 || auth()->user()->role_id == 1 || auth()->user()->role_id == 2) {
				$path = request()->path;
				
				$location = request()->location;
				$member = auth()->user();
				date_default_timezone_set('Asia/Jakarta');
				$member->time = date('H:i');

				$check_attendance = \App\Attendance::where('user_id', $member->id)->whereNull('check_out_datetime')->orderBy('id', 'desc')->first();

				if ($check_attendance) {
					// return redirect()->route('auth.logout')->with(['status' => 'true', 'message' => 'Anda belum melakukan checkout untuk checkin sebelumnya']);
					return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
				}

				// save attendance
				$attendance = \App\Attendance::create([
					'user_id' => $member->id,
					'check_in_datetime' => date('Y-m-d H:i:s'),
					'check_in_place' => $location,
					'check_in_photo' => $path
				]);

				// send To mail management
				\Mail::to(env('EMAIL_RECEIVER'))->send(new \App\Mail\CheckIn($member, $path, $location));

				\Mail::to($member->email)->send(new \App\Mail\CheckIn($member, $path, $location));
			}

			return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
        }

		request()->session()->flash( 'status', $status );
		request()->session()->flash( 'message', $message );
		
        return redirect()->route('auth.verify');
    }

	public function uploadFoto()
	{
		if (request()->file( 'file' )) {
			$file_foto = request()->file( 'file' );
	
			$imagePath = request('file')->store('uploads/img/photo', 'public');

			$response = [
				'data' => [
					'status' => true,
					'imagePath' => $imagePath,
				]
			];
		} else {
			$response = [
				'data' => [
					'status' => false,
					'imagePath' => null,
				]
			];
		}
		return response()->json($response);
	}

	public function logoutIndex()
	{
		return view('auth.logout');
	}

	public function logout()
	{
		$member = auth()->user();
		$path = null;

		if (request()->foto) {
			// $imagePath = request('foto')->store('uploads/img/photo', 'public');
			$path = env('SITE_URL') . '/storage/' . request()->foto;
		}

		// $file = request('file')->store('uploads/file/report', 'public');

		$pathFile = null;
		if (request()->file) {
			// Initialize
			$file  				= request()->file('file');
			$destination_path 	= public_path('storage/uploads/file/report/');
			$md5_name 			= uniqid().md5_file($file->getRealPath());
			$ext 				= $file->getClientOriginalExtension();
			$fileSize 			= $file->getSize();

			if ($fileSize <= 500000) { // 5 MB
				if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg' || $ext == 'mp4' || $ext == 'avi' || $ext == 'pdf') {
					$file->move($destination_path,"$md5_name.$ext");
					$pathFile = url('/download/' . "$md5_name.$ext");
				}
			}
		}
		
		$location = request()->location;
		date_default_timezone_set('Asia/Jakarta');
		$time = date('H:i');
		$note = request()->note != null ? request()->note : '-';

		// save attendance
		$attendance = \App\Attendance::where('user_id', $member->id)->whereNull('check_out_datetime')->orderBy('id', 'desc')->first();
		if ($attendance) {
			$attendance->check_out_datetime = date('Y-m-d H:i:s');
			$attendance->check_out_place = $location;
			$attendance->check_out_photo = $path;
			$attendance->report_location = $pathFile;
			$attendance->save();
		}

		// send To mail management
		\Mail::to(env('EMAIL_RECEIVER'))->send(new \App\Mail\CheckOut($member, $path, $location, $pathFile, $time, $note));
		\Mail::to($member->email)->send(new \App\Mail\CheckOut($member, $path, $location, $pathFile, $time, $note));
		
		Auth::logout();

		// Check ajax request
		if(request()->ajax()){
		    return response()->json([
		        'status'    => true,
		        'message'   => 'Checkout Berhasil'
		    ]);

		    die;
		}
		
		return redirect('/');
	}

	public function download($file)
	{
		if ($file != null) {
			return response()->download(public_path('storage/uploads/file/report/' . $file));
		}

		return false;
	}

	public function index(Request $request)
	{
		if (Auth::check()) {
			return redirect()->route('company:home', \Str::slug(auth()->user()->company->Name));
		}
		
		// Delete Session is_demo
		$request->session()->forget('id_demo');

		if (request('package-id')) {
			// Create Session For Select Package
			request()->session()->put([
			    'package-id' => request('package-id')
			]);
		}

		// Initialize
		$textMenu = 'Masuk';

		return view('auth.login', compact('textMenu'));
	}

	public function verifyOtp()
	{
		// dd(session()->all());
		return view('auth.otp');
	}

	public function otpStore()
	{
		$status = 'FAILED';
		$message = "Email atau password salah, atau akun Anda belum aktif, silakan coba lagi";
		
		$check_user = \App\User::where('email', request()->email)->first();

		if (!$check_user) {
			// Initialize
			$password = $this->generateRandomString(6);
			$otp 	  = rand(1111,9999);

			// Create User
			$user = \App\User::create([
						'email' 	=> request()->email,
						'nip' 		=> rand(100000,999999),
						'name'	 	=> '-',
						'role_id' 	=> 1,
						'password' 	=> bcrypt($otp),
						'is_demo' 	=> (Session::get('is_demo')) ? 1 : 0
					]);

			if ($user) {
				$user->otp = $otp;
				\Mail::to($user->email)->send(new \App\Mail\VerificationOtp($user));
			}
	
			$email = $user->email;
		}

		if ($check_user) {
			$otp = rand(1111,9999);
			$check_user->password = bcrypt($otp);
			$check_user->save();
			$check_user->otp = $otp;
			\Mail::to($check_user->email)->send(new \App\Mail\VerificationOtp($check_user));
			$email = $check_user->email;
		}


		$path = null;
		if (request()->foto) {
			// $imagePath = request('foto')->store('uploads/img/photo', 'public');
			$path = env('SITE_URL') . '/storage/' . request()->foto;
		}
		
		$location = request()->location;
		date_default_timezone_set('Asia/Jakarta');

		$data_user['path'] = $path;
		$data_user['time'] = date('H:i');
		$data_user['location'] = $location;
		$data_user['email'] = $email;

		// set session
		request()->session()->put($data_user);

		$status = 'SUCCESS';
		$message = "Silahkan cek email anda untuk verifikasi otp";

		request()->session()->flash( 'status', $status );
		request()->session()->flash( 'message', $message );
		
        return redirect()->route('auth.verify');
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
