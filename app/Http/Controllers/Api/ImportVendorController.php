<?php

namespace App\Http\Controllers\Api;

use App\Address;
use App\BulkDataError;
use App\BulkDataFile;
use App\Company;
use App\Course;
use App\Http\Controllers\Controller;
use App\Imports\ImportVendor;
use App\MasterLocation;
use App\User;
use Illuminate\Http\Request;
use Validator;

class ImportVendorController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        $auth_user = User::find($request->get('user_id'));
        // Validation
        $validator = Validator::make(request()->all(), [
            'nama_pengguna'                     => 'required|max:255',
            'email_pengguna'                    => 'required|email|max:191',
            'telp_pengguna'                     => 'nullable|max:20',

            'nama_perusahaan'                   => 'required',
            'email_perusahaan'                  => 'required',
            'telp_perusahaan'                   => 'required',
            'alamat_perusahaan'                 => 'required',
            'kecamatan_perusahaan'              => 'required',

            'nama_produk'                       => 'required',
            'deskripsi_produk'                  => 'nullable',
            'harga_produk'                      => 'required|numeric',
        ],
        [
            'nama_pengguna.required' => 'Nama Pengguna wajib diisi',
            'email_pengguna.required' => 'Email Pengguna wajib diisi',

            'nama_perusahaan.required' => 'Nama Perusahaan wajib diisi',
            'email_perusahaan.required' => 'Email Perusahaan wajib diisi',
            'telp_perusahaan.required' => 'Telp Perusahaan wajib diisi',
            'alamat_perusahaan.required' => 'Alamat Perusahaan wajib diisi',
            'kecamatan_perusahaan.required' => 'Kecamatan Perusahaan wajib diisi',

            'nama_produk.required' => 'Nama Produk wajib diisi',
            'harga_produk.required' => 'Nama Produk wajib diisi',
            'harga_produk.numeric' => 'Nama Produk wajib diisi angka',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        // cek kecamatan
        $district = MasterLocation::where('status_kecamatan', '1')->where('kecamatan','LIKE','%'.$request->kecamatan_perusahaan.'%')->first();

        if (!$district) {
            $district = MasterLocation::where('kecamatan','LIKE','%'.$request->kecamatan_perusahaan.'%')->first();
        }

        if (!$district) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Nama perusahaan Kecamatan ('.request('kecamatan_perusahaan').') tidak ditemukan.'
            ]);
        }

        $company = Company::where([
            'Name' => $request->nama_perusahaan,
            'Phone' => $request->telp_perusahaan,
            'Address' => $request->alamat_perusahaan,
            'Email' => $request->email_perusahaan,
            'Name' => $request->nama_perusahaan,
            'status' => 2,
            'is_verified' => 1
        ])->first();

        if (!$company) {
            $company = Company::create([
                'Name' => $request->nama_perusahaan,
                'Phone' => $request->telp_perusahaan,
                'Address' => $request->alamat_perusahaan,
                'Email' => $request->email_perusahaan,
                'Name' => $request->nama_perusahaan,
                'AddedTime' => time(),
                'AddedByIP' => $request->ip(),
                'status' => 2,
                'is_verified' => 1
            ]);
        }

        $user = User::where([
            'name' => $request->nama_pengguna,
            'company_id' => $company->ID,
            'email' => $request->email_pengguna,
            'phone' => $request->telp_pengguna,
            'role_id' => 1,
            'is_active' => 'y',
        ])->first();

        if (!$user) {

            $user = User::create([
                'name' => $request->nama_pengguna,
                'company_id' => $company->ID,
                'email' => $request->email_pengguna,
                'phone' => $request->telp_pengguna,
                'password' => bcrypt(rand(1111,9999)),
                'role_id' => 1,
                'is_active' => 'y',
                'referral_code' => $this->generateRandomString(6),
            ]);
        }

        $address = Address::where([
            'company_id' => $company->ID,
            'user_id' => $user->id,
            'district_id' => $district->id,
            'main_address' => 1,
            'is_company' => 1,
            'details_address' => $request->alamat_perusahaan,

        ])->first();

        if (!$address) {
            $address = Address::create([
                'company_id' => $company->ID,
                'user_id' => $user->id,
                'district_id' => $district->id,
                'main_address' => 1,
                'is_company' => 1,
                'details_address' => $request->alamat_perusahaan,
    
            ]);
        }

        $product = Course::create([
            'user_id' => $user->id,
            'name' => $request->nama_produk,
            'description' => $request->deskripsi_produk,
            'price' => $request->harga_produk,
            'price_num' => $request->harga_produk,
            'commission' => 5,
            'slug'      => \Str::slug($request->nama_produk.'-'.$user->company->Name.'-'.$user->id.date('Yds'), '-'),
            'course_package_category' => 1,
        ]);

        if (!$product) {
            $product = Course::create([
                'user_id' => $user->id,
                'name' => $request->nama_produk,
                'description' => $request->deskripsi_produk,
                'price' => $request->harga_produk,
                'price_num' => $request->harga_produk,
                'commission' => 5,
                'slug'      => \Str::slug($request->nama_produk.'-'.$user->company->Name.'-'.$user->id.date('Yds'), '-'),
                'course_package_category' => 1,
            ]);
        }

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        

        return response()->json([
            'status'    => 'success',
            'message'   => 'User berhasil ditambahkan.',
            'data'      => $user
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function importBulk(Request $request)
    {
        $auth_user = User::find($request->get('user_id'));
        // Validation
        $validator = Validator::make(request()->all(), [
            'nama_pengguna'                     => 'required|max:255',
            'email_pengguna'                    => 'required|email|max:191',
            'telp_pengguna'                     => 'nullable|max:20',

            'nama_perusahaan'                   => 'required',
            'email_perusahaan'                  => 'required',
            'telp_perusahaan'                   => 'required',
            'alamat_perusahaan'                 => 'required',
            'kecamatan_perusahaan'              => 'required',

            'nama_produk'                       => 'required',
            'deskripsi_produk'                  => 'nullable',
            'harga_produk'                      => 'required|numeric',
        ],
        [
            'nama_pengguna.required' => 'Nama Pengguna wajib diisi',
            'email_pengguna.required' => 'Email Pengguna wajib diisi',

            'nama_perusahaan.required' => 'Nama Perusahaan wajib diisi',
            'email_perusahaan.required' => 'Email Perusahaan wajib diisi',
            'telp_perusahaan.required' => 'Telp Perusahaan wajib diisi',
            'alamat_perusahaan.required' => 'Alamat Perusahaan wajib diisi',
            'kecamatan_perusahaan.required' => 'Kecamatan Perusahaan wajib diisi',

            'nama_produk.required' => 'Nama Produk wajib diisi',
            'harga_produk.required' => 'Nama Produk wajib diisi',
            'harga_produk.numeric' => 'Nama Produk wajib diisi angka',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        // cek kecamatan
        $district = MasterLocation::where('status_kecamatan', '1')->where('kecamatan','LIKE','%'.$request->kecamatan_perusahaan.'%')->first();

        if (!$district) {
            $district = MasterLocation::where('kecamatan','LIKE','%'.$request->kecamatan_perusahaan.'%')->first();
        }

        if (!$district) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Nama perusahaan Kecamatan ('.request('kecamatan_perusahaan').') tidak ditemukan.'
            ]);
        }

        $company = Company::where([
            'Name' => $request->nama_perusahaan,
            'Phone' => $request->telp_perusahaan,
            'Address' => $request->alamat_perusahaan,
            'Email' => $request->email_perusahaan,
            'Name' => $request->nama_perusahaan,
            'status' => 2,
            'is_verified' => 1
        ])->first();

        if (!$company) {
            $company = Company::create([
                'Name' => $request->nama_perusahaan,
                'Phone' => $request->telp_perusahaan,
                'Address' => $request->alamat_perusahaan,
                'Email' => $request->email_perusahaan,
                'Name' => $request->nama_perusahaan,
                'AddedTime' => time(),
                'AddedByIP' => $request->ip(),
                'status' => 2,
                'is_verified' => 1
            ]);
        }

        $user = User::where([
            'name' => $request->nama_pengguna,
            'company_id' => $company->ID,
            'email' => $request->email_pengguna,
            'phone' => $request->telp_pengguna,
            'role_id' => 1,
            'is_active' => 'y',
        ])->first();

        if (!$user) {

            $user = User::create([
                'name' => $request->nama_pengguna,
                'company_id' => $company->ID,
                'email' => $request->email_pengguna,
                'phone' => $request->telp_pengguna,
                'password' => bcrypt(rand(1111,9999)),
                'role_id' => 1,
                'is_active' => 'y',
                'referral_code' => $this->generateRandomString(6),
            ]);
        }

        $address = Address::where([
            'company_id' => $company->ID,
            'user_id' => $user->id,
            'district_id' => $district->id,
            'main_address' => 1,
            'is_company' => 1,
            'details_address' => $request->alamat_perusahaan,

        ])->first();

        if (!$address) {
            $address = Address::create([
                'company_id' => $company->ID,
                'user_id' => $user->id,
                'district_id' => $district->id,
                'main_address' => 1,
                'is_company' => 1,
                'details_address' => $request->alamat_perusahaan,
    
            ]);
        }

        $product = Course::create([
            'user_id' => $user->id,
            'name' => $request->nama_produk,
            'description' => $request->deskripsi_produk,
            'price' => $request->harga_produk,
            'price_num' => $request->harga_produk,
            'commission' => 5,
            'slug'      => \Str::slug($request->nama_produk.'-'.$user->company->Name.'-'.$user->id.date('Yds'), '-'),
            'course_package_category' => 1,
        ]);

        if (!$product) {
            $product = Course::create([
                'user_id' => $user->id,
                'name' => $request->nama_produk,
                'description' => $request->deskripsi_produk,
                'price' => $request->harga_produk,
                'price_num' => $request->harga_produk,
                'commission' => 5,
                'slug'      => \Str::slug($request->nama_produk.'-'.$user->company->Name.'-'.$user->id.date('Yds'), '-'),
                'course_package_category' => 1,
            ]);
        }

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        

        return response()->json([
            'status'    => 'success',
            'message'   => 'User berhasil ditambahkan.',
            'data'      => $user
        ]);
    }

    public function downloadImportTemplate()
    {
        return response()->json([
            'status'    => 'success',
            'message'   => 'file template import',
            'data'      => asset('docs/import_vendor.xlsx')
        ]);
    }

    public function uploadImport(Request $request)
    {
        // Validation
        $validator = Validator::make(request()->all(), [
            'file'                  => 'required|mimes:xlsx,csv,xls',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'    => 'error',
                'message'   => $validator->errors()->first()
            ];

            return response()->json($data);
        }

        // $file = request()->file('file')->store('uploads/import/invite', 'public');
        // $file = env('SITE_URL') . '/storage/' . $file;

        $extension = request('file')->getClientOriginalExtension();
        $filename = request('file')->getClientOriginalName();

        $filename = $filename . '-' . date('Y-m-d h.i.s');

        $path = request('file')->storeAs('uploads/import-bulk', $filename . '.' . $extension, 'public');
        $rows = \Excel::toArray(new ImportVendor, $request->file);
        $file = env('SITE_URL') . '/storage/' . $path;

        $bulk_data_file = BulkDataFile::create([
            'file' => $path,
            'type' => 1, // 1 = import vendor
            'total_row' => count($rows[0]),
            'number_process_row' => 0,
            'user_id' => auth()->user()->id
        ]);


        return response()->json([
            'status'    => 'success',
            'message'   => 'upload template import berhasil, import dalam antrian',
            'data'      => $file
        ]);
    }

    public function bulkFile()
    {
        $bulk_data = BulkDataFile::where('user_id', auth()->user()->id)->where('type', 1)->orderBy('id', 'DESC')->paginate(20);
        return response()->json([
            'status'    => 'success',
            'message'   => 'List Bulk import file',
            'data'      => $bulk_data
        ]);
    }

    public function bulkFileError(BulkDataFile $bulk_data)
    {
        $bulk_data_error = BulkDataError::where('bulk_data_file_id', $bulk_data->id)->paginate(20);
        return response()->json([
            'status'    => 'success',
            'message'   => 'Detail Error Bulk import file',
            'data'      => $bulk_data_error
        ]);
    }

    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }
}
