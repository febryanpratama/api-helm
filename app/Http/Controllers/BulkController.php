<?php

namespace App\Http\Controllers;

use App\BulkDataError;
use App\BulkDataFile;
use App\Imports\ImportVendor;
use Illuminate\Http\Request;

class BulkController extends Controller
{
    public function bulkStore(Request $request)
    {
        $status = 'no input';
        // get bulk file
        $bulk_data_file = BulkDataFile::where('is_done', '0')->orderBy('id', 'ASC')->first();


        if ($bulk_data_file) {

            if ($bulk_data_file->type == 1) { // vendor
    
                $last_row = $bulk_data_file->number_process_row;
                $row = \Excel::toArray(new ImportVendor, storage_path('app/public/' . $bulk_data_file->file));
    
                $data_row = $row[0][$last_row];
    

                // save to api
                try {
                    // set post fields
                    $ch = curl_init(env('SITE_URL') . '/api/vendor/imports/store-bulk?user_id=' . $bulk_data_file->user_id);
                    // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $auth ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_row);

                    // execute!
                    $response = curl_exec($ch);

                    $response = json_decode($response, true);

                    if ($response['status'] == 'error') {
                        // save error to DB
                        $bulk_data_error = BulkDataError::create([
                            'bulk_data_file_id' => $bulk_data_file->id,
                            'number_row' => $last_row,
                            'value_data' => $data_row,
                            'error_data' => $response['message'],

                        ]);
                    }

                    $bulk_data_file->update(['number_process_row' => $bulk_data_file->number_process_row + 1]);

                    if ($bulk_data_file->total_row == $bulk_data_file->number_process_row) { // update done jika number prosess sudah sama dengan total row
                        $bulk_data_file->update(['is_done' => 1]);
                    }


                    // close the connection, release resources used
                    curl_close($ch);

                } catch (\Throwable $th) {
                    
                    $bulk_data_file->update(['number_process_row' => $bulk_data_file->number_process_row + 1]);

                    // save error to DB
                    $bulk_data_error = BulkDataError::create([
                        'bulk_data_file_id' => $bulk_data_file->id,
                        'number_row' => $last_row,
                        'value_data' => $data_row,
                        'error_data' => json_encode($th->getMessage(), true),

                    ]);

                    if ($bulk_data_file->total_row == $bulk_data_file->number_process_row) { // update done jika number prosess sudah sama dengan total row
                        $bulk_data_file->update(['is_done' => 1]);
                    }
                }

                $status = 'bulk data success';
    
    
    
    
            }
        }



        return $status;

        
    }
}
