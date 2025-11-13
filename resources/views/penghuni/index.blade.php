@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
  <!-- DataTables Responsive CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Penghuni</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Data Penghuni</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPenghuniModal" id="addPenghuniBtn">
          Tambah Penghuni Baru
        </button>
        <div class="table-responsive">
          <table id="DTPenghuni" class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Tgl Lahir</th>
                <th>Tempat Lahir</th> <!-- Kolom Baru -->
                <th>No Telp</th>
                <th>Jenis Kelamin</th>
                <th>Status Kawin</th>
                <th>Agama</th>
                <th>Pekerjaan</th>
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
</div>

<!-- Modal Tambah/Edit Penghuni -->
<div class="modal fade" id="addPenghuniModal" tabindex="-1" aria-labelledby="addPenghuniModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addPenghuniModalLabel">Tambah Data Penghuni</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addPenghuniForm">
          @csrf <!-- CSRF token for Laravel security -->
          <input type="hidden" name="_method" value="POST" id="formMethod">
          <input type="hidden" name="id" id="penghuniId">

          <div class="mb-3">
            <label for="nik" class="form-label">NIK</label>
            <div class="input-group">
                  <input type="text" class="form-control" id="nik" name="nik" required minlength="16" maxlength="16">
                  <button class="btn btn-outline-secondary" type="button" id="fetchNikDataBtn">
                    <span id="nikSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    Cari
                </button>
            </div>
            <div id="nikErrorMessage" class="text-danger mt-1"></div>
          </div>
          <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
          </div>
          <div class="mb-3">
            <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
          </div>
          <div class="mb-3">
            <label for="no_tlp" class="form-label">No. Telepon</label>
            <input type="text" class="form-control" id="no_tlp" name="no_tlp" required>
          </div>
          <div class="mb-3">
            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
              <option value="">Pilih Jenis Kelamin</option>
              <option value="1">Laki-laki</option>
              <option value="2">Perempuan</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="status_kawin" class="form-label">Status Kawin</label>
            <select class="form-select" id="status_kawin" name="status_kawin" required>
              <option value="">Pilih Status Kawin</option>
              <option value="1">Belum Kawin</option>
              <option value="2">Kawin/Nikah</option>
              <option value="3">Cerai Hidup</m>
              <option value="4">Cerai Mati</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="agama" class="form-label">Agama</label>
            <select class="form-select" id="agama" name="agama" required>
              <option value="">Pilih Agama</option>
              <option value="1">Islam</option>
              <option value="2">Kristen</option>
              <option value="3">Katolik</option>
              <option value="4">Hindu</option>
              <option value="5">Buddha</option>
              <option value="6">Konghucu</option>
              <option value="7">Penghayat Kepercayaan</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="pekerjaan" class="form-label">Pekerjaan</label>
            <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" required>
          </div>
          <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" id="submitPenghuniBtn">Simpan Data</button>
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
  <script src="{{ asset('assets/plugins/flatpickr/flatpickr.min.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/data-table.js') }}"></script>

  <script>
    const ajaxUrl = @json(route('penghuni.ajax.DTPenghuni'));
    const storeUrl = @json(route('penghuni.store')); 
    const getDataIndividuUrl = @json(route('penghuni.getDataIndividuFromAPI'));
    const editUrlTemplate = @json(route('penghuni.edit', ['id' => ':id'])); 
    const updateUrlTemplate = @json(route('penghuni.update', ['id' => ':id'])); 
    const deleteUrlTemplate = @json(route('penghuni.destroy', ['id' => ':id'])); 

    let tglLahirFlatpickrInstance;

    $(function() {
        'use strict';

        $('#DTPenghuni').DataTable({
            "ajax": {
                url: ajaxUrl,
                type: 'GET',
            },
            "ordering": true,
            "paging": true,
            "processing": true,
            "serverSide": true,
            "autoWidth": false,
            "responsive": true,
            "columns": [
                {data: 'id', name: 'id'},
                {data: 'nik', name: 'nik'},
                {data: 'nama', name: 'nama'},
                {data: 'email', name: 'email'},
                {data: 'tgl_lahir', name: 'tgl_lahir'},
                {data: 'tempat_lahir', name: 'tempat_lahir'},
                {data: 'no_tlp', name: 'no_tlp'},
                {data: 'jenis_kelamin', name: 'jenis_kelamin'},
                {data: 'status_kawin', name: 'status_kawin'},
                {data: 'agama', name: 'agama'},
                {data: 'pekerjaan', name: 'pekerjaan'},
                {data: 'alamat', name: 'alamat'},
                {data: 'aksi', name: 'aksi', orderable: false, searchable: false}
            ]
        });

        tglLahirFlatpickrInstance = flatpickr("#tgl_lahir", {
            dateFormat: "Y-m-d",
        });
    });

    // Membersihkan field form yang diisi oleh API
    function clearAPIFields() {
        $('#nama').val('');
        if (tglLahirFlatpickrInstance) {
            tglLahirFlatpickrInstance.clear();
        } else {
            $('#tgl_lahir').val('');
        }
        $('#tempat_lahir').val('');
        $('#no_tlp').val('');
        $('#jenis_kelamin').val('');
        $('#status_kawin').val('');
        $('#agama').val('');
        $('#email').val('');
        $('#pekerjaan').val('');
        $('#alamat').val('');
    }

    // Mengosongkan seluruh field form
    function clearFormFields() {
        $('#penghuniId').val(''); 
        $('#nik').val('');
        clearAPIFields();
        $('#nikErrorMessage').text(''); 
        $('#formMethod').val('POST'); 
        $('#addPenghuniModalLabel').text('Tambah Data Penghuni'); 
        $('#submitPenghuniBtn').text('Simpan Data'); 
        $('#nik').prop('disabled', false); 
        $('#fetchNikDataBtn').prop('disabled', false); 
    }

    // Mengisi form dengan data dari API atau database
    function populateForm(data) {
        $('#nama').val(data.nama);
        if (tglLahirFlatpickrInstance) {
            tglLahirFlatpickrInstance.setDate(data.tgl_lahir, true);
        } else {
            $('#tgl_lahir').val(data.tgl_lahir);
        }
        $('#tempat_lahir').val(data.tempat_lahir || '');
        $('#no_tlp').val(data.no_tlp);
        $('#jenis_kelamin').val(data.jenis_kelamin);
        $('#status_kawin').val(data.status_kawin);
        $('#agama').val(data.agama);
        $('#email').val(data.email || ''); 
        $('#pekerjaan').val(data.pekerjaan || ''); 
        $('#alamat').val(data.alamat || '');     
    }

    // Event listener untuk tombol "Tambah Penghuni Baru"
    $('#addPenghuniBtn').on('click', function() {
        clearFormFields(); 
    });

    // Fungsi untuk mengambil data dari API
    function fetchPenghuniData() {
        const nik = $('#nik').val();
        const nikErrorMessage = $('#nikErrorMessage');
        const nikSpinner = $('#nikSpinner');
        const fetchNikDataBtn = $('#fetchNikDataBtn');

        if (nik.length !== 16) {
            nikErrorMessage.text('NIK harus 16 digit.');
            clearAPIFields();
            return;
        }

        nikErrorMessage.text('');
        nikSpinner.removeClass('d-none');
        fetchNikDataBtn.prop('disabled', true);

        $.ajax({
            url: getDataIndividuUrl,
            method: 'POST',
            data: { 
                nik: nik, 
                _token: $('meta[name="_token"]').attr('content') 
            }, 
            success: function(response) {
                if (response.success && response.data) {
                    populateForm(response.data);
                    nikErrorMessage.text('Data NIK ditemukan, silakan lengkapi data lainnya.').removeClass('text-danger').addClass('text-success');
                    $('#nik').prop('disabled', false); 
                    fetchNikDataBtn.prop('disabled', false).text('Data Ditemukan'); 
                } else {
                    // Data tidak ditemukan, tampilkan pesan di bawah input NIK tanpa pop-up
                    nikErrorMessage.text(response.message || 'Data NIK tidak ditemukan. Silakan isi data secara manual.').removeClass('text-success').addClass('text-danger');
                    clearAPIFields();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat mencari data NIK. Silakan isi data secara manual.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                // Tampilkan pesan error di bawah input NIK tanpa pop-up
                nikErrorMessage.text(errorMessage).removeClass('text-success').addClass('text-danger');
                clearAPIFields();
            },
            complete: function() {
                nikSpinner.addClass('d-none');
                if (!($('#nik').prop('disabled'))) {
                    fetchNikDataBtn.prop('disabled', false).text('Cari');
                }
            }
        });
    }

    // Tangani klik tombol "Cari" di samping input NIK
    $('#fetchNikDataBtn').on('click', fetchPenghuniData);

    // Tangani event ketika input NIK berubah atau selesai diisi (blur)
    $('#nik').on('change', function() {
        fetchPenghuniData();
    });

    // Fungsi untuk mode edit
    window.editPenghuni = function(id) {
        clearFormFields(); 
        $('#addPenghuniModalLabel').text('Edit Data Penghuni'); 
        $('#submitPenghuniBtn').text('Update Data'); 
        $('#formMethod').val('PUT'); 
        $('#penghuniId').val(id); 
        $('#nik').prop('disabled', true); 
        $('#fetchNikDataBtn').prop('disabled', true); 
        
        $.ajax({
            url: editUrlTemplate.replace(':id', id),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    populateForm(response.data);
                    $('#addPenghuniModal').modal('show'); 
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal mengambil data penghuni untuk diedit.', 'error');
                console.error('AJAX Error fetching penghuni data for edit:', xhr.responseText);
            }
        });
    };

    // Fungsi untuk mode hapus
    window.hapusPenghuni = function(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data penghuni ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteUrl = deleteUrlTemplate.replace(':id', id);
                $.ajax({
                    url: deleteUrl,
                    method: 'POST', 
                    data: {
                        _token: $('meta[name="_token"]').attr('content'),
                        _method: 'DELETE' 
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Dihapus!', response.message, 'success');
                            $('#DTPenghuni').DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal!', response.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                        console.error('AJAX Error deleting penghuni data:', xhr.responseText);
                    }
                });
            }
        });
    };

    // Tangani submit form untuk menambah/mengedit penghuni
    $('#addPenghuniForm').on('submit', function(event) {
        event.preventDefault(); 
        const submitButton = $('#submitPenghuniBtn');
        const originalButtonText = submitButton.text();
        const formMethod = $('#formMethod').val(); 
        const penghuniId = $('#penghuniId').val(); 
        let targetUrl = storeUrl;
        if (formMethod === 'PUT') {
            targetUrl = updateUrlTemplate.replace(':id', penghuniId);
        }
        submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');

        const formData = $(this).serialize(); 
        $.ajax({
            url: targetUrl,
            method: 'POST', 
            data: formData, 
            success: function(response) {
                if (response.success) {
                    Swal.fire('Berhasil!', response.message, 'success');
                    $('#addPenghuniModal').modal('hide'); 
                    $('#addPenghuniForm')[0].reset(); 
                    clearFormFields(); 
                    $('#DTPenghuni').DataTable().ajax.reload(null, false); 
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'Terjadi kesalahan validasi:<br>';
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            errorMessage += `- ${errors[key][0]}<br>`;
                        }
                    }
                    Swal.fire('Gagal!', errorMessage, 'error');
                } else if (xhr.status === 409) { 
                    Swal.fire('Gagal!', xhr.responseJSON.message, 'error');
                } else {
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data. Silakan cek konsol untuk detail.', 'error');
                    console.error('AJAX Error:', xhr.responseText);
                }
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });
  </script>
@endpush
