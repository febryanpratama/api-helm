<?php

namespace App\Utils;

class ResponseCode {
    static function succesGet($message, $data){
        return response()->json([
            'status' => true,
            'code' => 200,
            'message' => $message ?? 'Data berhasil ditemukan',
            'result' => $data ?? null
        ],200);
    }

    static function succesCreate($message){
        return response()->json([
            'status' => true,
            'code' => 201,
            'message' => $message ?? 'Data berhasil ditambahkan',
        ],201);
    }

    static function succesUpdate($message, $data){
        return response()->json([
            'status' => true,
            'code' => 201,
            'message' => $message ?? 'Data berhasil diubah',
            'result' => $data
        ],201);
    }

    static function succesDelete($message){
        return response()->json([
            'status' => true,
            'code' => 201,
            'message' => $message ?? 'Data berhasil dihapus',
        ],200);
    }

    static function errorPost($message){
        return response()->json([
            'status' => false,
            'code' => 400,
            'message' => $message ?? 'Terjadi kesalahan',
        ],400);
    }

    static function errorServer($message){
        return response()->json([
            'status' => false,
            'message' => $message ?? 'Terjadi kesalahan',
        ],500);
    }
}