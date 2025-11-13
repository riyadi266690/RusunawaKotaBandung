@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <!-- DataTables Responsive CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
  </ol>
</nav>

<div class="row">
  <div class="col-lg-6 col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">Data Lokasi</h6>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLokasiModal" id="addLokasiBtn">
            Tambah Lokasi Baru
          </button>
        </div>
        <p class="text-muted">Harap periksa data anda sebelum disimpan</p>
        <div class="table-responsive">
          <table id="DTLokasi" class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Lokasi</th>
                <th>Penanggung Jawab</th>
                <th>Alamat</th>
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
  <div class="col-lg-6 col-xl-6 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">Data Gedung</h6>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addGedungModal" id="addGedungBtn">
            Tambah Gedung Baru
          </button>
        </div>
        <div class="table-responsive">
          <table id="DTGedung" class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nama Gedung</th>
                <th>Tipe Gedung</th>
                <th>Lokasi</th>
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

<div class="row">
  <div class="col-12 col-xl-12 grid-margin stretch-card">
    <div class="card overflow-hidden">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-4 mb-md-3">
          <h6 class="card-title mb-0">Data Unit</h6>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnitModal" id="addUnitBtn">
            Tambah Unit Baru
          </button>
        </div>
        <div class="table-responsive">
          <table id="DTUnit" class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Gedung</th>
                <th>Nomor Unit</th>
                <th>Lantai</th>
                <th>Tipe Unit</th>
                <th>Status Jual</th>
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






<!-- Modal Tambah/Edit Lokasi -->
<div class="modal fade" id="addLokasiModal" tabindex="-1" aria-labelledby="addLokasiModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addLokasiModalLabel">Tambah Data Lokasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addLokasiForm">
          @csrf
          <input type="hidden" name="_method" value="POST" id="lokasiFormMethod">
          <input type="hidden" name="id" id="lokasiId">
          <div class="mb-3">
            <label for="nama_lokasi" class="form-label">Nama Lokasi</label>
            <input type="text" class="form-control" id="nama_lokasi" name="nama_lokasi" required>
          </div>
          <div class="mb-3">
            <label for="kepala_lokasi" class="form-label">Penanggung Jawab</label>
            <input type="text" class="form-control" id="kepala_lokasi" name="kepala_lokasi" required>
          </div>
          <div class="mb-3">
            <label for="alamat_lokasi" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat_lokasi" name="alamat_lokasi" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="submitLokasiBtn">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Gedung -->
<div class="modal fade" id="addGedungModal" tabindex="-1" aria-labelledby="addGedungModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addGedungModalLabel">Tambah Data Gedung</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addGedungForm">
          @csrf
          <input type="hidden" name="_method" value="POST" id="gedungFormMethod">
          <input type="hidden" name="id" id="gedungId">
          <div class="mb-3">
            <label for="nama_gedung" class="form-label">Nama Gedung</label>
            <input type="text" class="form-control" id="nama_gedung" name="nama_gedung" required>
          </div>
          <div class="mb-3">
            <label for="tipe_gedung" class="form-label">Tipe Gedung</label>
            <input type="text" class="form-control" id="tipe_gedung" name="tipe_gedung" required>
          </div>
          <div class="mb-3">
            <label for="lokasi_id" class="form-label">Lokasi</label>
            <select class="form-select" id="lokasi_id" name="lokasi_id" required>
              <option value="">Pilih Lokasi</option>
              <!-- Options will be loaded via AJAX -->
            </select>
          </div>
          <button type="submit" class="btn btn-primary" id="submitGedungBtn">Simpan Data</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal Tambah/Edit Unit -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addUnitModalLabel">Tambah Data Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addUnitForm">
          @csrf
          <input type="hidden" name="_method" value="POST" id="unitFormMethod">
          <input type="hidden" name="id" id="unitId">
          <div class="mb-3">
            <label for="gedung_id" class="form-label">Gedung</label>
            <select class="form-select" id="gedung_id" name="gedung_id" required>
              <option value="">Pilih Gedung</option>
              <!-- Options will be loaded via AJAX -->
            </select>
          </div>
          <div class="mb-3">
            <label for="nomor" class="form-label">Nomor Unit</label>
            <input type="text" class="form-control" id="nomor" name="nomor" required>
          </div>
          <div class="mb-3">
            <label for="lantai" class="form-label">Lantai</label>
            <select class="form-select" id="lantai" name="lantai" required>
              <option value="">Pilih Lantai</option>
              @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
              @endfor
            </select>
          </div>
          <div class="mb-3">
            <label for="tipe_unit" class="form-label">Tipe Unit</label>
            <select class="form-select" id="tipe_unit" name="tipe_unit" required>
              <option value="">Pilih Tipe Unit</option>
              <option value="Hunian">Hunian</option>
              <option value="RBH">RBH</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="status_jual" class="form-label">Status Jual</label>
            <select class="form-select" id="status_jual" name="status_jual" required>
              <option value="1">Tersedia</option>
              <option value="0">Dalam Perbaikan</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" id="submitUnitBtn">Simpan Data</button>
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
  <script src="{{ asset('assets/plugins/datatables-net/jquery.dataTables.js') }}"></script>
  <script src="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.js') }}"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
  <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/data-table.js') }}"></script>

  <script>
    // URL Constants
    const ajaxUrlLokasi = @json(route('pengaturan.ajax.DTLokasi'));
    const storeLokasiUrl = @json(route('pengaturan.lokasi.store'));
    const editLokasiUrlTemplate = @json(route('pengaturan.lokasi.edit', ['lokasi' => ':id']));
    const updateLokasiUrlTemplate = @json(route('pengaturan.lokasi.update', ['lokasi' => ':id']));
    const deleteLokasiUrlTemplate = @json(route('pengaturan.lokasi.destroy', ['lokasi' => ':id']));
    const ajaxUrlGedung = @json(route('pengaturan.ajax.DTGedung'));
    const storeGedungUrl = @json(route('pengaturan.gedung.store'));
    const editGedungUrlTemplate = @json(route('pengaturan.gedung.edit', ['gedung' => ':id']));
    const updateGedungUrlTemplate = @json(route('pengaturan.gedung.update', ['gedung' => ':id']));
    const deleteGedungUrlTemplate = @json(route('pengaturan.gedung.destroy', ['gedung' => ':id']));
    const getLokasiOptionsUrl = @json(route('pengaturan.lokasi.options')); // New route for dropdown
    const ajaxUrlUnit = @json(route('pengaturan.ajax.DTUnit')); // New
    const storeUnitUrl = @json(route('pengaturan.unit.store')); // New
    const editUnitUrlTemplate = @json(route('pengaturan.unit.edit', ['unit' => ':id'])); // New
    const updateUnitUrlTemplate = @json(route('pengaturan.unit.update', ['unit' => ':id'])); // New
    const deleteUnitUrlTemplate = @json(route('pengaturan.unit.destroy', ['unit' => ':id'])); // New
    const getGedungOptionsUrl = @json(route('pengaturan.gedung.options')); // New route for dropdown

    $(document).ready(function() {
        'use strict';

        // Initialize DTLokasi DataTable
        $('#DTLokasi').DataTable({
            processing: true,
            serverSide: true,
            ajax: ajaxUrlLokasi,
            responsive: true,
            columns: [
                { data: 'id', name: 'id' },
                { data: 'nama_lokasi', name: 'nama_lokasi' },
                { data: 'kepala_lokasi', name: 'kepala_lokasi' },
                { data: 'alamat_lokasi', name: 'alamat_lokasi' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });
        
        // Initialize DTGedung DataTable
        $('#DTGedung').DataTable({
            processing: true,
            serverSide: true,
            ajax: ajaxUrlGedung,
            responsive: true,
            columns: [
                { data: 'id', name: 'id' },
                { data: 'nama_gedung', name: 'nama_gedung' },
                { data: 'tipe_gedung', name: 'tipe_gedung' },
                { data: 'lokasi', name: 'lokasi' }, // Ini akan menampilkan nama lokasi
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });

        // Initialize DTUnit DataTable
        $('#DTUnit').DataTable({
            processing: true,
            serverSide: true,
            ajax: ajaxUrlUnit,
            responsive: true,
            columns: [
                { data: 'id', name: 'id' },
                { data: 'gedung', name: 'gedung' }, // Ini akan menampilkan nama gedung
                { data: 'nomor', name: 'nomor' },
                { data: 'lantai', name: 'lantai' },
                { data: 'tipe_unit', name: 'tipe_unit' },
                { data: 'status_jual', name: 'status_jual' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
            ]
        });

        // --- Lokasi CRUD Functions ---

        function clearLokasiFormFields() {
            $('#lokasiId').val('');
            $('#nama_lokasi').val('');
            $('#kepala_lokasi').val('');
            $('#alamat_lokasi').val('');
            $('#lokasiFormMethod').val('POST');
            $('#addLokasiModalLabel').text('Tambah Data Lokasi');
            $('#submitLokasiBtn').text('Simpan Data');
        }

        function populateLokasiForm(data) {
            $('#lokasiId').val(data.id);
            $('#nama_lokasi').val(data.nama_lokasi);
            $('#kepala_lokasi').val(data.kepala_lokasi);
            $('#alamat_lokasi').val(data.alamat_lokasi);
        }

        // Event listener for "Tambah Lokasi Baru" button
        $('#addLokasiBtn').on('click', function() {
            clearLokasiFormFields();
        });

        // Edit Lokasi
        window.editLokasi = function(id) {
            clearLokasiFormFields();
            $('#addLokasiModalLabel').text('Edit Data Lokasi');
            $('#submitLokasiBtn').text('Update Data');
            $('#lokasiFormMethod').val('PUT');
            
            $.ajax({
                url: editLokasiUrlTemplate.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateLokasiForm(response.data);
                        $('#addLokasiModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengambil data lokasi untuk diedit.', 'error');
                    console.error('AJAX Error fetching lokasi data for edit:', xhr.responseText);
                }
            });
        };

        // Hapus Lokasi
        window.hapusLokasi = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data lokasi ini akan dihapus secara permanen! Ini juga akan menghapus gedung yang terkait.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = deleteLokasiUrlTemplate.replace(':id', id);
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
                                $('#DTLokasi').DataTable().ajax.reload(null, false);
                                $('#DTGedung').DataTable().ajax.reload(null, false); // Reload Gedung juga
                                $('#DTUnit').DataTable().ajax.reload(null, false); // Reload Unit juga
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data lokasi.', 'error');
                            console.error('AJAX Error deleting lokasi data:', xhr.responseText);
                        }
                    });
                }
            });
        };

        // Submit Lokasi Form (Add/Update)
        $('#addLokasiForm').on('submit', function(event) {
            event.preventDefault();
            const submitButton = $('#submitLokasiBtn');
            const originalButtonText = submitButton.text();
            const formMethod = $('#lokasiFormMethod').val();
            const lokasiId = $('#lokasiId').val();

            let targetUrl = storeLokasiUrl;
            if (formMethod === 'PUT') {
                targetUrl = updateLokasiUrlTemplate.replace(':id', lokasiId);
            }

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            const formData = $(this).serialize();

            $.ajax({
                url: targetUrl,
                method: 'POST', // Always POST for method spoofing
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#addLokasiModal').modal('hide');
                        clearLokasiFormFields();
                        $('#DTLokasi').DataTable().ajax.reload(null, false);
                        // Refresh dropdown lokasi di form gedung jika modal gedung terbuka atau akan dibuka
                        loadLokasiOptions(); 
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
                    console.error('AJAX Error submitting lokasi form:', xhr.responseText);
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
        });

        // --- Gedung CRUD Functions ---

        function clearGedungFormFields() {
            $('#gedungId').val('');
            $('#nama_gedung').val('');
            $('#tipe_gedung').val('');
            $('#lokasi_id').val(''); // Reset dropdown
            $('#gedungFormMethod').val('POST');
            $('#addGedungModalLabel').text('Tambah Data Gedung');
            $('#submitGedungBtn').text('Simpan Data');
        }

        function populateGedungForm(data) {
            $('#gedungId').val(data.id);
            $('#nama_gedung').val(data.nama_gedung);
            $('#tipe_gedung').val(data.tipe_gedung);
            $('#lokasi_id').val(data.lokasi_id); // Set dropdown value
        }

        // Load Lokasi options for Gedung form
        function loadLokasiOptions(selectedLokasiId = null) {
            $.ajax({
                url: getLokasiOptionsUrl,
                method: 'GET',
                success: function(response) {
                    const selectElement = $('#lokasi_id');
                    selectElement.empty();
                    selectElement.append('<option value="">Pilih Lokasi</option>');
                    if (response.success && response.data) {
                        response.data.forEach(function(lokasi) {
                            selectElement.append(
                                `<option value="${lokasi.id}">${lokasi.nama_lokasi}</option>`
                            );
                        });
                        if (selectedLokasiId) {
                            selectElement.val(selectedLokasiId);
                        }
                    } else {
                        console.error('Failed to load lokasi options:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error loading lokasi options:', xhr.responseText);
                }
            });
        }

        // Event listener for "Tambah Gedung Baru" button
        $('#addGedungBtn').on('click', function() {
            clearGedungFormFields();
            loadLokasiOptions(); // Load options every time modal is opened for add
        });

        // Edit Gedung
        window.editGedung = function(id) {
            clearGedungFormFields();
            $('#addGedungModalLabel').text('Edit Data Gedung');
            $('#submitGedungBtn').text('Update Data');
            $('#gedungFormMethod').val('PUT');
            
            $.ajax({
                url: editGedungUrlTemplate.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Load options first, then populate form with selected ID
                        loadLokasiOptions(response.data.lokasi_id); 
                        populateGedungForm(response.data);
                        $('#addGedungModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengambil data gedung untuk diedit.', 'error');
                    console.error('AJAX Error fetching gedung data for edit:', xhr.responseText);
                }
            });
        };

        // Hapus Gedung
        window.hapusGedung = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data gedung ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = deleteGedungUrlTemplate.replace(':id', id);
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
                                $('#DTGedung').DataTable().ajax.reload(null, false);
                                $('#DTUnit').DataTable().ajax.reload(null, false); // Reload Unit juga
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data gedung.', 'error');
                            console.error('AJAX Error deleting gedung data:', xhr.responseText);
                        }
                    });
                }
            });
        };

        // Submit Gedung Form (Add/Update)
        $('#addGedungForm').on('submit', function(event) {
            event.preventDefault();
            const submitButton = $('#submitGedungBtn');
            const originalButtonText = submitButton.text();
            const formMethod = $('#gedungFormMethod').val();
            const gedungId = $('#gedungId').val();

            let targetUrl = storeGedungUrl;
            if (formMethod === 'PUT') {
                targetUrl = updateGedungUrlTemplate.replace(':id', gedungId);
            }

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            const formData = $(this).serialize();

            $.ajax({
                url: targetUrl,
                method: 'POST', // Always POST for method spoofing
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#addGedungModal').modal('hide');
                        clearGedungFormFields();
                        $('#DTGedung').DataTable().ajax.reload(null, false);
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
                    console.error('AJAX Error submitting gedung form:', xhr.responseText);
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
        });
    });


    // --- Unit CRUD Functions ---

        function clearUnitFormFields() {
            $('#unitId').val('');
            $('#gedung_id').val(''); 
            $('#nomor').val('');
            $('#lantai').val('');
            $('#tipe_unit').val('');
            $('#status_jual').val('1'); // Default ke Tersedia
            $('#unitFormMethod').val('POST');
            $('#addUnitModalLabel').text('Tambah Data Unit');
            $('#submitUnitBtn').text('Simpan Data');
        }

        function populateUnitForm(data) {
            $('#unitId').val(data.id);
            $('#nomor').val(data.nomor);
            $('#lantai').val(data.lantai);
            $('#tipe_unit').val(data.tipe_unit);
            $('#status_jual').val(data.status_jual);
            $('#gedung_id').val(data.gedung_id); // Set dropdown value
        }

        // Load Gedung options for Unit form
        function loadGedungOptions(selectedGedungId = null) {
            $.ajax({
                url: getGedungOptionsUrl,
                method: 'GET',
                success: function(response) {
                    const selectElement = $('#gedung_id');
                    selectElement.empty();
                    selectElement.append('<option value="">Pilih Gedung</option>');
                    if (response.success && response.data) {
                        response.data.forEach(function(gedung) {
                            selectElement.append(
                                `<option value="${gedung.id}">${gedung.nama_gedung} (${gedung.lokasi})</option>`
                            );
                        });
                        if (selectedGedungId) {
                            selectElement.val(selectedGedungId);
                        }
                    } else {
                        console.error('Failed to load gedung options:', response.message);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error loading gedung options:', xhr.responseText);
                }
            });
        }

        // Event listener for "Tambah Unit Baru" button
        $('#addUnitBtn').on('click', function() {
            clearUnitFormFields();
            loadGedungOptions(); // Load options every time modal is opened for add
        });

        // Edit Unit
        window.editUnit = function(id) {
            clearUnitFormFields();
            $('#addUnitModalLabel').text('Edit Data Unit');
            $('#submitUnitBtn').text('Update Data');
            $('#unitFormMethod').val('PUT');
            
            $.ajax({
                url: editUnitUrlTemplate.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        // Load options first, then populate form with selected ID
                        loadGedungOptions(response.data.gedung_id); 
                        populateUnitForm(response.data);
                        $('#addUnitModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Gagal mengambil data unit untuk diedit.', 'error');
                    console.error('AJAX Error fetching unit data for edit:', xhr.responseText);
                }
            });
        };

        // Hapus Unit
        window.hapusUnit = function(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data unit ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const deleteUrl = deleteUnitUrlTemplate.replace(':id', id);
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
                                $('#DTUnit').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data unit.', 'error');
                            console.error('AJAX Error deleting unit data:', xhr.responseText);
                        }
                    });
                }
            });
        };

        // Submit Unit Form (Add/Update)
        $('#addUnitForm').on('submit', function(event) {
            event.preventDefault();
            const submitButton = $('#submitUnitBtn');
            const originalButtonText = submitButton.text();
            const formMethod = $('#unitFormMethod').val();
            const unitId = $('#unitId').val();

            let targetUrl = storeUnitUrl;
            if (formMethod === 'PUT') {
                targetUrl = updateUnitUrlTemplate.replace(':id', unitId);
            }

            submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

            const formData = $(this).serialize();

            $.ajax({
                url: targetUrl,
                method: 'POST', // Always POST for method spoofing
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#addUnitModal').modal('hide');
                        clearUnitFormFields();
                        $('#DTUnit').DataTable().ajax.reload(null, false);
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
                    console.error('AJAX Error submitting unit form:', xhr.responseText);
                },
                complete: function() {
                    submitButton.prop('disabled', false).text(originalButtonText);
                }
            });
        });
    
  </script>
@endpush
