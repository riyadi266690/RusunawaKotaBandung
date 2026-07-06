<?php

namespace Tests\Feature;

use App\Models\Gedung;
use App\Models\Kontrak;
use App\Models\Lokasi;
use App\Models\Penghuni;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenghuniApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test API access without credentials returns 401.
     */
    public function test_api_requires_credentials(): void
    {
        $response = $this->getJson('/api/penghuni?nama_lokasi=Rusun');
        $response->assertStatus(401);
    }

    /**
     * Test API access with invalid credentials returns 401.
     */
    public function test_api_rejects_invalid_credentials(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'salah',
            'PHP_AUTH_PW' => 'salahjuga',
        ])->getJson('/api/penghuni?nama_lokasi=Rusun');

        $response->assertStatus(401);
    }

    /**
     * Test API access with valid credentials but missing parameter returns 422.
     */
    public function test_api_requires_nama_lokasi_parameter(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'tes',
            'PHP_AUTH_PW' => 'pasword',
        ])->getJson('/api/penghuni');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nama_lokasi']);
    }

    /**
     * Test API with valid credentials but non-existent lokasi returns 404.
     */
    public function test_api_returns_404_if_lokasi_not_found(): void
    {
        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'tes',
            'PHP_AUTH_PW' => 'pasword',
        ])->getJson('/api/penghuni?nama_lokasi=LokasiPalsu');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Lokasi dengan nama "LokasiPalsu" tidak ditemukan.'
            ]);
    }

    /**
     * Test API returns data correctly when lokasi exists.
     */
    public function test_api_returns_penghuni_data_with_contracts(): void
    {
        // Create Lokasi
        $lokasi = Lokasi::create([
            'nama_lokasi' => 'Rusunawa Bandung',
            'kepala_lokasi' => 'Kepala Rusun',
            'alamat_lokasi' => 'Jl. Rusunawa'
        ]);

        // Create Gedung
        $gedung = Gedung::create([
            'lokasi_id' => $lokasi->id,
            'nama_gedung' => 'Gedung A',
            'tipe_gedung' => 'Hunian'
        ]);

        // Create Unit
        $unit = Unit::create([
            'gedung_id' => $gedung->id,
            'nomor' => '101',
            'lantai' => '1',
            'tipe_unit' => 'Hunian'
        ]);

        // Create Penghuni
        $penghuni = Penghuni::create([
            'nik' => 'encrypted_nik',
            'nama' => 'encrypted_nama',
            'email' => 'encrypted_email',
            'tgl_lahir' => '1990-01-01',
            'no_tlp' => 'encrypted_phone',
            'jenis_kelamin' => 1,
            'status_kawin' => 1,
            'agama' => 1,
            'tempat_lahir' => 'Bandung'
        ]);

        // Create Kontrak
        $kontrak = Kontrak::create([
            'unit_id' => $unit->id,
            'no_kontrak' => 'KONTRAK/001/2026',
            'tipe_kontrak' => 1,
            'tgl_awal' => '2026-01-01',
            'tgl_akhir' => '2027-01-01',
            'status_kontrak' => 1,
            'nama_pihak1' => 'Kepala Rusun',
            'status_ttd' => 1,
            'penghuni_id1' => $penghuni->id
        ]);

        $response = $this->withHeaders([
            'PHP_AUTH_USER' => 'tes',
            'PHP_AUTH_PW' => 'pasword',
        ])->getJson('/api/penghuni?nama_lokasi=Rusunawa Bandung');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data penghuni berhasil ditemukan.'
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'nik',
                        'nama',
                        'email',
                        'tgl_lahir',
                        'no_tlp',
                        'jenis_kelamin',
                        'status_kawin',
                        'agama',
                        'pekerjaan',
                        'alamat',
                        'tempat_lahir',
                        'kontrak' => [
                            '*' => [
                                'id',
                                'no_kontrak',
                                'tipe_kontrak',
                                'tgl_awal',
                                'tgl_akhir',
                                'status_kontrak',
                                'unit' => [
                                    'id',
                                    'nama_unit',
                                    'gedung' => [
                                        'id',
                                        'nama_gedung',
                                        'lokasi' => [
                                            'id',
                                            'nama_lokasi'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
