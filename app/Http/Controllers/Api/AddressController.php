<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MasterLocation;
use App\Address;

class AddressController extends Controller
{
    public function search()
    {
        // Initialize
        $q = request('q');

        if (!$q) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Masukkan minimal 3 huruf'
            ]);
        }

        // Initialize
        $data = MasterLocation::where('status_kecamatan', '1')->where('kecamatan','LIKE','%'.$q.'%')->orderBy('kecamatan', 'ASC')->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function index()
    {
        // Check If Role ID 1
        if (auth()->user()->role_id == 1 && auth()->user()->company_id == null) {
            return response()->json([
                'status'    => false,
                'message'   => 'Anda belum mendaftarkan toko.'
            ]);
        }

        // Initialize
        $data = [];

        if (auth()->user()->role_id == 1) {
            // Initialize
            $data = Address::with('masterLocation')->where('company_id', auth()->user()->company_id)->get();
        } else {
            // Initialize
            $data = Address::with('masterLocation')->where('user_id', auth()->user()->id)->get();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function store(Request $request)
    {
        // Check If Role ID 1
        if (auth()->user()->role_id == 1 && auth()->user()->company_id == null) {
            return response()->json([
                'status'    => false,
                'message'   => 'Anda belum mendaftarkan toko.'
            ]);
        }
        
        // Validation
        $validated = $request->validate([
            'district_id'       => 'required',
            'details_address'   => 'required',
            // 'latitude'          => 'required',
            // 'longitude'         => 'required'
        ]);

        // Check Account
        if (auth()->user()->role_id == 1) {
            // Check Address
            $exists = Address::where([
                'company_id'    => auth()->user()->company_id,
                'district_id'   => request('district_id') 
            ])->first();

            if ($exists) {
                return response()->json([
                    'status'    => 'error',
                ]);
            }
        }

        if (request('main_address') == 1) {
            Address::where('user_id', auth()->user()->id)->update(['main_address' => 0]);
        } else {
            // Update First Address
            $firstAddress = Address::where('user_id', auth()->user()->id)->first();
            $mainAddress  = Address::where(['user_id' => auth()->user()->id, 'main_address' => 1])->first();

            if ($firstAddress && !$mainAddress) {
                $firstAddress->update([
                    'main_address' => 1
                ]);
            }
        }

        $data = Address::create([
            'user_id'           => auth()->user()->id,
            'company_id'        => auth()->user()->company_id,
            'district_id'       => request('district_id'),
            'is_company'        => (auth()->user()->company_id) ? 1 : 0,
            'main_address'      => (request('main_address')) ? request('main_address') : 0,
            'details_address'   => request('details_address'),
            'latitude'          => request('latitude'),
            'longitude'         => request('longitude')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menambahkan data.',
            'data'      => $data
        ]);
    }

    public function show($id)
    {
        // Initialize
        $data = Address::with('masterLocation')->where('id', $id)->first();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        // Validation
        $validated = $request->validate([
            'district_id'       => 'required',
            'details_address'   => 'required',
            // 'latitude'          => 'required',
            // 'longitude'         => 'required'
        ]);

        // Check Address
        $address = Address::where([
            'id' => $id
        ])->first();

        if (!$address) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Alamat tidak ditemukan.'
            ]);
        }

        if ($address->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Anda tidak memiliki akses.'
            ]);
        }

        if (request('main_address') == 1) {
            Address::where('user_id', auth()->user()->id)->update(['main_address' => null]);
        }

        $address->update([
            'district_id'       => request('district_id'),
            'details_address'   => request('details_address'),
            'main_address'      => request('main_address'),
            'latitude'          => request('latitude'),
            'longitude'         => request('longitude')
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mengubah data.',
            'data'      => $address
        ]);
    }

    public function destroy($id)
    {
        // Initialize
        $data = Address::where('id', $id)->first();

        if ($data) {
            // Check user account
            if ($data->user_id != auth()->user()->id) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Anda tidak memiliki akses.'
                ]);
            }

            $data->delete();
        }

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $id
            ]
        ]);
    }

    public function listCity()
    {
        // Initialize
        $q = request('q');

        if (!$q) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Masukkan minimal 3 huruf'
            ]);
        }

        // Initialize
        $data = \DB::table('master_kota')->whereNotNull('provinsi_id')->where('kota','LIKE','%'.$q.'%')->orderBy('kota', 'ASC')->get();

        return response()->json([
            'status'    => 'success',
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ]);
    }
}
