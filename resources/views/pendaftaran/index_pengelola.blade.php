@extends('layout.master')

@push('plugin-styles')
  <link href="{{ asset('assets/plugins/datatables-net-bs5/dataTables.bootstrap5.css') }}" rel="stylesheet" />
  <link href="{{ asset('assets/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="#">Pendaftar</a></li>
    <li class="breadcrumb-item active" aria-current="page">Index</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Data Pendaftar</h6>
        <!--p class="text-muted mb-3">Read the <a href="https://datatables.net/" target="_blank"> Official DataTables Documentation </a>for a full list of instructions and other options.</!--p-->
        <div class="table-responsive">
          <table id="DTPendaftar" class="table">
            <thead>
              <tr>
                <th>Status</th>
                <th>Nama</th>
                <th>No Telp</th>
                <th>Tanggal Daftar</th>
                <th>Tanggal Wawancara</th>
                <th>Tanggal Selesai</th>
                <th>Catatan</th>
              </tr>
            </thead>
            
          </table>
        </div>
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
@endpush

@push('custom-scripts')
  <script src="{{ asset('assets/js/data-table.js') }}"></script>

  <script>

    // Setel token CSRF untuk semua permintaan AJAX
    // (Bisa dihapus jika Anda lebih suka menaruh token di setiap request)
    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // });

    const ajaxUrl = @json(route('pendaftar.ajax.DTPendaftar'));
    const updateUrlTemplate = "{{ route('pendaftar.updateWawancara', ['id' => ':id']) }}";

    $(function() {
        'use strict';

        $('#DTPendaftar').DataTable({
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
                {data: 'status', name: 'status'},
                {data: 'nama', name: 'nama'},
                {data: 'telp_pendaftar', name: 'telp_pendaftar'},
                {data: 'daftar', name: 'daftar'},
                {data: 'wawancara', name: 'wawancara'},
                {data: 'selesai', name: 'selesai'},
                {data: 'ket_wawancara', name: 'ket_wawancara'}
            ]
        });
    });

    $('#DTPendaftar').on('click', '.edit-wawancara-btn', function() {
       event.preventDefault(); // Mencegah navigasi default tautan

        const id = $(this).data('id');
        const existingCatatan = $(this).data('catatan') || '';

        Swal.fire({
            title: 'Pilih Tanggal Wawancara dan Catatan',
            html: 
                '<input type="date" id="swal-input-date" class="form-control mb-3">' +
                '<textarea id="swal-input-catatan" class="form-control" placeholder="Masukkan catatan wawancara...">' + existingCatatan + '</textarea>',
            focusConfirm: false,
            preConfirm: () => {
                const newDate = $('#swal-input-date').val();
                const newCatatan = $('#swal-input-catatan').val();
                
                if (!newDate) {
                    Swal.showValidationMessage('Mohon pilih tanggal');
                    return false;
                }
                return { tgl_wawancara: newDate, catatan: newCatatan };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const dataToUpdate = result.value;
                const updateUrl = updateUrlTemplate.replace(':id', id);

                $.ajax({
                    url: updateUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tgl_wawancara: dataToUpdate.tgl_wawancara,
                        ket_wawancara: dataToUpdate.catatan
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('#DTPendaftar').DataTable().ajax.reload(null, false);
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
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui tanggal.', 'error');
                        }
                    }
                });
            }
        });
    });
</script>
 <script>

    const updateFinalUrlTemplate = "{{ route('pendaftar.updateSelesai', ['id' => ':id']) }}";
    $('#DTPendaftar').on('click', '.edit-selesai-btn', function() {
       event.preventDefault(); // Mencegah navigasi default tautan

        const id = $(this).data('id');
        const existingCatatan = $(this).data('catatan') || '';

        Swal.fire({
            title: 'Pilih Tanggal Selesai dan Catatan',
            html: 
                '<input type="date" id="swal-input-date" class="form-control mb-3">' +
                '<textarea id="swal-input-catatan" class="form-control" placeholder="Masukkan catatan ...">' + existingCatatan + '</textarea>',
            focusConfirm: false,
            preConfirm: () => {
                const newDate = $('#swal-input-date').val();
                const newCatatan = $('#swal-input-catatan').val();
                
                if (!newDate) {
                    Swal.showValidationMessage('Mohon pilih tanggal');
                    return false;
                }
                return { tgl_final: newDate, catatan: newCatatan };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const dataToUpdate = result.value;
                const updateUrl = updateFinalUrlTemplate.replace(':id', id);

                $.ajax({
                    url: updateUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tgl_final: dataToUpdate.tgl_final,
                        ket_wawancara: dataToUpdate.catatan
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Berhasil!', response.message, 'success');
                            $('#DTPendaftar').DataTable().ajax.reload(null, false);
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
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui tanggal.', 'error');
                        }
                    }
                });
            }
        });
    });
    </script>
    
@endpush