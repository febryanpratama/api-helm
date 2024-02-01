<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CourseInstituteResource;
use App\Company;
use App\MasterLocation;
use App\User;
use Validator;

class CourseInstituteController extends Controller
{
    public function show()
    {
        // Initialize
        $courseInstitution = Company::with('user')->where('id', auth()->user()->company_id)->first();

        if (!$courseInstitution) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda belum mendaftarkan Toko.'
            ]);
        }

        return new CourseInstituteResource($courseInstitution);
    }

    public function update(Request $request, $id)
    {
        /*
            Notes :
            $id tidak terpakai
         */
        
        // Validation
        $validator = Validator::make(request()->all(), [
            'name'      => 'required',
            'address'   => 'required',
            'status'    => 'required',
            'city_id'   => 'required'
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => false,
                'message'   => $validator->errors()->first(),
                'code'      => 400
            ];

            return response()->json($data, 400);
        }
        
        // Check Company By User
        $company = Company::where('id', auth()->user()->company_id)->first();

        if (!$company) {
            // Check Company By Name
            $companyName   = str_replace([',','.','`',"'",'"','-','_','(',')','*','^','&','$','#','@','!','+','=','~','?','/','|'], '', request('name'));
            $companyExists = Company::where('Name', 'like', '%'.$companyName.'%')->first();

            if ($companyExists) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Toko dengan nama '.request('name').' sudah terdaftar.'
                ]);
            }

            // Check City
            if (request('from_web')) {
                // Initialize
                $cityId = MasterLocation::where('kota', request('city_id'))->first();

                if (!$cityId) {
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Kota ('.request('city_id').') tidak ditemukan.'
                    ]);
                }
            }

            // Initialize
            $logo = request()->file('logo');

            if ($logo) {
                // Initialize
                $extLogo = $logo->getClientOriginalExtension();

                // Check Extension
                if ($extLogo == 'php' || $extLogo == 'sql' || $extLogo == 'js'|| $extLogo == 'gif') {
                    return response()->json([
                        'status'    => false,
                        'message'   => 'Extension Logo File Not Supported!'
                    ]);
                }

                $path = $logo->store('uploads/'.$companyName.'/logo', 'public');
                $path = env('SITE_URL').'/storage/'.$path;
            } else {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Logo harus diisi.'
                ]);
            }

            if (request('from_web')) {
                $cityId = $cityId->kota_id;
            } else {
                $cityId = $request->city_id ? $request->city_id : null;
            }

            $company = Company::create([
                'Name'          => $companyName,
                'Email'         => auth()->user()->email,
                'Phone'         => request('phone'),
                // 'Description'   => request('description'),
                'Address'       => request('address'),
                'Logo'          => $path,
                'facebook'      => request('facebook_url'),
                'instagram'     => request('instagram_url'),
                'youtube'       => request('youtube_url'),
                'linkedin'      => request('linkedin_url'),
                'AddedTime'     => time(),
                'AddedByIP'     => '127.0.0.1',
                'status'        => $request->status ? $request->status : null,
                'city_id'       => $cityId,
            ]);

            $user = User::where('id', auth()->user()->id)->update([
                'company_id' => $company->ID
            ]);

            // $transaction = Transaction::create([
            //     'IDClient'              => auth()->user()->id,
            //     'DesktopPriceFinal'     => 0,
            //     'IDPackage'             => 1,
            //     'AddedTime'             => time(),
            //     'AddedByIP'             => '127.0.0.1',
            //     'StartDateTime'         => time(),
            //     'EndDateTime'           => time()
            // ]);

            return new CourseInstituteResource($company);
        }

        // Initialize
        $logo    = request()->file('logo');
        $company = Company::where('id', auth()->user()->company_id)->first();
        $path    = $company->Logo;

        if (request('from_web')) {
            // Initialize
            $cityId = MasterLocation::where('kota', request('city_id'))->first();

            if (!$cityId) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Kota ('.request('city_id').') tidak ditemukan.'
                ]);
            }

            $cityId = $cityId->kota_id;
        } else {
            $cityId = $request->city_id ? $request->city_id : null;
        }

        // Check Upload File
        if ($logo) {
            // Initialize
            $extLogo = $logo->getClientOriginalExtension();

            // Check Extension
            if ($extLogo == 'php' || $extLogo == 'sql' || $extLogo == 'js'|| $extLogo == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Logo File Not Supported!'
                ]);
            }

            // Unlink File
            if ($company->Logo) {
                // Initialize
                $expLogo = explode('/', $company->Logo);

                @unlink('storage/uploads/'.auth()->user()->company->Name.'/logo/'.$expLogo[7]);
            }

            $path = $logo->store('uploads/'.auth()->user()->company->Name.'/logo', 'public');
            $path = env('SITE_URL').'/storage/'.$path;
        }

        // Update Course Institute
        $company->update([
            'Name'      => $request->name,
            'Phone'     => $request->phone,
            'Email'     => $request->email,
            'Address'   => $request->address,
            'Logo'      => $path,
            'facebook'  => $request->facebook_url,
            'instagram' => $request->instagram_url,
            'youtube'   => $request->youtube_url,
            'linkedin'  => $request->linkedin_url,
            'status'    => $request->status ? $request->status : null,
            'city_id'   => $cityId
        ]);

        return new CourseInstituteResource($company);
    }
}
