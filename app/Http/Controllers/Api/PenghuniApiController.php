<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lokasi;
use App\Models\Penghuni;
use Illuminate\Http\Request;

class PenghuniApiController extends Controller
{
    /**
     * Tampilkan data penghuni beserta kontraknya berdasarkan nama lokasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string',
        ]);

        $namaLokasi = $request->query('nama_lokasi');

        $lokasi = Lokasi::where('nama_lokasi', $namaLokasi)->first();

        if (!$lokasi) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi dengan nama "' . $namaLokasi . '" tidak ditemukan.',
                'data' => []
            ], 404);
        }

        $lokasiId = $lokasi->id;

        // Query data penghuni yang terelasi dengan kontrak di lokasi tersebut
        $penghuni = Penghuni::where(function ($query) use ($lokasiId) {
            $query->whereHas('kontrakAsPenghuni1', function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                });
            })->orWhereHas('kontrakAsPenghuni2', function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                });
            })->orWhereHas('kontrakAsPenghuni3', function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                });
            })->orWhereHas('kontrakAsPenghuni4', function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                });
            });
        })->with([
            'kontrakAsPenghuni1' => function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                })->with('unit.gedung.lokasi');
            },
            'kontrakAsPenghuni2' => function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                })->with('unit.gedung.lokasi');
            },
            'kontrakAsPenghuni3' => function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                })->with('unit.gedung.lokasi');
            },
            'kontrakAsPenghuni4' => function ($q) use ($lokasiId) {
                $q->whereHas('unit.gedung', function ($qg) use ($lokasiId) {
                    $qg->where('lokasi_id', $lokasiId);
                })->with('unit.gedung.lokasi');
            }
        ])->get();

        $result = $penghuni->map(function ($p) {
            $allContracts = collect()
                ->merge($p->kontrakAsPenghuni1)
                ->merge($p->kontrakAsPenghuni2)
                ->merge($p->kontrakAsPenghuni3)
                ->merge($p->kontrakAsPenghuni4)
                ->unique('id')
                ->values()
                ->map(function ($k) {
                    return [
                        'id' => $k->id,
                        'no_kontrak' => $k->no_kontrak,
                        'tipe_kontrak' => $k->tipe_kontrak == 1 ? 'Unit Hunian' : 'Unit RBH',
                        'tgl_awal' => $k->tgl_awal,
                        'tgl_akhir' => $k->tgl_akhir,
                        'status_kontrak' => $k->status_kontrak == 1 ? 'Aktif' : 'Non Aktif',
                        'unit' => [
                            'id' => $k->unit?->id,
                            'nomor' => $k->unit?->nomor,
                            'nama_unit' => $k->unit?->nomor, // fallback
                            'lantai' => $k->unit?->lantai,
                            'tipe_unit' => $k->unit?->tipe_unit,
                            'gedung' => [
                                'id' => $k->unit?->gedung?->id,
                                'nama_gedung' => $k->unit?->gedung?->nama_gedung,
                                'lokasi' => [
                                    'id' => $k->unit?->gedung?->lokasi?->id,
                                    'nama_lokasi' => $k->unit?->gedung?->lokasi?->nama_lokasi,
                                ]
                            ]
                        ]
                    ];
                });

            return [
                'id' => $p->id,
                'nik' => $p->nik,
                'nama' => $p->nama,
                'email' => $p->email,
                'tgl_lahir' => $p->tgl_lahir,
                'no_tlp' => $p->no_tlp,
                'jenis_kelamin' => $p->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan',
                'status_kawin' => $p->status_kawin,
                'agama' => $p->agama,
                'pekerjaan' => $p->pekerjaan,
                'alamat' => $p->alamat,
                'tempat_lahir' => $p->tempat_lahir,
                'kontrak' => $allContracts
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data penghuni berhasil ditemukan.',
            'data' => $result
        ], 200);
    }
}
