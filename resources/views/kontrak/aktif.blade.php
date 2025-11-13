@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
  <!-- DataTables Responsive CSS -->
  <!-- DataTables Responsive CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <link href="{{ asset('assets/plugins/select2/select2bs5.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2-bootstrap-5-theme.min.css') }}" />

@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Kontrak</a></li>
    <li class="breadcrumb-item active" aria-current="page">Aktif</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">Data Kontrak Aktif</h6>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addKontrakModal" id="addKontrakBtn">
            Tambah Kontrak Baru
          </button>
        </div>
        <p class="text-muted">Daftar kontrak yang sedang aktif.</p>
        <div class="table-responsive">
          <table id="DTKontrakAktif" class="table">
            <thead>
                <tr>
                    <!-- Kolom untuk tombol expand child row -->
                    <th></th>
                    <th>ID</th>
                    <th>No. Kontrak</th>
                    <th>Unit</th>
                    <th>Tipe Kontrak</th>
                    <th>Tgl. Awal</th>
                    <th>Tgl. Akhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- DataTables will populate this -->
            </tbody>
          </table>
        </div>
      </div> 
    </div>
  </div>
</div> <!-- row -->

<!-- Modal Tambah/Edit Kontrak -->
<div class="modal fade" id="addKontrakModal" tabindex="-1" aria-labelledby="addKontrakModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addKontrakModalLabel">Tambah Data Kontrak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form id="addKontrakForm" method="POST">
          @csrf
          <input type="hidden" name="_method" value="POST" id="kontrakFormMethod">
          <input type="hidden" name="id" id="kontrakId">
          <input type="hidden" name="status_kontrak" value="1"> {{-- Default untuk kontrak aktif --}}
        <div class="row">
        <div class="col-md-12">
          <div class="mb-3">
            <label for="unit_id" class="form-label">Unit</label>
            <select class="form-select select2-modal" id="unit_id" name="unit_id" required>
              <option value="">Pilih Unit</option>
              <!-- Options will be loaded via AJAX -->
            </select>
          </div>
          <div class="mb-3">
            <label for="penghuni_ids" class="form-label">Penghuni</label>
            <select class="form-select select2-modal" id="penghuni_ids" name="penghuni_ids[]" multiple="multiple" required>
              <option value="">Pilih Penghuni</option>
              <!-- Options will be loaded dynamically by Select2 AJAX -->
            </select>
          </div>
          </div> <!-- col-md-12 -->
        <div class="col-md-6">
          <div class="mb-3">
            <label for="no_kontrak" class="form-label">Nomor Kontrak</label>
            <input type="text" class="form-control" id="no_kontrak" name="no_kontrak" required>
          </div>
          <div class="mb-3">
            <label for="harga_sewa" class="form-label">Harga Sewa</label>
            <input type="integer" class="form-control" id="harga_sewa" name="harga_sewa" required>
          </div>          
          <div class="mb-3">
            <label for="tipe_kontrak_display" class="form-label">Tipe Kontrak</label>
            <!-- Input ini hanya untuk menampilkan teks, tidak dikirim ke server -->
            <input type="text" class="form-control" id="tipe_kontrak_display" readonly required>
            <small class="text-muted">Tipe kontrak akan otomatis terisi berdasarkan Unit yang dipilih.</small>
          </div>
          <!-- Input tersembunyi ini akan menyimpan nilai 1 atau 2 dan dikirim ke server -->
          <input type="hidden" id="tipe_kontrak" name="tipe_kontrak">
          <div class="mb-3">
            <label for="tgl_awal" class="form-label">Tanggal Awal Kontrak</label>
            <input type="date" class="form-control" id="tgl_awal" name="tgl_awal" required>
          </div>
          <div class="mb-3">
            <label for="tgl_akhir" class="form-label">Tanggal Akhir Kontrak</label>
            <input type="date" class="form-control" id="tgl_akhir" name="tgl_akhir" required>
          </div>
          <div class="mb-3">
            <label for="nama_pihak1" class="form-label">Nama Pihak 1 (Kepala Lokasi)</label>
            <input type="text" class="form-control" id="nama_pihak1" name="nama_pihak1" readonly required>
            <small class="text-muted">Nama Pihak 1 akan otomatis terisi berdasarkan Lokasi Unit.</small>
          </div>
          <div class="mb-3">
            <label for="status_ttd" class="form-label">Status Tanda Tangan</label>
            <select class="form-select" id="status_ttd" name="status_ttd" required>
              <option value="0">Draft</option>
              <option value="1">Sudah TTD</option>
            </select>
          </div>

          </div> <!-- col-md-6 -->
          <div class="col-md-6">
          <div class="mb-3">
            <label for="harga_air" class="form-label">Harga Air (Optional)</label>
            <input type="integer" class="form-control" id="harga_air" name="harga_air">
          </div>
          <div class="mb-3">
            <label for="jenis_usaha" class="form-label">Jenis Usaha (Optional)</label>
            <input type="text" class="form-control" id="jenis_usaha" name="jenis_usaha">
          </div>
          <div class="mb-3">
            <label for="luas_usaha" class="form-label">Luas Usaha (m²) (Optional)</label>
            <input type="double" class="form-control" id="luas_usaha" name="luas_usaha">
          </div>
          </div> <!-- col-md-6 -->
          </div> <!-- row -->
          

          <!-- Hidden inputs for each penghuni_id -->
          <input type="hidden" name="penghuni_id1" id="penghuni_id1">
          <input type="hidden" name="penghuni_id2" id="penghuni_id2">
          <input type="hidden" name="penghuni_id3" id="penghuni_id3">
          <input type="hidden" name="penghuni_id4" id="penghuni_id4">

          <button type="submit" class="btn btn-primary" id="submitKontrakBtn">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Putus Kontrak -->
<div class="modal fade" id="putusKontrakModal" tabindex="-1" aria-labelledby="putusKontrakModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="putusKontrakModalLabel">Putus Kontrak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="putusKontrakForm">
          @csrf
          <input type="hidden" id="putusKontrakId" name="id">
          <div class="mb-3">
            <label for="tgl_keluar" class="form-label">Tanggal Keluar</label>
            <input type="date" class="form-control" id="tgl_keluar" name="tgl_keluar" required>
          </div>
          <div class="mb-3">
            <label for="masa_kontrak_putus" class="form-label">Masa Kontrak Berjalan</label>
            <input type="text" class="form-control" id="masa_kontrak_putus" readonly>
            <small class="text-muted">Masa kontrak akan otomatis terhitung.</small>
          </div>
          <button type="submit" class="btn btn-primary" id="submitPutusKontrakBtn">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>

<style>
  .table td .text-wrap {
    white-space: pre-wrap;
    word-wrap: break-word;
  }
</style>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('assets/plugins/select2/select2.min.js') }}"></script>

  <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/data-table.js') }}"></script>
<script>
    // URL Constants
    const ajaxUrlKontrakAktif = @json(route('kontrak.ajax.DTKontrakAktif'));
    const storeKontrakUrl = @json(route('kontrak.store'));
    const editKontrakUrlTemplate = @json(route('kontrak.edit', ['kontrak' => ':id']));
    const updateKontrakUrlTemplate = @json(route('kontrak.update', ['kontrak' => ':id']));
    const deleteKontrakUrlTemplate = @json(route('kontrak.destroy', ['kontrak' => ':id']));
    const getUnitOptionsUrl = @json(route('kontrak.unit.options')); // New route for unit dropdown
    const getPenghuniOptionsUrl = @json(route('kontrak.penghuni.options')); // New route for penghuni dropdown
    const getUnitDetailsUrl = @json(route('kontrak.unit.details', ['unit' => ':id'])); // New route for unit details (tipe_unit, kepala_lokasi)
     // Tambahkan baris ini di sini
    const putusKontrakUrlTemplate = @json(route('kontrak.putus', ['kontrak' => ':id']));

    $(document).ready(function() {
        'use strict';

        var childRowCss = `
              <style>
                  table.dataTable.dtr-inline.collapsed > tbody > tr > td.child, 
                  table.dataTable.dtr-inline.collapsed > tbody > tr > th.child, 
                  table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td:first-child, 
                  table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th:first-child {
                      padding-left: 50px !important;
                  }
              </style>
          `;
          
          // Menambahkan CSS ke head dokumen
          $('head').append(childRowCss);
        // Initialize DTKontrakAktif DataTable
       var table = $('#DTKontrakAktif').DataTable({
        processing: true,
        serverSide: true,
        ajax: ajaxUrlKontrakAktif,
        responsive: true,
        columns: [
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: ''
            },
            { data: 'id', name: 'id' },
            { data: 'no_kontrak', name: 'no_kontrak' },
            { data: 'unit_info', name: 'unit_info', orderable: false, searchable: false }, // Combined unit info
            { data: 'tipe_kontrak_label', name: 'tipe_kontrak', orderable: false }, // Label for tipe_kontrak
            { data: 'tgl_awal', name: 'tgl_awal' },
            { data: 'tgl_akhir', name: 'tgl_akhir' },
            // Kolom-kolom ini dipindahkan ke child row (nested table)
            // { data: 'masa_kontrak', name: 'masa_kontrak', orderable: false, searchable: false },
            // { data: 'status_ttd_label', name: 'status_ttd', orderable: false },
            // { data: 'nama_pihak1', name: 'nama_pihak1' },
            // { data: 'penghuni1_nama', name: 'penghuni1_nama', orderable: false, searchable: true },
            // { data: 'penghuni2_nama', name: 'penghuni2_nama', orderable: false, searchable: true },
            // { data: 'penghuni3_nama', name: 'penghuni3_nama', orderable: false, searchable: true },
            // { data: 'penghuni4_nama', name: 'penghuni4_nama', orderable: false, searchable: true },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ],
        language: {
            "search": "Search by No Kontrak:"
        }
    });
    
    // Menambahkan event listener untuk membuka dan menutup child row
    $('#DTKontrakAktif tbody').on('click', 'td.dt-control', function() {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            // Data 'details' diambil dari respons AJAX server
            row.child(formatDetails(row.data().details)).show();
            tr.addClass('shown');
        }
    });

    /**
     * Format the child row with a nested table for better readability.
     * d is the data object for the row.
     */
    function formatDetails(d) {
        // d is the 'details' object from the server response
        var html = '<table class="table table-bordered table-striped" cellpadding="5" cellspacing="0" border="0">';
        
        // Looping untuk setiap pasangan key-value dalam objek details
        for (const [key, value] of Object.entries(d)) {
            html += '<tr>' +
                '<td class="fw-bold" style="width: 150px;">' + key + ':</td>' +
                '<td>' + value + '</td>' +
                '</tr>';
        }

        html += '</table>';
        return html;
    }


        // --- Kontrak CRUD Functions ---

        function clearKontrakFormFields() {
            $('#kontrakId').val('');
            $('#unit_id').val('').trigger('change');
            $('#no_kontrak').val('');
            $('#harga_sewa').val('');
            $('#harga_air').val('');
            $('#jenis_usaha').val('');
            $('#luas_usaha').val('');
            $('#tipe_kontrak').val('');
            $('#tgl_awal').val('');
            $('#tgl_akhir').val('');
            $('#nama_pihak1').val('');
            $('#status_ttd').val('0'); // Default to Draft
            $('#penghuni_id1').val('');
            $('#penghuni_id2').val('');
            $('#penghuni_id3').val('');
            $('#penghuni_id4').val('');
            $('#kontrakFormMethod').val('POST');
            $('#addKontrakModalLabel').text('Tambah Data Kontrak');
            $('#submitKontrakBtn').text('Simpan Data');

            // Clear selected options for penghuni
            const penghuniSelect = $('#penghuni_ids');
            penghuniSelect.empty().trigger('change');
        }

        // Set up Select2 for unit dropdown
        function setupUnitSelect2() {
            $('#unit_id').select2({
                theme: "bootstrap-5",
                dropdownParent: $('#addKontrakModal')
            });
        }

         // Load Unit options for Kontrak form
        function loadUnitOptions(selectedUnitId = null) {
            $.ajax({
                url: getUnitOptionsUrl,
                method: 'GET',
                success: function(response) {
                    const selectElement = $('#unit_id');
                    selectElement.empty();
                    selectElement.append('<option value="">Pilih Unit</option>');
                    if (response.success && response.data) {
                        response.data.forEach(function(unit) {
                            const option = new Option(
                                `${unit.nomor} (${unit.lantai} - ${unit.tipe_unit}) - ${unit.gedung_nama} (${unit.lokasi_nama})`, 
                                unit.id, 
                                false, 
                                false
                            );
                            selectElement.append(option);
                        });
                        if (selectedUnitId) {
                            selectElement.val(selectedUnitId).trigger('change');
                        }
                    } else {
                        console.error('Failed to load unit options:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error loading unit options:', xhr.responseText);
                }
            });
        }

        // Set up Select2 for penghuni dropdown
        function setupPenghuniSelect2(selector, selectedPenghuniIds = []) {
            $(selector).select2({
                theme: "bootstrap-5",
                dropdownParent: $('#addKontrakModal'),
                ajax: {
                    url: getPenghuniOptionsUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term, // keyword pencarian
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.data.map(item => ({
                                id: item.id,
                                text: item.text
                            }))
                        };
                    },
                    cache: true
                }
            });

            // If initial values are provided, fetch and set them
            if (selectedPenghuniIds.length > 0) {
                $.ajax({
                    url: getPenghuniOptionsUrl,
                    method: 'GET',
                    data: {
                        ids: selectedPenghuniIds // Pass an array of IDs
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            const options = response.data.map(item => new Option(item.text, item.id, true, true));
                            $(selector).append(options).trigger('change');
                        }
                    }
                });
            }
        }

        // Fetch Unit details (tipe_unit, kepala_lokasi) when unit_id changes
        $('#unit_id').on('change', function() {
            const unitId = $(this).val();
            if (unitId) {
                const url = getUnitDetailsUrl.replace(':id', unitId);
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.data) {
                            // Mengambil nilai integer untuk hidden input
                            $('#tipe_kontrak').val(response.data.tipe_kontrak_int);
                            // Mengambil label teks untuk display input
                            $('#tipe_kontrak_display').val(response.data.tipe_kontrak_label);
                            $('#nama_pihak1').val(response.data.kepala_lokasi);
                        } else {
                            console.error('Failed to load unit details:', response.message);
                            $('#tipe_kontrak').val('');
                            $('#tipe_kontrak_display').val('');
                            $('#nama_pihak1').val('');
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX Error fetching unit details:', xhr.responseText);
                        $('#tipe_kontrak').val('');
                        $('#tipe_kontrak_display').val('');
                        $('#nama_pihak1').val('');
                    }
                });
            } else {
                $('#tipe_kontrak').val('');
                $('#tipe_kontrak_display').val('');
                $('#nama_pihak1').val('');
            }
        });


        // Event listener for "Tambah Kontrak Baru" button
        $('#addKontrakBtn').on('click', function() {
            clearKontrakFormFields();
            setupUnitSelect2(); // Inisialisasi Select2 untuk Unit
            loadUnitOptions(); // Load unit options for new contract
            setupPenghuniSelect2('#penghuni_ids'); // Inisialisasi Select2 untuk Penghuni
        });

        // Edit Kontrak
        window.editKontrak = function(id) {
            clearKontrakFormFields();
            $('#addKontrakModalLabel').text('Edit Data Kontrak');
            $('#submitKontrakBtn').text('Update Data');
            $('#kontrakFormMethod').val('PUT');
            
            $.ajax({
                url: editKontrakUrlTemplate.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        // Populate with Select2
                        setupUnitSelect2(); // Inisialisasi Select2 untuk Unit
                        loadUnitOptions(data.unit_id);
                        
                        // Collect all penghuni_id values into an array
                        const penghuniIds = [data.penghuni_id1, data.penghuni_id2, data.penghuni_id3, data.penghuni_id4].filter(Boolean);
                        setupPenghuniSelect2('#penghuni_ids', penghuniIds);
                        
                        // Populate other fields
                        $('#kontrakId').val(data.id);
                        $('#no_kontrak').val(data.no_kontrak);
                        $('#tipe_kontrak').val(data.tipe_kontrak_raw);
                        $('#tgl_awal').val(data.tgl_awal);
                        $('#tgl_akhir').val(data.tgl_akhir);
                        $('#nama_pihak1').val(data.nama_pihak1);
                        $('#status_ttd').val(data.status_ttd).trigger('change');
                        
                        $('#addKontrakModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengambil data kontrak untuk diedit.', 'error');
                    console.error('AJAX Error fetching kontrak data for edit:', xhr.responseText);
                }
            });
        };

        // Hapus Kontrak
        window.hapusKontrak = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data kontrak ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = deleteKontrakUrlTemplate.replace(':id', id);
                    $.ajax({
                        url: deleteUrl,
                        method: 'POST', // Menggunakan POST untuk DELETE method spoofing
                        data: {
                            _token: $('meta[name="_token"]').attr('content'),
                            _method: 'DELETE' // Method spoofing
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Dihapus!', response.message, 'success');
                                $('#DTKontrakAktif').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data kontrak.', 'error');
                            console.error('AJAX Error deleting kontrak data:', xhr.responseText);
                        }
                    });
                }
            });
        };


        // Fungsi baru untuk membuka modal putus kontrak
        window.putusKontrak = function(id) {
            $('#putusKontrakId').val(id);
            // Anda bisa melakukan AJAX call di sini untuk mendapatkan tanggal awal kontrak
            // Misalnya:
            // $.ajax({
            //     url: editKontrakUrlTemplate.replace(':id', id),
            //     method: 'GET',
            //     success: function(response) {
            //         const tglAwal = response.data.tgl_awal;
            //         // Simpan tglAwal di suatu tempat atau langsung hitung di sini
            //     }
            // });

            $('#putusKontrakModal').modal('show');
        };

        // Hitung masa kontrak berjalan saat tanggal keluar diubah
        $('#tgl_keluar').on('change', function() {
            const id = $('#putusKontrakId').val();
            const tglKeluar = $(this).val();

            if (id && tglKeluar) {
                $.ajax({
                    url: editKontrakUrlTemplate.replace(':id', id),
                    method: 'GET',
                    success: function(response) {
                        const tglAwal = new Date(response.data.tgl_awal);
                        const tglKeluarDate = new Date(tglKeluar);
                        
                        // Hitung selisih hari
                        const diffTime = Math.abs(tglKeluarDate - tglAwal);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                        
                        // Hitung tahun, bulan, hari (sederhana)
                        const years = Math.floor(diffDays / 365);
                        const remainingDaysAfterYears = diffDays % 365;
                        const months = Math.floor(remainingDaysAfterYears / 30); // Estimasi
                        const days = remainingDaysAfterYears % 30;
                        
                        $('#masa_kontrak_putus').val(`${years} tahun, ${months} bulan, ${days} hari`);
                    }
                });
            }
        });

        // Submit Putus Kontrak Form
        $('#putusKontrakForm').on('submit', function(event) {
            event.preventDefault();
            const kontrakId = $('#putusKontrakId').val();
            const tglKeluar = $('#tgl_keluar').val();
            const submitButton = $('#submitPutusKontrakBtn');
            const originalButtonText = submitButton.text();

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
            
            // Buat route baru untuk update status dan tgl_keluar
            // Asumsi route '/kontrak/putus/{id}' sudah dibuat

            const putusKontrakUrl = putusKontrakUrlTemplate.replace(':id', kontrakId);

            $.ajax({
                url: putusKontrakUrl,
                method: 'POST',
                data: {
                    _token: $('meta[name="_token"]').attr('content'),
                    tgl_keluar: tglKeluar,
                    status_kontrak: 0 // Mengubah status menjadi tidak aktif
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#putusKontrakModal').modal('hide');
                        $('#DTKontrakAktif').DataTable().ajax.reload(null, false);
                    } else {
                        Swal.fire('Gagal!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat memutuskan kontrak.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal!', errorMessage, 'error');
                    console.error('AJAX Error putting kontrak:', xhr.responseText);
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
        });

        // Submit Kontrak Form (Add/Update)
        $('#addKontrakForm').on('submit', function(event) {
            event.preventDefault();
            const submitButton = $('#submitKontrakBtn');
            const originalButtonText = submitButton.text();
            const formMethod = $('#kontrakFormMethod').val();
            const kontrakId = $('#kontrakId').val();

            let targetUrl = storeKontrakUrl;
            if (formMethod === 'PUT') {
                targetUrl = updateKontrakUrlTemplate.replace(':id', kontrakId);
            }

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
            
            // Get selected penghuni IDs from the multi-select dropdown
            const selectedPenghuniIds = $('#penghuni_ids').val();

            // Clear hidden inputs first
            $('#penghuni_id1').val('');
            $('#penghuni_id2').val('');
            $('#penghuni_id3').val('');
            $('#penghuni_id4').val('');

            // Assign selected IDs to the hidden inputs
            if (selectedPenghuniIds && selectedPenghuniIds.length > 0) {
                $('#penghuni_id1').val(selectedPenghuniIds[0]);
                if (selectedPenghuniIds.length > 1) {
                    $('#penghuni_id2').val(selectedPenghuniIds[1]);
                }
                if (selectedPenghuniIds.length > 2) {
                    $('#penghuni_id3').val(selectedPenghuniIds[2]);
                }
                if (selectedPenghuniIds.length > 3) {
                    $('#penghuni_id4').val(selectedPenghuniIds[3]);
                }
            }

            // Serialize the form data
            $('#penghuni_ids').prop('disabled', true); // Disable to avoid sending array

            const formData = $(this).serialize();
            $('#penghuni_ids').prop('disabled', false);
            $.ajax({
                url: targetUrl,
                method: 'POST', // Always POST for method spoofing
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#addKontrakModal').modal('hide');
                        clearKontrakFormFields();
                        $('#DTKontrakAktif').DataTable().ajax.reload(null, false);
                    } else {
                        let errorMessage = response.message || 'Gagal menyimpan data.';
                        if (response.errors) {
                            errorMessage += '<br>' + Object.values(response.errors).map(e => `- ${e[0]}`).join('<br>');
                        }
                        Swal.fire('Gagal!', errorMessage, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage += '<br>' + Object.values(xhr.responseJSON.errors).map(e => `- ${e[0]}`).join('<br>');
                    }
                    Swal.fire('Gagal!', errorMessage, 'error');
                    console.error('AJAX Error submitting kontrak form:', xhr.responseText);
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
        });
    });
  </script>
@endpush
