<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Company;
use Str;

class CompanyController extends Controller
{
    public function update(Request $request, Company $company)
    {
        // Validate
        if (!$request->institution_name) {
            return response()->json([
                'status'    => false,
                'message'   => 'Nama Lembaga Kursus harus diisi'
            ]);

            die;
        }

        // Initialize
        $avatar = request()->file('avatar');
        $logo   = request()->file('logo');
        $path   = $company->Logo;
        $pathAv = auth()->user()->avatar;

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

                die;
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

        if ($avatar) {
            // Initialize
            $extAvatar = $avatar->getClientOriginalExtension();

            // Check Extension
            if ($extAvatar == 'php' || $extAvatar == 'sql' || $extAvatar == 'js'|| $extAvatar == 'gif') {
                return response()->json([
                    'status'    => false,
                    'message'   => 'Extension Avatar File Not Supported!'
                ]);

                die;
            }

            // Unlink File
            if (auth()->user()->avatar) {
                // Initialize
                $expAvatar = explode('/', auth()->user()->avatar);

                @unlink('storage/uploads/avatar/'.$expAvatar[6]);
            }

            $pathAv = $avatar->store('uploads/avatar', 'public');
            $pathAv = env('SITE_URL').'/storage/'.$pathAv;
        }

        // Update User
        User::where('id', auth()->user()->id)->update(['name' => $request->name, 'phone' => $request->phone, 'avatar' => $pathAv]);

        // Update Company
        $company->update([
            'Phone'     => $request->institution_phone,
            'Email'     => $request->institution_email,
            'Address'   => $request->institution_address,
            'Logo'      => $path,
            'facebook'  => $request->facebook,
            'instagram' => $request->instagram,
            'youtube'   => $request->youtube,
            'linkedin'  => $request->linkedin
        ]);

        // Check Name Company Exists
        $companyNameExists = Company::where(['Name' => $request->institution_name])->first();

        // Check Name Institution
        if ($company->ID == auth()->user()->company_id && $companyNameExists && $companyNameExists->ID == auth()->user()->company_id) {
            // Update Company
            $company->update([
                'Name' => str_replace([',','.','`',"'",'"','-','_','(',')','*','^','&','$','#','@','!','+','=','~','?','/','|'], '', $request->institution_name)
            ]);
        } else if ($company->ID == auth()->user()->company_id && !$companyNameExists) {
            // Update Company
            $company->update([
                'Name' => str_replace([',','.','`',"'",'"','-','_','(',')','*','^','&','$','#','@','!','+','=','~','?','/','|'], '', $request->institution_name)
            ]);
        } else if ($companyNameExists && $companyNameExists->ID != auth()->user()->company_id) {
           return response()->json([
               'status'    => false,
               'message'   => 'Nama Lembaga Kursus sudah terdaftar oleh user lain.'
           ]); 
        }

        return response()->json([
            'status'        => true,
            'message'       => 'Data berhasil diperbarui',
            'company_name'  => Str::slug(auth()->user()->company->Name)
        ]);
    }
}
