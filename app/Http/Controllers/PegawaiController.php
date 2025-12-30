<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index()
    {
        $data = Pegawai::all();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}