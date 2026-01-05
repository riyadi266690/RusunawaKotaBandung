<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pegawai\storePegawai;
use App\Http\Requests\Pegawai\updatePegawai;
use App\Http\Resources\Pegawai\PegawaiResource;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function allDataPegawai()
    {
        $data = Pegawai::all();

        return response()->json([
            'success' => true,
            'data' => PegawaiResource::collection($data)
        ]);
    }

    function storePegawai(storePegawai $request)
    {
        $pegawai = Pegawai::create($request->all());
        return response()->json([
            'success' => true,
            'data' => $pegawai
        ]);
    }

    function editPegawai(updatePegawai $request, Pegawai $pegawai)
    {
        $pegawai->update($request->all());
        return response()->json([
            'success' => true,
            'data' => $pegawai
        ]);
    }

    function destroyPegawai(Pegawai $pegawai)
    {
        $pegawai->delete();
        return response()->json([
            'success' => true,
            'message' => 'Data pegawai berhasil dihapus'
        ]);
    }
}
