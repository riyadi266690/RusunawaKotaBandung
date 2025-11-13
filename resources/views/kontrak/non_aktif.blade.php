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
    <li class="breadcrumb-item active" aria-current="page">Non Aktif</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
          <h6 class="card-title mb-0">Data Kontrak Non Aktif</h6>
          
        </div>
        <p class="text-muted">Daftar kontrak yang tidak aktif.</p>
        <div class="table-responsive">
          <table id="DTKontrakNonAktif" class="table">
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
                    <th>Tgl. Keluar</th>
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
    const ajaxUrlKontrakAktif = @json(route('kontrak.ajax.DTKontrakNonAktif'));

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
       var table = $('#DTKontrakNonAktif').DataTable({
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
            { data: 'tgl_keluar', name: 'tgl_keluar' }
        ],
        language: {
            "search": "Search by No Kontrak:"
        }
    });
    
    // Menambahkan event listener untuk membuka dan menutup child row
    $('#DTKontrakNonAktif tbody').on('click', 'td.dt-control', function() {
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



    });
  </script>
@endpush
